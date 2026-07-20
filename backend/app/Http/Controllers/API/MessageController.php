<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService,
    ) {}
    /**
     * List messages for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $messages = Message::with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
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
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string|max:5000',
            'exchange_id' => 'nullable|exists:exchanges,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        $message = $this->messageService->send(
            $user,
            (int) $request->receiver_id,
            $request->content,
            $request->exchange_id ? (int) $request->exchange_id : null,
        );

        $message->load(['receiver:id,name,avatar']);

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
        $user = $request->user();

        $message = Message::with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->findOrFail($id);

        if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    /**
     * Update the specified message (mark as read).
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $message = Message::findOrFail($id);

        if ($message->receiver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    /**
     * Remove the specified message.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $message = Message::findOrFail($id);

        if ($message->sender_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted',
        ]);
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
            $partner = User::select('id', 'name', 'avatar', 'email')->find($partnerId);
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
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'avatar' => $partner->avatar,
                    'email' => $partner->email,
                ],
                'last_message' => $lastMessage ? [
                    'id' => $lastMessage->id,
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
        $partnerId = (int) $userId;

        if ($partnerId === (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid conversation partner',
            ], 422);
        }

        // #region agent log
        $allowed = $this->messageService->canAccessConversation($user, $partnerId);
        $payload = json_encode([
            'sessionId' => '6d48b3',
            'runId' => 'post-fix',
            'hypothesisId' => 'H1-idor-open',
            'location' => 'MessageController::conversation',
            'message' => 'conversation access check',
            'data' => [
                'authUserId' => (int) $user->id,
                'partnerId' => $partnerId,
                'allowed' => $allowed,
                'canMessage' => $this->messageService->canMessage($user, $partnerId),
            ],
            'timestamp' => (int) (microtime(true) * 1000),
        ])."\n";
        @file_put_contents(storage_path('logs/debug-6d48b3.log'), $payload, FILE_APPEND);
        // #endregion

        // Authorize before loading partner PII (Facebook-style: server gate, no leak on deny).
        if (! $allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }

        $partner = User::select('id', 'name', 'avatar')->find($partnerId);
        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $messages = Message::with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
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
                'partner' => $partner,
                'messages' => $messages,
                'can_message' => $this->messageService->canMessage($user, $partnerId),
            ],
        ]);
    }
}
