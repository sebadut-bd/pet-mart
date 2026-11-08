<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100); // e.g. Breed, Age, Vaccination Status
            $table->string('attribute_key', 80); // e.g. breed, age, vaccination_status
            $table->string('type', 30)->default('text'); // text, number, select, boolean, file
            $table->json('options')->nullable(); // select choices e.g. ["Vaccinated", "Not Vaccinated", "Partially"]
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_attributes');
    }
};
