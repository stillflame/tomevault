<?php

namespace Tests\Feature;

use App\Models\Tome;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TomeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_correct_json_structure(): void
    {
        Tome::factory()->count(12)->create();

        $response = $this->getJson(config('api.api_prefix') . '/tomes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'status',
                'meta' => [
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                    'last_page',
                    'next_page_url',
                    'prev_page_url',
                    'timestamp',
                ],
            ]);
    }

    public function test_index_returns_all_data_if_below_threshold(): void
    {
        Tome::factory()->count(8)->create();

        $response = $this->getJson(config('api.api_prefix') . '/tomes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'status',
                'meta' => [
                    'total',
                    'timestamp',
                ],
            ])
            ->assertJsonCount(8, 'data');
    }

    public function test_public_can_list_tomes(): void
    {
        Tome::factory()->count(3)->create();

        $response = $this->getJson(config('api.api_prefix') . '/tomes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_unauthenticated_cannot_create_tome(): void
    {
        $response = $this->postJson(config('api.api_prefix') . '/tomes', [
            'title' => 'New Tome',
            // Add required fields here
        ]);

        $response->assertStatus(401); // Unauthorized
    }

    public function test_authenticated_can_create_tome(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(config('api.api_prefix') . '/tomes', [
                'title' => 'New Tome',
                'danger_level' => 'Unknown',    // required to avoid null error in resource
                // Add required fields here
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Tome']);
    }
}
