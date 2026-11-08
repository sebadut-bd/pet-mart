<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();

            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('is_negotiable')->default(false);
            $table->boolean('is_free')->default(false);

            // listing type: sell, adopt, lost, found, stud_service
            $table->enum('listing_type', ['sell', 'adopt', 'lost', 'found', 'stud_service'])->default('sell');

            // for goods: new, used; for pets: leave null
            $table->enum('condition', ['new', 'used'])->nullable();

            // location
            $table->unsignedBigInteger('division_id')->nullable()->index();
            $table->unsignedBigInteger('district_id')->nullable()->index();
            $table->unsignedBigInteger('area_id')->nullable()->index();
            $table->string('full_address')->nullable();

            // flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_urgent')->default(false);

            // status: pending=awaiting admin approval, active, sold, expired, rejected
            $table->enum('status', ['pending', 'active', 'sold', 'expired', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->unsignedBigInteger('views')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'listing_type']);
            $table->index(['division_id', 'district_id', 'area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
