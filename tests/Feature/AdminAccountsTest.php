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
}
