<?php

namespace Database\Factories;

use App\Models\ApiLog;

use Illuminate\Database\Eloquent\Factories\Factory;
use JsonException;

class ApiLogFactory extends Factory
{
    protected $model = ApiLog::class;

    /**
     * @throws JsonException
     */
    public function definition(): array
    {
        return [
            'request_id' => $this->faker->uuid,
            'method' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
            'url' => $this->faker->url,
            'endpoint' => '/api/' . $this->faker->word,
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
            'user_id' => $this->faker->optional(0.7)->numberBetween(1, 1000),
            'user_type' => $this->faker->randomElement(['user', 'admin', 'anonymous']),
            'status_code' => $this->faker->randomElement([200, 201, 400, 401, 403, 404, 500]),
            'response_time_ms' => $this->faker->numberBetween(50, 3000),
            'response_size' => $this->faker->numberBetween(100, 50000),
            'request_headers' => json_encode([
                'Accept' => 'application/json',
                'User-Agent' => $this->faker->userAgent,
            ], JSON_THROW_ON_ERROR),
            'request_data' => $this->faker->optional()->passthrough(json_encode([
                'param1' => $this->faker->word,
                'param2' => $this->faker->numberBetween(1, 100),
            ], JSON_THROW_ON_ERROR)),
            'response_data' => json_encode([
                'success' => $this->faker->boolean,
                'data' => $this->faker->words(3, true),
            ], JSON_THROW_ON_ERROR),
            'cache_hit' => $this->faker->boolean(30), // 30% cache hit rate
            'log_level' => $this->faker->randomElement(['info', 'warning', 'error']),
            'error_message' => $this->faker->optional(0.2)->sentence,
            'error_context' => $this->faker->optional(0.2)->passthrough(json_encode([
                'file' => '/path/to/file.php',
                'line' => $this->faker->numberBetween(1, 500),
            ], JSON_THROW_ON_ERROR)),
            'metadata' => json_encode([
                'memory_usage' => $this->faker->numberBetween(1000000, 50000000),
                'query_count' => $this->faker->numberBetween(1, 20),
            ], JSON_THROW_ON_ERROR),
            'created_at' => $this->faker->dateTimeBetween('-30 days'),
        ];
    }

    public function successful(): static
    {
        return $this->state(static fn (array $attributes) => [
            'status_code' => 200,
            'log_level' => 'info',
            'error_message' => null,
            'error_context' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_code' => $this->faker->randomElement([400, 401, 403, 404, 500]),
            'log_level' => 'error',
            'error_message' => $this->faker->sentence,
            'error_context' => json_encode([
                'file' => '/app/Http/Controllers/SomeController.php',
                'line' => $this->faker->numberBetween(1, 100),
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    public function slow(): static
    {
        return $this->state(fn (array $attributes) => [
            'response_time_ms' => $this->faker->numberBetween(2000, 10000),
        ]);
    }

    public function fromIp(string $ip): static
    {
        return $this->state(static fn (array $attributes) => [
            'ip_address' => $ip,
        ]);
    }

    public function toEndpoint(string $endpoint, string $method = 'GET'): static
    {
        return $this->state(static fn (array $attributes) => [
            'endpoint' => $endpoint,
            'method' => $method,
        ]);
    }
}
