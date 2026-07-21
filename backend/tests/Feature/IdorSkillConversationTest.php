<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IdorSkillConversationTest extends TestCase
{
    use RefreshDatabase;

    private function assertNotFoundJson($response): void
    {
        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Not found');
    }

    public function test_user_cannot_update_others_skill(): void
    {
        $category = Category::create(['name' => 'T', 'description' => 't']);
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $skill = Skill::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Owner skill',
            'description' => 'd',
            'level' => 'beginner',
            'is_available' => true,
        ]);

        Sanctum::actingAs($other);
        $this->assertNotFoundJson(
            $this->putJson("/api/skills/{$skill->uuid}", ['title' => 'Hijacked'])
        );
    }

    public function test_unrelated_conversation_returns_404(): void
    {
        $maria = User::factory()->create(['name' => 'Maria']);
        $carlos = User::factory()->create(['name' => 'Carlos']);

        Sanctum::actingAs($maria);
        $this->assertNotFoundJson(
            $this->getJson("/api/conversations/{$carlos->uuid}")
        );
    }

    public function test_non_uuid_conversation_partner_returns_404(): void
    {
        $maria = User::factory()->create(['name' => 'Maria']);

        Sanctum::actingAs($maria);
        $this->assertNotFoundJson(
            $this->getJson('/api/conversations/3')
        );
    }

    public function test_guest_cannot_see_unavailable_skills_when_filtering_by_user_id(): void
    {
        $category = Category::create(['name' => 'T', 'description' => 't']);
        $owner = User::factory()->create();
        $available = Skill::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Public skill',
            'description' => 'd',
            'level' => 'beginner',
            'is_available' => true,
        ]);
        $unavailable = Skill::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Draft skill',
            'description' => 'd',
            'level' => 'beginner',
            'is_available' => false,
        ]);

        $response = $this->getJson("/api/skills?user_id={$owner->uuid}");

        $response->assertOk()->assertJsonPath('success', true);
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($available->uuid, $ids);
        $this->assertNotContains($unavailable->uuid, $ids);
    }

    public function test_other_user_cannot_see_unavailable_skills_when_filtering_by_user_id(): void
    {
        $category = Category::create(['name' => 'T', 'description' => 't']);
        $owner = User::factory()->create();
        $other = User::factory()->create();
        Skill::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Draft skill',
            'description' => 'd',
            'level' => 'beginner',
            'is_available' => false,
        ]);

        Sanctum::actingAs($other);
        $response = $this->getJson("/api/skills?user_id={$owner->uuid}");

        $response->assertOk()->assertJsonPath('success', true);
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEmpty($ids);
    }
}
