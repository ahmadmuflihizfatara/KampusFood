<?php

namespace App\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama', 'harga', 'tersedia'])]
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'tersedia' => 'boolean',
        ];
    }
}
