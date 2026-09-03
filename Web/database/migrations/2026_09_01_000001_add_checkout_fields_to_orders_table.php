<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'alamat_pengiriman')) {
                $table->text('alamat_pengiriman')->nullable();
            }
            if (! Schema::hasColumn('orders', 'tanggal_pesan')) {
                $table->date('tanggal_pesan')->nullable();
            }
            if (! Schema::hasColumn('orders', 'kode_voucher')) {
                $table->string('kode_voucher')->nullable();
            }
            if (! Schema::hasColumn('orders', 'diskon')) {
                $table->decimal('diskon', 12, 2)->default(0);
            }
        });

        Schema::table('order_details', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_details', 'catatan')) {
                $table->text('catatan')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table): void {
            if (Schema::hasColumn('order_details', 'catatan')) {
                $table->dropColumn('catatan');
            }
        });
        Schema::table('orders', function (Blueprint $table): void {
            $columns = [];
            foreach (['alamat_pengiriman', 'tanggal_pesan', 'kode_voucher', 'diskon'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $columns[] = $column;
                }
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
