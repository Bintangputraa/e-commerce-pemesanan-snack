<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;

class PromoController extends Controller
{
    public function check(Request $request) {
    $request->validate([
        'code' => 'required|string',
        'total_price' => 'required|numeric' // Kirim total belanja dari Android
    ]);

    $promo = Promo::where('code', $request->code)->first();

    if (!$promo) {
        return response()->json([
            'success' => false,
            'message' => 'Kode voucher tidak ditemukan'
        ], 404);
    }

    list($isValid, $message) = $promo->isValid($request->total_price);

    return response()->json([
        'success' => $isValid,
        'message' => $message,
        'discount_amount' => $promo->discount_amount,
        'discount_type' => $promo->discount_type
    ]);
}
}
