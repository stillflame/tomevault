<?php

namespace Tests\Unit;

use App\Models\Tome;
use App\Services\TomeService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TomeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TomeService $tomeService;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tomeService = $this->app->make(TomeService::class);
    }

    public function test_returns_all_tomes_without_pagination_if_count_below_threshold()
    {
        Tome::factory()->count(5)->create();

        $result = $this->tomeService->getTomesForIndex();

        $this->assertCount(5, $result['data']);
        $this->assertEquals(5, $result['meta']['total']);
        $this->assertArrayNotHasKey('current_page', $result['meta']);
    }

    public function test_returns_paginated_tomes_if_count_above_threshold()
    {
        Tome::factory()->count(15)->create();

        $result = $this->tomeService->getTomesForIndex();

        $this->assertCount(10, $result['data']);
        $this->assertEquals(15, $result['meta']['total']);
        $this->assertEquals(1, $result['meta']['current_page']);
        $this->assertEquals(2, $result['meta']['last_page']);
    }
}
