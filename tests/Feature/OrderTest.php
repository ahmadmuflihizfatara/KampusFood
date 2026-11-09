<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TEST-01 from the slides: the server's pricing rules cannot be bypassed from the client.
 */
class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $mahasiswa;

    private Menu $nasiGoreng;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mahasiswa = User::factory()->create();
        $this->nasiGoreng = Menu::factory()->create(['nama' => 'Nasi Goreng', 'harga' => 15000]);
    }

    public function test_normal_order_is_priced_from_the_database(): void
    {
        $this->actingAs($this->mahasiswa)
            ->postJson('/api/orders', ['menu_id' => $this->nasiGoreng->id, 'jumlah' => 2])
            ->assertCreated()
            ->assertJson(['total_harga' => 30000, 'harga_satuan' => 15000, 'status' => Order::MenungguPembayaran]);

        $this->assertDatabaseHas('orders', ['user_id' => $this->mahasiswa->id, 'total_harga' => 30000]);
    }

    public function test_client_sent_total_is_ignored(): void
    {
        $this->actingAs($this->mahasiswa)
            ->postJson('/api/orders', ['menu_id' => $this->nasiGoreng->id, 'jumlah' => 2, 'total_harga' => 1])
            ->assertCreated()
            ->assertJson(['total_harga' => 30000]);

        $this->assertDatabaseMissing('orders', ['total_harga' => 1]);
    }

    public function test_negative_quantity_is_rejected(): void
    {
        $this->actingAs($this->mahasiswa)
            ->postJson('/api/orders', ['menu_id' => $this->nasiGoreng->id, 'jumlah' => -5])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('jumlah');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_fractional_quantity_is_rejected(): void
    {
        $this->actingAs($this->mahasiswa)
            ->postJson('/api/orders', ['menu_id' => $this->nasiGoreng->id, 'jumlah' => 0.5])
            ->assertJsonValidationErrors('jumlah');
    }

    public function test_unknown_menu_is_rejected(): void
    {
        $this->actingAs($this->mahasiswa)
            ->postJson('/api/orders', ['menu_id' => 99999, 'jumlah' => 2])
            ->assertJsonValidationErrors('menu_id');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_sold_out_menu_is_rejected(): void
    {
        $habis = Menu::factory()->habis()->create();

        $this->actingAs($this->mahasiswa)
            ->postJson('/api/orders', ['menu_id' => $habis->id, 'jumlah' => 1])
            ->assertJsonValidationErrors('menu_id');
    }

    public function test_guest_cannot_order(): void
    {
        $this->postJson('/api/orders', ['menu_id' => $this->nasiGoreng->id, 'jumlah' => 1])
            ->assertUnauthorized();
    }

    public function test_user_cannot_view_someone_elses_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->mahasiswa)->get(route('orders.show', $order))->assertForbidden();
        $this->actingAs($order->user)->get(route('orders.show', $order))->assertOk();
    }

    public function test_home_page_lists_available_menus(): void
    {
        $this->actingAs($this->mahasiswa)->get('/')->assertOk()->assertSee('Nasi Goreng');
    }
}
