<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'midtrans_order_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('midtrans_order_id')->nullable()->after('id_order');
            });
        }
        if (! Schema::hasColumn('orders', 'snap_token')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('snap_token')->nullable()->after('total_harga');
            });
        }
        if (! Schema::hasColumn('orders', 'payment_type')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('payment_type')->nullable()->after('status_pembayaran');
            });
        }
        if (! Schema::hasColumn('orders', 'transaction_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('transaction_id')->nullable()->after('payment_type');
            });
        }
        if (! Schema::hasColumn('orders', 'transaction_status')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('transaction_status')->nullable()->after('transaction_id');
            });
        }
        if (! Schema::hasColumn('orders', 'payment_url')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('payment_url', 500)->nullable()->after('transaction_status');
            });
        }
    }

    public function down(): void
    {
        $columns = [
            'midtrans_order_id',
            'snap_token',
            'payment_type',
            'transaction_id',
            'transaction_status',
            'payment_url',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('orders', $column)) {
                Schema::table('orders', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
