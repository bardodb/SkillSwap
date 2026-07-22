<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Skill::with(['user', 'category']);

        $query->where('is_available', true);

        if ($request->filled('user_id')) {
            $ownerId = $this->resolvePublicUserId($request->input('user_id'));
            if ($ownerId === null) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 12,
                        'total' => 0,
                    ],
                ]);
            }
            $query->where('user_id', $ownerId);
        }

        // Filter by category (accepts public UUID or legacy integer)
        if ($request->filled('category_id')) {
            $categoryId = $this->resolvePublicCategoryId($request->input('category_id'));
            if ($categoryId === null) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 12,
                        'total' => 0,
                    ],
                ]);
            }
            $query->where('category_id', $categoryId);
        }

        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $skills = $query->latest()->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $skills->items(),
            'pagination' => [
                'current_page' => $skills->currentPage(),
                'last_page' => $skills->lastPage(),
                'per_page' => $skills->perPage(),
                'total' => $skills->total(),
            ]
        ]);
    }

    /**
     * Get the current user's skills.
     */
    public function mySkills(Request $request)
    {
        $skills = Skill::with(['category'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $skills,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $request->all();
        if ($request->filled('category_id')) {
            $resolvedCategoryId = $this->resolvePublicCategoryId($request->input('category_id'));
            if ($resolvedCategoryId === null) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => ['category_id' => ['The selected category id is invalid.']],
                ], 422);
            }
            $payload['category_id'] = $resolvedCategoryId;
        }

        $validator = Validator::make($payload, [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'level' => 'required|in:beginner,intermediate,advanced,expert',
            'image' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $skill = Skill::create([
            'user_id' => $request->user()->id,
            'category_id' => $payload['category_id'],
            'title' => $payload['title'],
            'description' => $payload['description'],
            'level' => $payload['level'],
            'image' => $payload['image'] ?? null,
            'tags' => $payload['tags'] ?? null,
            'is_available' => true,
        ]);

        $skill->load(['user', 'category']);

        return response()->json([
            'success' => true,
            'data' => $skill
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Skill $skill)
    {
        $skill->load(['user', 'category']);
        
        return response()->json([
            'success' => true,
            'data' => $skill
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Skill $skill)
    {
        try {
            $skill = $request->user()->skills()->whereKey($skill->id)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'level' => 'sometimes|in:beginner,intermediate,advanced,expert',
            'image' => 'nullable|string',
            'tags' => 'nullable|array',
            'is_available' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $skill->update($request->only([
            'category_id', 'title', 'description', 'level', 'image', 'tags', 'is_available'
        ]));

        $skill->load(['user', 'category']);

        return response()->json([
            'success' => true,
            'data' => $skill
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill, Request $request)
    {
        try {
            $skill = $request->user()->skills()->whereKey($skill->id)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }

        $skill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skill deleted successfully'
        ]);
    }

    /**
     * Find potential skill matches for the current user.
     */
    public function findMatches(Request $request)
    {
        $currentUserId = $request->user()->id;
        
        // Get user's skills to find complementary matches
        $userSkills = Skill::where('user_id', $currentUserId)
            ->where('is_available', true)
            ->get();
            
        if ($userSkills->isEmpty()) {
                    return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Add some skills to find matches'
        ]);
        }

        // Find skills from other users in different categories
        // to encourage diverse skill exchanges
        $userCategoryIds = $userSkills->pluck('category_id')->unique();
        
        $matches = Skill::with(['user', 'category'])
            ->where('user_id', '!=', $currentUserId)
            ->where('is_available', true)
            ->whereNotIn('category_id', $userCategoryIds) // Different categories for diversity
            ->whereHas('user', function($query) {
                // Only users with good ratings
                $query->where('rating', '>=', 4.0)
                      ->orWhereNull('rating'); // Include new users
            })
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($skill) {
                return [
                    'id' => $skill->uuid,
                    'skill_title' => $skill->title,
                    'skill_description' => $skill->description,
                    'skill_level' => $skill->level,
                    'user_id' => $skill->user->uuid,
                    'user_name' => $skill->user->name,
                    'user_rating' => $skill->user->rating ?? 0,
                    'user_location' => $skill->user->location ?? 'Não informado',
                    'category_name' => $skill->category->name,
                    'match_score' => rand(75, 95) // Simple random score for now
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $matches
        ]);
    }

    private function resolvePublicUserId(mixed $value): ?int
    {
        if (User::isValidPublicUuid($value)) {
            $id = User::where('uuid', $value)->value('id');

            return $id !== null ? (int) $id : null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function resolvePublicCategoryId(mixed $value): ?int
    {
        if (Category::isValidPublicUuid($value)) {
            $id = Category::where('uuid', $value)->value('id');

            return $id !== null ? (int) $id : null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
