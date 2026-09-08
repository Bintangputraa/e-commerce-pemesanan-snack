<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('transaction_time')->nullable()->after('status_baca');
            $table->string('transaction_status')->nullable()->after('transaction_time');
            $table->string('transaction_id')->nullable()->after('transaction_status');
            $table->text('status_message')->nullable()->after('transaction_id');
            $table->string('status_code')->nullable()->after('status_message');
            $table->text('signature_key')->nullable()->after('status_code');
            $table->timestamp('settlement_time')->nullable()->after('signature_key');
            $table->string('payment_type')->nullable()->after('settlement_time');
            $table->string('order_id')->nullable()->after('payment_type');
            $table->string('merchant_id')->nullable()->after('order_id');
            $table->decimal('gross_amount', 12, 2)->nullable()->after('merchant_id');
            $table->string('fraud_status')->nullable()->after('gross_amount');
            $table->string('currency')->nullable()->after('fraud_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_time',
                'transaction_status',
                'transaction_id',
                'status_message',
                'status_code',
                'signature_key',
                'settlement_time',
                'payment_type',
                'order_id',
                'merchant_id',
                'gross_amount',
                'fraud_status',
                'currency',
                'created_at',
                'updated_at',
            ]);
        });
    }
};
