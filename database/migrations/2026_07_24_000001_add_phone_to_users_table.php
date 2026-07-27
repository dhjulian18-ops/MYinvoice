<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom phone (bisa null dulu agar tidak ganggu data lama)
            $table->string('phone')->nullable()->unique()->after('name');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            // Jadikan email nullable agar tidak wajib
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'phone_verified_at']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
