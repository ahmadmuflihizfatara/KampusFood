<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_cannot_change_menu_price(): void
    {
        $menu = Menu::factory()->create(['harga' => 15000]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.menu.update', $menu), ['nama' => $menu->nama, 'harga' => 1000])
            ->assertForbidden();

        $this->assertSame(15000, $menu->fresh()->harga);
    }

    public function test_mahasiswa_cannot_open_admin_page(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.index'))->assertForbidden();
    }

    public function test_admin_can_update_and_create_menu(): void
    {
        $admin = User::factory()->admin()->create();
        $menu = Menu::factory()->create(['harga' => 15000]);

        $this->actingAs($admin)
            ->post(route('admin.menu.update', $menu), ['nama' => 'Nasi Goreng Spesial', 'harga' => 18000])
            ->assertRedirect();

        $this->assertSame(18000, $menu->fresh()->harga);
        $this->assertFalse($menu->fresh()->tersedia);

        $this->actingAs($admin)
            ->post(route('admin.menu.store'), ['nama' => 'Es Jeruk', 'harga' => 5000, 'tersedia' => '1'])
            ->assertRedirect();

        $this->assertDatabaseHas('menus', ['nama' => 'Es Jeruk', 'harga' => 5000, 'tersedia' => true]);
    }

    public function test_admin_cannot_set_invalid_price(): void
    {
        $menu = Menu::factory()->create(['harga' => 15000]);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.menu.update', $menu), ['nama' => $menu->nama, 'harga' => -1])
            ->assertSessionHasErrors('harga');
    }

    public function test_admin_completes_only_paid_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $paid = Order::factory()->dibayar()->create();
        $unpaid = Order::factory()->create();

        $this->actingAs($admin)->post(route('admin.orders.complete', $paid))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.orders.complete', $unpaid))->assertUnprocessable();

        $this->assertSame(Order::Selesai, $paid->fresh()->status);
        $this->assertSame(Order::MenungguPembayaran, $unpaid->fresh()->status);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function newAccount(array $overrides = []): array
    {
        return [
            'name' => 'Siti Mahasiswi',
            'username' => 'siti_01',
            'email' => 'siti@kampusfood.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'mahasiswa',
            ...$overrides,
        ];
    }

    public function test_admin_can_create_account_that_can_log_in(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.users.store'), $this->newAccount())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $user = User::where('username', 'siti_01')->firstOrFail();
        $this->assertSame('mahasiswa', $user->role);
        $this->assertNotSame('rahasia123', $user->password);

        auth()->logout();
        $this->post('/login', ['username' => 'siti_01', 'password' => 'rahasia123'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_create_admin_account(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.users.store'), $this->newAccount(['role' => 'admin']));

        $this->assertTrue(User::where('username', 'siti_01')->firstOrFail()->isAdmin());
    }

    public function test_mahasiswa_cannot_create_account(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.users.store'), $this->newAccount(['role' => 'admin']))
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'siti_01']);
    }

    public function test_account_input_is_validated(): void
    {
        $admin = User::factory()->admin()->create(['username' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->newAccount([
                'username' => 'admin',
                'role' => 'superuser',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ]))
            ->assertSessionHasErrors(['username', 'role', 'password']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->newAccount(['password_confirmation' => 'beda12345']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 1);
    }
}
