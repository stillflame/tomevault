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
                'meta' => [
                    'status',
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                    'last_page',
                    'next_page_url',
                    'prev_page_url',
                    'timestamps' => [
                        'timestamp',
                        'response_time_ms',
                    ],
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
                'meta' => [
                    'status',
                    'total',
                    'timestamps' => [
                        'timestamp',
                        'response_time_ms',
                    ],
                ],
            ])
            ->assertJsonCount(8, 'data');
    }

    public function test_show_returns_correct_json_structure(): void
    {
        $tome = Tome::factory()->create();

        $response = $this->getJson(config('api.api_prefix') . '/tomes/' . $tome->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    // Add other expected data fields
                ],
                'meta' => [
                    'status',
                    'timestamps' => [
                        'timestamp',
                        'response_time_ms',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_public_can_list_tomes(): void
    {
        Tome::factory()->count(3)->create();

        $response = $this->getJson(config('api.api_prefix') . '/tomes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.status', 200);
    }

    public function test_public_can_view_tome(): void
    {
        $tome = Tome::factory()->create();

        $response = $this->getJson(config('api.api_prefix') . '/tomes/' . $tome->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $tome->id)
            ->assertJsonPath('meta.status', 200);
    }

    public function test_unauthenticated_cannot_create_tome(): void
    {
        $response = $this->postJson(config('api.api_prefix') . '/tomes', [
            'title' => 'New Tome',
            'danger_level' => 'Unknown',
        ]);

        $response->assertStatus(401); // Unauthorized
    }

    public function test_authenticated_can_create_tome(): void
    {
        // Create required relationships
        $author = \App\Models\Character::factory()->create();
        $language = \App\Models\Language::factory()->create();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(config('api.api_prefix') . '/tomes', [
                'title' => 'New Tome',
                'author_id' => $author->id,
                'language_id' => $language->id,
                'danger_level' => 'Unknown',
                'artifact_type' => 'Tome',
                'cursed' => false,
                'sentient' => false,
                'illustrated' => false,
                'pages' => 100,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Tome'])
            ->assertJsonPath('meta.status', 201)
            ->assertJsonStructure([
                'data',
                'message',
                'meta' => [
                    'status',
                    'timestamps' => [
                        'timestamp',
                        'response_time_ms',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_response_times_are_included(): void
    {
        Tome::factory()->count(3)->create();

        $response = $this->getJson(config('api.api_prefix') . '/tomes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => [
                    'timestamps' => [
                        'response_time_ms',
                    ],
                ],
            ]);

        // Ensure response time is a positive number
        $responseTime = $response->json('meta.timestamps.response_time_ms');
        $this->assertIsNumeric($responseTime);
        $this->assertGreaterThan(0, $responseTime);
    }

    public function test_timestamps_are_iso8601_format(): void
    {
        $tome = Tome::factory()->create();

        $response = $this->getJson(config('api.api_prefix') . '/tomes/' . $tome->id);

        $timestamp = $response->json('meta.timestamps.timestamp');
        $createdAt = $response->json('meta.timestamps.created_at');
        $updatedAt = $response->json('meta.timestamps.updated_at');

        // Check ISO8601 format (basic validation)
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $timestamp);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $createdAt);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $updatedAt);
    }
}
