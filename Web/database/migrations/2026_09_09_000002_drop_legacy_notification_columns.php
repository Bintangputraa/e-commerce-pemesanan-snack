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
            if (Schema::hasColumn('notifications', 'id_user')) {
                $table->dropForeign(['id_user']);
                $table->dropColumn('id_user');
            }

            if (Schema::hasColumn('notifications', 'judul')) {
                $table->dropColumn('judul');
            }

            if (Schema::hasColumn('notifications', 'pesan')) {
                $table->dropColumn('pesan');
            }

            if (Schema::hasColumn('notifications', 'status_baca')) {
                $table->dropColumn('status_baca');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('id_user')->nullable()->after('id');
            $table->string('judul')->nullable()->after('id_user');
            $table->text('pesan')->nullable()->after('judul');
            $table->boolean('status_baca')->default(false)->after('pesan');
        });
    }
};
