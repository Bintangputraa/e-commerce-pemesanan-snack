<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'nama',
        'deskripsi',
        'harga',
        'gambar',
        'kategori',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'id_item');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'id_item');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'id_item');
    }
}
