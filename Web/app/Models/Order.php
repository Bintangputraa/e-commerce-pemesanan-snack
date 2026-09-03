<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';

    protected $primaryKey = 'id_order';

    protected $fillable = [
        'id_user',
        'total_harga',
        'status_pembayaran',
        'status_pesanan',
        'alamat_pengiriman',
        'tanggal_pesan',
        'kode_voucher',
        'diskon',
        'midtrans_order_id',
        'snap_token',
        'payment_type',
        'transaction_id',
        'transaction_status',
        'payment_url',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'tanggal_pesan' => 'date',
        'diskon' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'id_order', 'id_order');
    }
}
