<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Removes tables the app no longer uses:
 *  - sub_categories (+ products.sub_category_id): categories use parent_id for the tree instead
 *  - configurations: replaced by general_settings
 * A table that still holds rows is kept and logged, so no data is lost by accident.
 */
return new class extends Migration
{
    public function up(): void
    {
        $productsUseSub = Schema::hasColumn('products', 'sub_category_id')
            && DB::table('products')->whereNotNull('sub_category_id')->exists();

        if (Schema::hasColumn('products', 'sub_category_id') && ! $productsUseSub) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('sub_category_id');
            });
        }

        foreach (['sub_categories', 'configurations'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (DB::table($table)->exists() || ($table === 'sub_categories' && $productsUseSub)) {
                Log::warning("Legacy table {$table} still has data and was not dropped.");
                continue;
            }
            Schema::drop($table);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sub_categories')) {
            Schema::create('sub_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('slug', 120)->unique();
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('status')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
        if (! Schema::hasColumn('products', 'sub_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            });
        }
        if (! Schema::hasTable('configurations')) {
            Schema::create('configurations', function (Blueprint $table) {
                $table->id();
                $table->string('key', 50);
                $table->string('type', 50);
                $table->string('display_name', 100);
                $table->text('value');
                $table->integer('is_visible')->default(1);
                $table->integer('status')->default(1)->comment('1=Active,0=Inactive');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
};
