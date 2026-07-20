<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Exchange;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IdorExchangeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{exchange: Exchange, outsider: User}
     */
    private function exchangeBetweenParticipantsWithOutsider(): array
    {
        $category = Category::create(['name' => 'T', 'description' => 't']);
        $a = User::factory()->create();
        $b = User::factory()->create();
        $outsider = User::factory()->create();
        $skillA = Skill::create([
            'user_id' => $a->id, 'category_id' => $category->id,
            'title' => 'A', 'description' => 'd', 'level' => 'beginner', 'is_available' => true,
        ]);
        $skillB = Skill::create([
            'user_id' => $b->id, 'category_id' => $category->id,
            'title' => 'B', 'description' => 'd', 'level' => 'beginner', 'is_available' => true,
        ]);
        $exchange = Exchange::create([
            'initiator_id' => $a->id,
            'receiver_id' => $b->id,
            'offered_skill_id' => $skillA->id,
            'requested_skill_id' => $skillB->id,
            'status' => Exchange::STATUS_PENDING,
            'message' => 'hi',
        ]);

        return ['exchange' => $exchange, 'outsider' => $outsider];
    }

    private function assertNotFoundExchangeJson($response): void
    {
        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Not found');
    }

    public function test_non_participant_cannot_show_exchange(): void
    {
        ['exchange' => $exchange, 'outsider' => $outsider] = $this->exchangeBetweenParticipantsWithOutsider();

        Sanctum::actingAs($outsider);
        $this->assertNotFoundExchangeJson(
            $this->getJson("/api/exchanges/{$exchange->id}")
        );
    }

    public function test_non_participant_cannot_update_exchange(): void
    {
        ['exchange' => $exchange, 'outsider' => $outsider] = $this->exchangeBetweenParticipantsWithOutsider();

        Sanctum::actingAs($outsider);
        $this->assertNotFoundExchangeJson(
            $this->putJson("/api/exchanges/{$exchange->id}", ['status' => 'accepted'])
        );
    }

    public function test_non_participant_cannot_destroy_exchange(): void
    {
        ['exchange' => $exchange, 'outsider' => $outsider] = $this->exchangeBetweenParticipantsWithOutsider();

        Sanctum::actingAs($outsider);
        $this->assertNotFoundExchangeJson(
            $this->deleteJson("/api/exchanges/{$exchange->id}")
        );
    }
}
