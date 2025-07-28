<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Skill;
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

        // Filter by user_id (for user's own skills)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        } else {
            // Only show available skills when not filtering by user
            $query->where('is_available', true);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
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
        $validator = Validator::make($request->all(), [
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
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'level' => $request->level,
            'image' => $request->image,
            'tags' => $request->tags,
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
        // Check if user owns this skill
        if ($skill->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
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
        // Check if user owns this skill
        if ($skill->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
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
                    'id' => $skill->id,
                    'skill_title' => $skill->title,
                    'skill_description' => $skill->description,
                    'skill_level' => $skill->level,
                    'user_id' => $skill->user->id,
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
}
