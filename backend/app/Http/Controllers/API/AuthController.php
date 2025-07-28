<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'bio' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'bio' => $request->bio,
            'location' => $request->location,
            'phone' => $request->phone,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        
        // Garantir que rating seja sempre um número
        $user->rating = (float) ($user->rating ?: 0.0);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'name', 'email', 'bio', 'location', 'phone', 'avatar'
        ]));

        // Garantir que rating seja sempre um número
        $user->rating = (float) ($user->rating ?: 0.0);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function getUserProfile(Request $request, $userId)
    {
        try {
            $user = User::with(['skills.category'])->findOrFail($userId);
            
            // Calcular estatísticas públicas do usuário
            $totalSkills = $user->skills()->where('is_available', true)->count();
            $totalExchanges = $user->initiatedExchanges()
                ->where('status', 'completed')
                ->count() + 
                $user->receivedExchanges()
                ->where('status', 'completed')
                ->count();
            
            // Informações públicas do usuário
            $publicProfile = [
                'id' => $user->id,
                'name' => $user->name,
                'bio' => $user->bio,
                'location' => $user->location,
                'avatar' => $user->avatar,
                'rating' => (float) ($user->rating ?: 0.0),
                'total_exchanges' => $user->total_exchanges ?: 0,
                'member_since' => $user->created_at->format('Y-m-d'),
                'stats' => [
                    'skills' => $totalSkills,
                    'exchanges' => $totalExchanges,
                    'rating' => (float) ($user->rating ?: 0.0)
                ],
                'skills' => $user->skills->where('is_available', true)->map(function($skill) {
                    return [
                        'id' => $skill->id,
                        'title' => $skill->title,
                        'description' => $skill->description,
                        'level' => $skill->level,
                        'tags' => $skill->tags,
                        'category' => [
                            'id' => $skill->category->id,
                            'name' => $skill->category->name
                        ]
                    ];
                })->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $publicProfile
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar perfil do usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
