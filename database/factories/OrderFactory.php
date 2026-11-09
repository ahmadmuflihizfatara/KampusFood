<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jumlah = fake()->numberBetween(1, 3);
        $harga = fake()->numberBetween(3, 25) * 1000;

        return [
            'user_id' => User::factory(),
            'menu_id' => Menu::factory(['harga' => $harga]),
            'jumlah' => $jumlah,
            'harga_satuan' => $harga,
            'total_harga' => $harga * $jumlah,
            'status' => Order::MenungguPembayaran,
            'payment_reference' => 'KF-'.Str::ulid(),
        ];
    }

    public function dibayar(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::Dibayar,
            'paid_at' => now(),
        ]);
    }
}
