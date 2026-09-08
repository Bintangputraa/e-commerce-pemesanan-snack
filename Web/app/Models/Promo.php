<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = ['code', 'discount_type', 'discount_amount', 'min_purchase', 'quota', 'used', 'start_date', 'end_date', 'is_active'];

    public function isValid($totalAmount) {
        $now = now();
        
        if (!$this->is_active) return [false, "Voucher tidak aktif"];
        if ($now < $this->start_date) return [false, "Voucher belum bisa digunakan"];
        if ($now > $this->end_date) return [false, "Voucher sudah kedaluwarsa"];
        if ($this->quota > 0 && $this->used >= $this->quota) return [false, "Kouta voucher sudah habis"];
        if ($totalAmount < $this->min_purchase) return [false, "Minimal belanja belum terpenuhi"];
        
        return [true, "Voucher berhasil diterapkan"];
    }
}
