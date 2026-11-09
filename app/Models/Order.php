<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * No #[Fillable]: every column is set explicitly by server code, never mass-assigned from a request.
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public const string MenungguPembayaran = 'menunggu_pembayaran';

    public const string Dibayar = 'dibayar';

    public const string Selesai = 'selesai';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'integer',
            'total_harga' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
