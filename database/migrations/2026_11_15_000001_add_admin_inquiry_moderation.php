<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->timestamp('admin_edited_at')->nullable()->after('is_read');
            $table->index(['product_id', 'sender_id', 'receiver_id']);
        });

        Schema::create('inquiry_admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('inquiry_id')->nullable()->index();
            $table->string('action', 30);
            $table->text('old_message')->nullable();
            $table->unsignedInteger('affected')->default(1);
            $table->timestamps();
        });

        $this->addModule();
    }

    public function down(): void
    {
        $moduleId = DB::table('modules')->where('name', 'inquiry')->value('id');
        if ($moduleId) {
            $permissionIds = DB::table('permissions')->where('module_id', $moduleId)->pluck('id');
            DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
            DB::table('roles_modules')->where('module_id', $moduleId)->delete();
            DB::table('modules')->where('id', $moduleId)->delete();
        }

        Schema::dropIfExists('inquiry_admin_logs');

        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'sender_id', 'receiver_id']);
            $table->dropColumn('admin_edited_at');
        });
    }

    private function addModule(): void
    {
        $parentId = DB::table('modules')->where('name', 'marketplace')->value('id');
        if (! $parentId || DB::table('modules')->where('name', 'inquiry')->exists()) {
            return;
        }

        $now = now();
        $moduleId = DB::table('modules')->insertGetId([
            'display_name' => 'Inquiries',
            'name'         => 'inquiry',
            'link'         => '/admin/marketplace/inquiries',
            'icon'         => 'fa-comments',
            'parent_id'    => $parentId,
            'status'       => 1,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
        DB::table('roles_modules')->insert(['role_id' => 1, 'module_id' => $moduleId, 'status' => 1, 'created_at' => $now, 'updated_at' => $now]);

        foreach (['view', 'update', 'delete'] as $action) {
            $permissionId = DB::table('permissions')->insertGetId([
                'module_id'    => $moduleId,
                'name'         => 'inquiry_' . $action,
                'display_name' => 'Inquiries ' . $action,
                'status'       => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            DB::table('role_permissions')->insert(['role_id' => 1, 'permission_id' => $permissionId, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
};
