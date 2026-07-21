<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Category;
use App\Models\Exchange;
use App\Models\Message;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    private function createUserPairWithSkills(): array
    {
        $category = Category::create([
            'name' => 'Tech',
            'description' => 'Technology',
        ]);

        $initiator = User::factory()->create();
        $receiver = User::factory()->create();

        $offeredSkill = Skill::create([
            'user_id' => $initiator->id,
            'category_id' => $category->id,
            'title' => 'PHP',
            'description' => 'PHP tutoring',
            'level' => 'intermediate',
            'is_available' => true,
        ]);

        $requestedSkill = Skill::create([
            'user_id' => $receiver->id,
            'category_id' => $category->id,
            'title' => 'Vue',
            'description' => 'Vue tutoring',
            'level' => 'beginner',
            'is_available' => true,
        ]);

        return compact('initiator', 'receiver', 'offeredSkill', 'requestedSkill');
    }

    public function test_creating_exchange_creates_first_message(): void
    {
        Event::fake([MessageSent::class]);

        $setup = $this->createUserPairWithSkills();
        Sanctum::actingAs($setup['initiator']);

        $response = $this->postJson('/api/exchanges', [
            'receiver_id' => $setup['receiver']->uuid,
            'offered_skill_id' => $setup['offeredSkill']->uuid,
            'requested_skill_id' => $setup['requestedSkill']->uuid,
            'message' => 'Olá, vamos trocar?',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('exchange.first_message_id', fn ($id) => is_string($id) && strlen($id) === 36)
            ->assertJsonPath('exchange.conversation_partner_id', $setup['receiver']->uuid);

        $exchangeUuid = $response->json('exchange.id');
        $firstMessageUuid = $response->json('exchange.first_message_id');

        $exchange = Exchange::where('uuid', $exchangeUuid)->firstOrFail();
        $firstMessage = Message::where('uuid', $firstMessageUuid)->firstOrFail();

        $this->assertDatabaseHas('messages', [
            'id' => $firstMessage->id,
            'exchange_id' => $exchange->id,
            'sender_id' => $setup['initiator']->id,
            'receiver_id' => $setup['receiver']->id,
            'content' => 'Olá, vamos trocar?',
        ]);

        Event::assertDispatched(MessageSent::class);

        Sanctum::actingAs($setup['receiver']);
        $conversations = $this->getJson('/api/conversations');
        $conversations->assertOk()
            ->assertJsonPath('data.0.can_message', true);
    }

    public function test_cannot_message_without_live_exchange(): void
    {
        Event::fake([MessageSent::class]);

        $setup = $this->createUserPairWithSkills();

        $exchange = Exchange::create([
            'initiator_id' => $setup['initiator']->id,
            'receiver_id' => $setup['receiver']->id,
            'offered_skill_id' => $setup['offeredSkill']->id,
            'requested_skill_id' => $setup['requestedSkill']->id,
            'status' => Exchange::STATUS_COMPLETED,
            'message' => 'Done',
        ]);

        Message::create([
            'sender_id' => $setup['initiator']->id,
            'receiver_id' => $setup['receiver']->id,
            'exchange_id' => $exchange->id,
            'content' => 'Histórico antigo',
            'is_read' => true,
        ]);

        Sanctum::actingAs($setup['initiator']);

        $response = $this->postJson('/api/messages', [
            'receiver_id' => $setup['receiver']->uuid,
            'content' => 'Nova mensagem',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['receiver_id']);

        Event::assertNotDispatched(MessageSent::class);

        $conversations = $this->getJson('/api/conversations');
        $conversations->assertOk()
            ->assertJsonPath('data.0.can_message', false);
    }
}
