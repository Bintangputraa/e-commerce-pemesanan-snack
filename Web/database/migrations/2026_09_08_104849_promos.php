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
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Misal: CEMILHEMAT
            $table->enum('discount_type', ['fixed', 'percentage']);
            $table->double('discount_amount'); // Misal: 5000 atau 10 (%)
            $table->double('min_purchase')->default(0); // Syarat minimal belanja
            $table->integer('quota')->default(0); // Batas total penggunaan
            $table->integer('used')->default(0); // Sudah berapa kali dipakai
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
