<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Menu ID 10 = Nasi Goreng Rp15.000, matching the case study in the slides.
     */
    public function run(): void
    {
        $menus = [
            10 => ['Nasi Goreng', 15000],
            11 => ['Mie Ayam', 12000],
            12 => ['Ayam Geprek', 17000],
            13 => ['Soto Ayam', 14000],
            14 => ['Es Teh Manis', 4000],
            15 => ['Jus Jeruk', 8000],
        ];

        foreach ($menus as $id => [$nama, $harga]) {
            $menu = new Menu(['nama' => $nama, 'harga' => $harga, 'tersedia' => true]);
            $menu->id = $id;
            $menu->save();
        }
    }
}
