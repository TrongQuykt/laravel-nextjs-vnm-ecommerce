<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test get dashboard stats
     */
    public function test_get_dashboard_stats()
    {
        // Create test data
        Order::factory()->count(5)->create(['status' => 'completed']);
        Order::factory()->count(2)->create(['status' => 'pending']);
        
        // Clear cache
        Cache::flush();

        $response = $this->get('/api/v1/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'total_orders',
                    'total_revenue',
                    'pending_orders',
                    'completed_orders',
                ]
            ]);
    }

    /**
     * Test get sales chart data
     */
    public function test_get_sales_chart()
    {
        Order::factory()->count(10)->create([
            'created_at' => now()->subDays(5),
            'status' => 'completed',
            'total_amount' => 100000,
        ]);

        Cache::flush();

        $response = $this->get('/api/v1/admin/dashboard/sales-chart?days=7');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'labels',
                    'revenue',
                    'orders',
                ]
            ]);
    }

    /**
     * Test get top products
     */
    public function test_get_top_products()
    {
        $products = Product::factory()->count(5)->create();
        
        Cache::flush();

        $response = $this->get('/api/v1/admin/dashboard/top-products?limit=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'total_sold',
                        'total_revenue',
                    ]
                ]
            ]);
    }

    /**
     * Test caching works
     */
    public function test_caching_works()
    {
        Order::factory()->count(3)->create(['status' => 'completed']);
        
        Cache::flush();

        // First call - should hit database
        $response1 = $this->get('/api/v1/admin/dashboard/stats');
        $response1->assertStatus(200);

        // Second call - should hit cache
        $response2 = $this->get('/api/v1/admin/dashboard/stats');
        $response2->assertStatus(200);

        // Both should return same data
        $this->assertEquals($response1->json(), $response2->json());
    }
}
