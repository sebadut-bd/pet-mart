<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150)->nullable()->unique();
            $table->string('phone', 20)->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('role_id')->nullable()->index();
            $table->tinyInteger('type')->default(2)->comment('1=Admin,2=Seller,3=Buyer');
            $table->string('image')->nullable();
            $table->text('bio')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('referral_code', 30)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('status')->default(true);
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();

            $table->index('phone');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
