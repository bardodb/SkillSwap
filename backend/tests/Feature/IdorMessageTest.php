<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IdorMessageTest extends TestCase
{
    use RefreshDatabase;

    private function assertNotFoundJson($response): void
    {
        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Not found');
    }

    private function createMessageBetween(User $sender, User $receiver): Message
    {
        return Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'content' => 'Private thread',
            'is_read' => false,
        ]);
    }

    public function test_user_cannot_show_message_between_others(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $carol = User::factory()->create();
        $message = $this->createMessageBetween($alice, $bob);

        Sanctum::actingAs($carol);
        $this->assertNotFoundJson($this->getJson("/api/messages/{$message->id}"));
    }

    public function test_user_cannot_update_message_between_others(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $carol = User::factory()->create();
        $message = $this->createMessageBetween($alice, $bob);

        Sanctum::actingAs($carol);
        $this->assertNotFoundJson($this->putJson("/api/messages/{$message->id}"));
    }

    public function test_user_cannot_delete_message_between_others(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $carol = User::factory()->create();
        $message = $this->createMessageBetween($alice, $bob);

        Sanctum::actingAs($carol);
        $this->assertNotFoundJson($this->deleteJson("/api/messages/{$message->id}"));
    }
}
