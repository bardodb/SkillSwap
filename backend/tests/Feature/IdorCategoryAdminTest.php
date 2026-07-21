<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IdorCategoryAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_create_category(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/categories', [
            'name' => 'New Category',
            'description' => 'Test',
        ])->assertForbidden();
    }

    public function test_non_admin_cannot_update_category(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Existing', 'description' => 'd']);

        Sanctum::actingAs($user);

        $this->putJson("/api/categories/{$category->uuid}", [
            'name' => 'Hijacked',
        ])->assertForbidden();
    }

    public function test_non_admin_cannot_destroy_category(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Existing', 'description' => 'd']);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/categories/{$category->uuid}")
            ->assertForbidden();
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        Sanctum::actingAs($admin);

        $this->postJson('/api/categories', [
            'name' => 'Admin Category',
            'description' => 'Created by admin',
        ])
            ->assertCreated()
            ->assertJsonPath('category.name', 'Admin Category');

        $this->assertDatabaseHas('categories', ['name' => 'Admin Category']);
    }
}
