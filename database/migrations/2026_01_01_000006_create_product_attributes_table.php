<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('attribute_key', 80);
            $table->string('attribute_value', 255);
            $table->timestamps();

            $table->index(['product_id', 'attribute_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
