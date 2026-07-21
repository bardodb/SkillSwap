<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exchange;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly MessageService $messageService,
    ) {}
    /**
     * List messages for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $messages = Message::with(['sender:id,uuid,name,avatar', 'receiver:id,uuid,name,avatar'])
            ->forParticipant($user)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,uuid',
            'content' => 'required|string|max:5000',
            'exchange_id' => 'nullable|exists:exchanges,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        $receiver = User::where('uuid', $request->receiver_id)->firstOrFail();
        $exchangeId = null;
        if ($request->exchange_id) {
            $exchange = Exchange::where('uuid', $request->exchange_id)->firstOrFail();
            $exchangeId = $exchange->id;
        }

        $message = $this->messageService->send(
            $user,
            (int) $receiver->id,
            $request->content,
            $exchangeId,
        );

        $message->load(['receiver:id,uuid,name,avatar']);

        return response()->json([
            'success' => true,
            'data' => $message,
        ], 201);
    }

    /**
     * Display the specified message.
     */
    public function show(Request $request, string $id)
    {
        try {
            $user = $request->user();

            $message = Message::with(['sender:id,uuid,name,avatar', 'receiver:id,uuid,name,avatar'])
                ->forParticipant($user)
                ->where('uuid', $id)
                ->firstOrFail();

            $this->authorize('view', $message);

            return response()->json([
                'success' => true,
                'data' => $message,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }
    }

    /**
     * Update the specified message (mark as read).
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = $request->user();
            $message = Message::forParticipant($user)->where('uuid', $id)->firstOrFail();

            $this->authorize('update', $message);

            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $message,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }
    }

    /**
     * Remove the specified message.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $user = $request->user();
            $message = Message::forParticipant($user)->where('uuid', $id)->firstOrFail();

            $this->authorize('delete', $message);

            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }
    }

    /**
     * List conversations for the authenticated user.
     */
    public function conversations(Request $request)
    {
        $user = $request->user();

        $partnerIds = Message::query()
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->get(['sender_id', 'receiver_id'])
            ->flatMap(fn (Message $m) => [$m->sender_id, $m->receiver_id])
            ->unique()
            ->reject(fn ($id) => (int) $id === (int) $user->id)
            ->values();

        $conversations = $partnerIds->map(function ($partnerId) use ($user) {
            $partner = User::select('id', 'uuid', 'name', 'avatar', 'email')->find($partnerId);
            if (!$partner) {
                return null;
            }

            $lastMessage = Message::where(function ($q) use ($user, $partnerId) {
                $q->where(function ($inner) use ($user, $partnerId) {
                    $inner->where('sender_id', $user->id)->where('receiver_id', $partnerId);
                })->orWhere(function ($inner) use ($user, $partnerId) {
                    $inner->where('sender_id', $partnerId)->where('receiver_id', $user->id);
                });
            })
                ->orderByDesc('created_at')
                ->first();

            $unreadCount = Message::where('sender_id', $partnerId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            return [
                'partner' => [
                    'id' => $partner->uuid,
                    'name' => $partner->name,
                    'avatar' => $partner->avatar,
                    'email' => $partner->email,
                ],
                'last_message' => $lastMessage ? [
                    'id' => $lastMessage->uuid,
                    'content' => $lastMessage->content,
                    'sender_id' => $lastMessage->sender_id,
                    'created_at' => $lastMessage->created_at,
                    'is_read' => $lastMessage->is_read,
                ] : null,
                'unread_count' => $unreadCount,
                'can_message' => $this->messageService->canMessage($user, (int) $partnerId),
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    /**
     * Messages between the authenticated user and another user.
     */
    public function conversation(Request $request, string $userId)
    {
        $user = $request->user();

        if ($userId === $user->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid conversation partner',
            ], 422);
        }

        if (! User::isValidPublicUuid($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }

        $partner = User::where('uuid', $userId)->first();
        if (! $partner) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }

        $partnerId = (int) $partner->id;

        // Authorize before loading partner PII (Facebook-style: server gate, no leak on deny).
        if (! $this->messageService->canAccessConversation($user, $partnerId)) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }

        $partner = User::select('id', 'uuid', 'name', 'avatar')->find($partnerId);
        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $messages = Message::with(['sender:id,uuid,name,avatar', 'receiver:id,uuid,name,avatar'])
            ->where(function ($q) use ($user, $partnerId) {
                $q->where(function ($inner) use ($user, $partnerId) {
                    $inner->where('sender_id', $user->id)->where('receiver_id', $partnerId);
                })->orWhere(function ($inner) use ($user, $partnerId) {
                    $inner->where('sender_id', $partnerId)->where('receiver_id', $user->id);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark incoming unread as read
        Message::where('sender_id', $partnerId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'partner' => [
                    'id' => $partner->uuid,
                    'name' => $partner->name,
                    'avatar' => $partner->avatar,
                ],
                'messages' => $messages,
                'can_message' => $this->messageService->canMessage($user, $partnerId),
            ],
        ]);
    }
}
