<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAccountsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(array $attrs = []): User
    {
        return User::create($attrs + [
            'name'     => 'Person ' . Str::random(4),
            'phone'    => '+88019' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'email'    => Str::lower(Str::random(10)) . '@example.com',
            'password' => Hash::make('secret123'),
            'type'     => 2,
            'status'   => true,
        ]);
    }

    private function freePhone(): string
    {
        do {
            $local = '017' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (User::where('phone', '+88' . $local)->exists());

        return $local;
    }

    private function signInAdmin(): User
    {
        $admin = $this->makeUser(['type' => 1, 'role_id' => Role::value('id')]);
        $this->actingAs($admin, 'web');
        $this->withoutMiddleware(ValidateCsrfToken::class);

        return $admin;
    }

    public function test_members_list_excludes_dashboard_users(): void
    {
        $admin = $this->signInAdmin();
        $member = $this->makeUser();

        $ids = collect($this->getJson('/api/v1/admin/members?perPage=100&keyword=' . urlencode($member->name))->assertOk()->json('result.data'))->pluck('id');
        $this->assertTrue($ids->contains($member->id));

        $ids = collect($this->getJson('/api/v1/admin/members?perPage=100&keyword=' . urlencode($admin->name))->json('result.data'))->pluck('id');
        $this->assertFalse($ids->contains($admin->id));
    }

    public function test_member_can_be_edited_deactivated_and_deleted(): void
    {
        $this->signInAdmin();
        $member = $this->makeUser();
        $member->createToken('t');

        $phone = $this->freePhone();
        $this->putJson("/api/v1/admin/members/{$member->id}", [
            'name' => 'Renamed', 'phone' => $phone, 'email' => null, 'is_verified' => true, 'status' => false,
        ])->assertOk();

        $member->refresh();
        $this->assertSame('+88' . $phone, $member->phone);
        $this->assertFalse($member->status);
        $this->assertSame(0, $member->tokens()->count());

        $this->deleteJson("/api/v1/admin/members/{$member->id}")->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    public function test_admin_users_crud_and_guards(): void
    {
        $me = $this->signInAdmin();
        $roleId = Role::value('id');

        $id = $this->postJson('/api/v1/admin/users', [
            'name' => 'Moderator', 'email' => 'mod-' . Str::random(6) . '@example.com', 'phone' => $this->freePhone(),
            'role_id' => $roleId, 'password' => 'longpass1', 'password_confirmation' => 'longpass1', 'status' => true,
        ])->assertCreated()->json('result.id');

        $this->assertSame(1, (int) User::find($id)->type);

        $this->postJson("/api/v1/admin/users/{$id}/toggle-status")->assertOk();
        $this->assertFalse((bool) User::find($id)->status);

        $this->postJson("/api/v1/admin/users/{$me->id}/toggle-status")->assertUnprocessable();
        $this->deleteJson("/api/v1/admin/users/{$me->id}")->assertUnprocessable();

        $this->deleteJson("/api/v1/admin/users/{$id}")->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $id]);
    }

    public function test_inactive_admin_is_signed_out(): void
    {
        $admin = $this->makeUser(['type' => 1, 'status' => false]);
        $this->actingAs($admin, 'web');

        $this->getJson('/api/v1/admin/dashboard-stats')->assertUnauthorized();
    }

    public function test_role_and_module_status_toggle(): void
    {
        $this->signInAdmin();
        $role = Role::create(['name' => 'tmp_' . Str::random(6), 'display_name' => 'Temp']);
        $role->status = 1;
        $role->save();

        $this->postJson('/api/roles/status', ['id' => $role->id])->assertOk()->assertJsonPath('status', 2000);
        $this->assertSame(0, (int) $role->fresh()->status);

        $this->postJson('/api/roles/status', ['id' => 1])->assertUnprocessable();
        $this->deleteJson('/api/roles/1')->assertUnprocessable();
    }

    public function test_all_listings_filter_and_delete(): void
    {
        $this->signInAdmin();
        $seller = $this->makeUser();
        $category = \App\Models\Category::create(['name' => 'T ' . Str::random(4), 'slug' => 't-' . Str::lower(Str::random(8)), 'status' => true]);
        $make = fn (string $status) => \App\Models\Product::create([
            'user_id' => $seller->id, 'category_id' => $category->id, 'title' => 'Item ' . $status,
            'slug' => 'item-' . Str::lower(Str::random(10)), 'listing_type' => 'sell', 'status' => $status,
        ]);
        $live = $make('active');
        $rejected = $make('rejected');

        $ids = collect($this->getJson("/api/v1/admin/moderation?status=all&category_id={$category->id}")->assertOk()->json('result.data'))->pluck('id');
        $this->assertEqualsCanonicalizing([$live->id, $rejected->id], $ids->all());

        $ids = collect($this->getJson("/api/v1/admin/moderation?status=rejected&category_id={$category->id}")->json('result.data'))->pluck('id');
        $this->assertSame([$rejected->id], $ids->all());

        $this->deleteJson("/api/v1/admin/moderation/{$live->id}")->assertOk();
        $this->assertSoftDeleted('products', ['id' => $live->id]);
    }

    public function test_admin_user_photo_and_member_profile_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->signInAdmin();
        $roleId = Role::value('id');

        $path = $this->postJson('/api/v1/admin/uploads/images', [
            'image'  => \Illuminate\Http\UploadedFile::fake()->image('me.jpg', 400, 400),
            'folder' => 'avatars',
        ])->assertCreated()->json('result.path');
        $this->assertStringStartsWith('avatars/', $path);

        $id = $this->postJson('/api/v1/admin/users', [
            'name' => 'Photo Admin', 'email' => 'p-' . Str::random(6) . '@example.com', 'phone' => $this->freePhone(),
            'role_id' => $roleId, 'password' => 'longpass1', 'password_confirmation' => 'longpass1', 'status' => true, 'image' => $path,
        ])->assertCreated()->json('result.id');
        $this->assertSame($path, User::find($id)->image);

        $this->postJson('/api/v1/admin/users', [
            'name' => 'Bad Photo', 'email' => 'b-' . Str::random(6) . '@example.com', 'phone' => $this->freePhone(),
            'role_id' => $roleId, 'password' => 'longpass1', 'password_confirmation' => 'longpass1', 'status' => true, 'image' => 'avatars/missing.jpg',
        ])->assertUnprocessable()->assertJsonValidationErrors('image');

        $member = $this->makeUser();
        \Laravel\Sanctum\Sanctum::actingAs($member);
        $memberPath = $this->postJson('/api/v1/uploads/images', [
            'image'   => \Illuminate\Http\UploadedFile::fake()->image('face.png', 300, 300),
            'purpose' => 'avatar',
        ])->assertCreated()->json('data.path');

        $this->putJson('/api/v1/auth/me', ['image' => $memberPath])->assertOk()->assertJsonPath('data.image', asset('storage/' . $memberPath));
        $this->putJson('/api/v1/auth/me', ['image' => null])->assertOk()->assertJsonPath('data.image', null);
    }

    public function test_admin_can_set_and_clear_member_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->signInAdmin();
        $member = $this->makeUser();
        $base = ['name' => $member->name, 'phone' => substr($member->phone, 3), 'email' => null, 'is_verified' => false, 'status' => true];

        $path = $this->postJson('/api/v1/admin/uploads/images', [
            'image'  => \Illuminate\Http\UploadedFile::fake()->image('m.jpg', 300, 300),
            'folder' => 'avatars',
        ])->assertCreated()->json('result.path');

        $this->putJson("/api/v1/admin/members/{$member->id}", $base + ['image' => $path])
            ->assertOk()->assertJsonPath('result.image_path', $path);

        $this->putJson("/api/v1/admin/members/{$member->id}", $base + ['image' => 'avatars/nope.jpg'])
            ->assertUnprocessable()->assertJsonValidationErrors('image');

        $this->putJson("/api/v1/admin/members/{$member->id}", $base + ['image' => null])->assertOk();
        $this->assertNull($member->fresh()->image);
    }

    public function test_inactive_member_cannot_log_in_or_use_existing_token(): void
    {
        $member = $this->makeUser();
        $token = $member->createToken('t')->plainTextToken;

        $this->getJson('/api/v1/auth/me', ['Authorization' => "Bearer {$token}"])->assertOk();

        $member->forceFill(['status' => false])->save();
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/auth/me', ['Authorization' => "Bearer {$token}"])
            ->assertUnauthorized()->assertJsonPath('message', 'Your account has been deactivated. Please contact support.');
        $this->assertSame(0, $member->tokens()->count());

        $this->postJson('/api/v1/auth/login', ['phone' => substr($member->phone, 3), 'password' => 'secret123'])
            ->assertForbidden();
    }

    public function test_inactive_admin_cannot_sign_in(): void
    {
        $admin = $this->makeUser(['type' => 1, 'status' => false, 'email' => 'off-' . Str::random(6) . '@example.com']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'secret123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('web');
    }
}
