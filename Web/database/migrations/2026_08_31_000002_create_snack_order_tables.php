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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 12, 2);
            $table->unsignedInteger('stok')->default(0);
            $table->string('gambar')->nullable();
            $table->string('kategori');
            $table->timestamps();
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_item')->constrained('items')->cascadeOnDelete();
            $table->unique(['id_user', 'id_item']);
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_item')->constrained('items')->cascadeOnDelete();
            $table->unsignedInteger('jumlah');
            $table->unique(['id_user', 'id_item']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id('id_order');
            $table->foreignId('id_user')->constrained('users')->restrictOnDelete();
            $table->decimal('total_harga', 12, 2);
            $table->string('status_pembayaran')->default('pending');
            $table->string('status_pesanan')->default('pending');
            $table->text('alamat_pengiriman')->nullable();
            $table->date('tanggal_pesan')->nullable();
            $table->string('kode_voucher')->nullable();
            $table->decimal('diskon', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->foreignId('id_order')->constrained('orders', 'id_order')->cascadeOnDelete();
            $table->foreignId('id_item')->constrained('items')->restrictOnDelete();
            $table->unsignedInteger('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->text('catatan')->nullable();
            $table->primary(['id_order', 'id_item']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->text('pesan');
            $table->boolean('status_baca')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('items');
    }
};
