<?php

namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Exchange;
use App\Models\Skill;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExchangeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly MessageService $messageService,
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $exchanges = Exchange::with([
                'initiator', 
                'receiver', 
                'offeredSkill.category', 
                'requestedSkill.category'
            ])
            ->where(function($query) use ($user) {
                $query->where('initiator_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($exchange) use ($user) {
                $isInitiator = $exchange->initiator_id === $user->id;
                $partner = $isInitiator ? $exchange->receiver : $exchange->initiator;
                
                return [
                    'id' => $exchange->uuid,
                    'status' => $exchange->status,
                    'message' => $exchange->message,
                    'created_at' => $exchange->created_at->format('Y-m-d H:i:s'),
                    'scheduled_at' => $exchange->scheduled_at?->format('Y-m-d H:i:s'),
                    'completed_at' => $exchange->completed_at?->format('Y-m-d H:i:s'),
                    'is_initiator' => $isInitiator,
                    'partner' => [
                        'id' => $partner->uuid,
                        'name' => $partner->name,
                        'avatar' => $partner->avatar,
                        'rating' => (float) ($partner->rating ?: 0.0)
                    ],
                    'offered_skill' => [
                        'id' => $exchange->offeredSkill->uuid,
                        'title' => $exchange->offeredSkill->title,
                        'description' => $exchange->offeredSkill->description,
                        'level' => $exchange->offeredSkill->level,
                        'category' => $exchange->offeredSkill->category->name
                    ],
                    'requested_skill' => [
                        'id' => $exchange->requestedSkill->uuid,
                        'title' => $exchange->requestedSkill->title,
                        'description' => $exchange->requestedSkill->description,
                        'level' => $exchange->requestedSkill->level,
                        'category' => $exchange->requestedSkill->category->name
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $exchanges
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar trocas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|exists:users,uuid',
                'offered_skill_id' => 'required|exists:skills,uuid',
                'requested_skill_id' => 'required|exists:skills,uuid',
                'message' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $initiator = $request->user();
            $receiver = User::where('uuid', $request->receiver_id)->firstOrFail();
            $receiverId = $receiver->id;

            if ($receiverId === (int) $initiator->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => [
                        'receiver_id' => ['Você não pode criar uma troca consigo mesmo'],
                    ],
                ], 422);
            }

            $offeredSkill = Skill::where('uuid', $request->offered_skill_id)->firstOrFail();
            $requestedSkill = Skill::where('uuid', $request->requested_skill_id)->firstOrFail();
            $offeredSkillId = $offeredSkill->id;
            $requestedSkillId = $requestedSkill->id;

            if ((int) $offeredSkill->user_id !== (int) $initiator->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => [
                        'offered_skill_id' => ['A skill oferecida deve ser sua'],
                    ],
                ], 422);
            }

            if ((int) $requestedSkill->user_id !== (int) $receiverId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => [
                        'requested_skill_id' => ['A skill pedida deve ser do destinatário'],
                    ],
                ], 422);
            }

            if (! $offeredSkill->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => 'Habilidade oferecida não encontrada ou não disponível',
                ], 400);
            }

            if (! $requestedSkill->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => 'Habilidade solicitada não encontrada ou não disponível',
                ], 400);
            }

            // Verificar se já existe uma troca pendente entre estes usuários para estas habilidades
            $existingExchange = Exchange::where(function($query) use ($initiator, $receiverId, $offeredSkillId, $requestedSkillId) {
                $query->where('initiator_id', $initiator->id)
                      ->where('receiver_id', $receiverId)
                      ->where('offered_skill_id', $offeredSkillId)
                      ->where('requested_skill_id', $requestedSkillId);
            })
            ->orWhere(function($query) use ($initiator, $receiverId, $offeredSkillId, $requestedSkillId) {
                $query->where('initiator_id', $receiverId)
                      ->where('receiver_id', $initiator->id)
                      ->where('offered_skill_id', $requestedSkillId)
                      ->where('requested_skill_id', $offeredSkillId);
            })
            ->whereIn('status', [Exchange::STATUS_PENDING, Exchange::STATUS_ACCEPTED, Exchange::STATUS_SCHEDULED])
            ->first();

            if ($existingExchange) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe uma solicitação de troca pendente entre vocês para estas habilidades'
                ], 400);
            }

            [$exchange, $message] = DB::transaction(function () use ($initiator, $receiverId, $offeredSkillId, $requestedSkillId, $request) {
                $exchange = Exchange::create([
                    'initiator_id' => $initiator->id,
                    'receiver_id' => $receiverId,
                    'offered_skill_id' => $offeredSkillId,
                    'requested_skill_id' => $requestedSkillId,
                    'status' => Exchange::STATUS_PENDING,
                    'message' => $request->message,
                ]);

                $message = $this->messageService->sendForNewExchange(
                    $initiator,
                    $receiverId,
                    $exchange->id,
                    $request->message,
                );

                return [$exchange, $message];
            });

            broadcast(new MessageSent($message->load('sender:id,uuid,name,avatar')));

            // Carregar relacionamentos para resposta
            $exchange->load([
                'initiator', 
                'receiver', 
                'offeredSkill.category', 
                'requestedSkill.category'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitação de troca enviada com sucesso!',
                'exchange' => [
                    'id' => $exchange->uuid,
                    'status' => $exchange->status,
                    'message' => $exchange->message,
                    'created_at' => $exchange->created_at->format('Y-m-d H:i:s'),
                    'receiver' => [
                        'id' => $exchange->receiver->uuid,
                        'name' => $exchange->receiver->name,
                        'avatar' => $exchange->receiver->avatar
                    ],
                    'offered_skill' => [
                        'id' => $exchange->offeredSkill->uuid,
                        'title' => $exchange->offeredSkill->title,
                        'category' => $exchange->offeredSkill->category->name
                    ],
                    'requested_skill' => [
                        'id' => $exchange->requestedSkill->uuid,
                        'title' => $exchange->requestedSkill->title,
                        'category' => $exchange->requestedSkill->category->name
                    ],
                    'conversation_partner_id' => $receiver->uuid,
                    'first_message_id' => $message->uuid,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar solicitação de troca',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        try {
            if (! Exchange::isValidPublicUuid($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not found'
                ], 404);
            }

            $exchange = Exchange::with([
                'initiator', 
                'receiver', 
                'offeredSkill.category', 
                'requestedSkill.category'
            ])
                ->forParticipant($request->user())
                ->where('uuid', $id)
                ->firstOrFail();

            $this->authorize('view', $exchange);

            return response()->json([
                'success' => true,
                'exchange' => $exchange
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar troca',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:accepted,rejected,scheduled,completed,cancelled',
                'scheduled_at' => 'nullable|date|after:now',
                'rating' => 'nullable|integer|min:1|max:5',
                'feedback' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            $exchange = Exchange::forParticipant($user)
                ->where('uuid', $id)
                ->firstOrFail();

            $this->authorize('update', $exchange);

            $newStatus = $request->status;

            // Lógica específica por status
            switch ($newStatus) {
                case Exchange::STATUS_ACCEPTED:
                    if ($exchange->receiver_id !== $user->id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Apenas o destinatário pode aceitar a troca'
                        ], 403);
                    }
                    break;

                case Exchange::STATUS_REJECTED:
                    if ($exchange->receiver_id !== $user->id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Apenas o destinatário pode rejeitar a troca'
                        ], 403);
                    }
                    break;

                case Exchange::STATUS_COMPLETED:
                    if ($request->rating) {
                        $isInitiator = $exchange->initiator_id === $user->id;
                        if ($isInitiator) {
                            $exchange->rating_initiator = $request->rating;
                            $exchange->feedback_initiator = $request->feedback;
                        } else {
                            $exchange->rating_receiver = $request->rating;
                            $exchange->feedback_receiver = $request->feedback;
                        }
                    }
                    $exchange->completed_at = now();
                    break;
            }

            $exchange->status = $newStatus;
            
            if ($request->scheduled_at) {
                $exchange->scheduled_at = $request->scheduled_at;
            }
            
            $exchange->save();

            // Carregar relacionamentos para resposta
            $exchange->load([
                'initiator', 
                'receiver', 
                'offeredSkill.category', 
                'requestedSkill.category'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Troca atualizada com sucesso!',
                'exchange' => $exchange
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar troca',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $user = $request->user();

            $exchange = Exchange::forParticipant($user)->where('uuid', $id)->firstOrFail();

            $this->authorize('delete', $exchange);

            // Verificar se o usuário tem permissão para deletar esta troca
            if ($exchange->initiator_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas o iniciador pode cancelar a troca'
                ], 403);
            }

            // Apenas permitir cancelamento se estiver pendente
            if ($exchange->status !== Exchange::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas trocas pendentes podem ser canceladas'
                ], 400);
            }

            $exchange->delete();

            return response()->json([
                'success' => true,
                'message' => 'Solicitação de troca cancelada com sucesso!'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar troca',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
