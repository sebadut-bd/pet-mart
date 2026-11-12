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

            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();

            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('is_negotiable')->default(false);
            $table->boolean('is_free')->default(false);

            $table->enum('listing_type', ['sell', 'adopt', 'lost', 'found', 'stud_service'])->default('sell');

            $table->enum('condition', ['new', 'used'])->nullable();

            $table->foreignId('division_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('full_address')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_urgent')->default(false);

            $table->enum('status', ['pending', 'active', 'sold', 'expired', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->unsignedBigInteger('views')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'listing_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
