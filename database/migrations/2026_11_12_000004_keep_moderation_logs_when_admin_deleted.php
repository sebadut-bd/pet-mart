<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_moderation_logs', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->unsignedBigInteger('admin_id')->nullable()->change();
            $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admin_moderation_logs', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->foreign('admin_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
