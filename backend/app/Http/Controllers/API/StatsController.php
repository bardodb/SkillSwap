<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Skill;
use App\Models\Exchange;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * Get platform statistics
     */
    public function index(): JsonResponse
    {
        try {
            $stats = [
                'users' => User::count(),
                'skills' => Skill::count(),
                'exchanges' => Exchange::where('status', Exchange::STATUS_COMPLETED)->count(),
                'categories' => Category::count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user-specific statistics
     */
    public function userStats(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Contar habilidades do usuário
            $userSkills = Skill::where('user_id', $user->id)->count();

            // Contar trocas do usuário
            $userExchanges = Exchange::where(function($query) use ($user) {
                $query->where('initiator_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })->where('status', Exchange::STATUS_COMPLETED)->count();

            // Contar conexões únicas (pessoas com quem fez trocas)
            $connections = Exchange::where(function($query) use ($user) {
                $query->where('initiator_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->where('status', Exchange::STATUS_COMPLETED)
            ->get()
            ->map(function($exchange) use ($user) {
                return $exchange->initiator_id === $user->id 
                    ? $exchange->receiver_id 
                    : $exchange->initiator_id;
            })
            ->unique()
            ->count();

            $stats = [
                'skills' => (int) $userSkills,
                'exchanges' => (int) $userExchanges,
                'connections' => (int) $connections,
                'rating' => (float) ($user->rating ?: 0.0)
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas do usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weekly statistics and changes for user
     */
    public function weeklyStats(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $weekAgo = now()->subDays(7);
            
            // Calcular variações da semana
            
            // Skills adicionadas esta semana
            $skillsThisWeek = Skill::where('user_id', $user->id)
                ->where('created_at', '>=', $weekAgo)
                ->count();
            
            // Trocas completadas esta semana
            $exchangesThisWeek = Exchange::where(function($query) use ($user) {
                $query->where('initiator_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->where('status', Exchange::STATUS_COMPLETED)
            ->where('completed_at', '>=', $weekAgo)
            ->count();
            
            // Novas conexões esta semana
            $newConnectionsThisWeek = Exchange::where(function($query) use ($user) {
                $query->where('initiator_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->where('status', Exchange::STATUS_COMPLETED)
            ->where('completed_at', '>=', $weekAgo)
            ->get()
            ->map(function($exchange) use ($user) {
                return $exchange->initiator_id === $user->id 
                    ? $exchange->receiver_id 
                    : $exchange->initiator_id;
            })
            ->unique()
            ->count();
            
            // Calcular mudança na avaliação (comparar com média da semana passada)
            $ratingChange = 0;
            $currentRating = (float) ($user->rating ?: 0.0);
            
            // Para simplificar, vamos calcular baseado em trocas recentes com ratings
            $recentExchangesWithRating = Exchange::where(function($query) use ($user) {
                $query->where('initiator_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->where('status', Exchange::STATUS_COMPLETED)
            ->where('completed_at', '>=', $weekAgo)
            ->where(function($query) use ($user) {
                $query->where(function($q) use ($user) {
                    $q->where('initiator_id', $user->id)->whereNotNull('rating_initiator');
                })->orWhere(function($q) use ($user) {
                    $q->where('receiver_id', $user->id)->whereNotNull('rating_receiver');
                });
            })
            ->get();
            
            if ($recentExchangesWithRating->count() > 0) {
                $weeklyRatingSum = 0;
                $ratingCount = 0;
                
                foreach ($recentExchangesWithRating as $exchange) {
                    if ($exchange->initiator_id === $user->id && $exchange->rating_initiator) {
                        $weeklyRatingSum += $exchange->rating_initiator;
                        $ratingCount++;
                    }
                    if ($exchange->receiver_id === $user->id && $exchange->rating_receiver) {
                        $weeklyRatingSum += $exchange->rating_receiver;
                        $ratingCount++;
                    }
                }
                
                if ($ratingCount > 0) {
                    $weeklyAvgRating = $weeklyRatingSum / $ratingCount;
                    $ratingChange = $weeklyAvgRating - $currentRating;
                }
            }
            
            // Gerar mensagens dinâmicas
            $skillsMessage = $this->generateWeeklyMessage('skills', $skillsThisWeek);
            $exchangesMessage = $this->generateWeeklyMessage('exchanges', $exchangesThisWeek);
            $connectionsMessage = $this->generateWeeklyMessage('connections', $newConnectionsThisWeek);
            $ratingMessage = $this->generateRatingMessage($currentRating, $ratingChange);
            
            $weeklyStats = [
                'skills' => [
                    'change' => $skillsThisWeek,
                    'message' => $skillsMessage,
                    'type' => $skillsThisWeek > 0 ? 'positive' : 'neutral'
                ],
                'exchanges' => [
                    'change' => $exchangesThisWeek,
                    'message' => $exchangesMessage,
                    'type' => $exchangesThisWeek > 0 ? 'positive' : 'neutral'
                ],
                'connections' => [
                    'change' => $newConnectionsThisWeek,
                    'message' => $connectionsMessage,
                    'type' => $newConnectionsThisWeek > 0 ? 'positive' : 'neutral'
                ],
                'rating' => [
                    'change' => round($ratingChange, 1),
                    'message' => $ratingMessage,
                    'type' => $ratingChange > 0 ? 'positive' : ($ratingChange < 0 ? 'negative' : 'neutral')
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $weeklyStats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas semanais',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate weekly message based on type and count
     */
    private function generateWeeklyMessage($type, $count)
    {
        if ($count === 0) {
            $messages = [
                'skills' => 'Sem novas skills',
                'exchanges' => 'Nenhuma troca esta semana',
                'connections' => 'Sem novas conexões'
            ];
        } else {
            $messages = [
                'skills' => "+{$count} esta semana",
                'exchanges' => "+{$count} esta semana", 
                'connections' => "+{$count} esta semana"
            ];
        }
        
        return $messages[$type] ?? '';
    }
    
    /**
     * Generate rating message based on current rating and change
     */
    private function generateRatingMessage($rating, $change)
    {
        if ($change > 0.2) {
            return 'Melhorando! 📈';
        } elseif ($change < -0.2) {
            return 'Atenção! 📉';
        } elseif ($rating >= 4.5) {
            return 'Excelente! ⭐';
        } elseif ($rating >= 4.0) {
            return 'Muito bom! 👍';
        } elseif ($rating >= 3.0) {
            return 'Bom trabalho! 💪';
        } else {
            return 'Continue praticando! 🚀';
        }
    }
} 