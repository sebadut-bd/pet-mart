<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inquiry;
use App\Models\InquiryAdminLog;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminInquiryTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(array $attrs = []): User
    {
        return User::create($attrs + [
            'name'     => 'Person ' . Str::random(6),
            'phone'    => '+88018' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'password' => Hash::make('secret123'),
            'type'     => 2,
            'status'   => true,
        ]);
    }

    private function makeProduct(User $owner): Product
    {
        $cat = Category::create(['name' => 'Inq Cats ' . Str::random(4), 'slug' => 'inq-cats-' . Str::lower(Str::random(6)), 'status' => true]);

        return Product::create([
            'user_id'      => $owner->id,
            'category_id'  => $cat->id,
            'title'        => 'Inquiry test listing ' . Str::random(5),
            'slug'         => 'inq-listing-' . Str::lower(Str::random(8)),
            'description'  => 'A test listing description that is long enough.',
            'listing_type' => 'sell',
            'status'       => 'active',
        ]);
    }

    private function say(Product $p, User $from, User $to, string $text): Inquiry
    {
        return Inquiry::create(['product_id' => $p->id, 'sender_id' => $from->id, 'receiver_id' => $to->id, 'message' => $text, 'is_read' => false]);
    }

    private function signIn(?int $roleId = 1): User
    {
        $admin = $this->makeUser(['type' => 1, 'role_id' => $roleId]);
        $this->actingAs($admin, 'web');
        $this->withoutMiddleware(ValidateCsrfToken::class);

        return $admin;
    }

    /** Seller with one listing, two buyers chatting about it. */
    private function scenario(): array
    {
        $seller = $this->makeUser(['name' => 'Seller ' . Str::random(8)]);
        $buyerA = $this->makeUser();
        $buyerB = $this->makeUser();
        $product = $this->makeProduct($seller);

        $a1 = $this->say($product, $buyerA, $seller, 'Is the kitten still available?');
        $a2 = $this->say($product, $seller, $buyerA, 'Yes, come on Friday.');
        $b1 = $this->say($product, $buyerB, $seller, 'What is the lowest price?');

        return compact('seller', 'buyerA', 'buyerB', 'product', 'a1', 'a2', 'b1');
    }

    public function test_drill_down_from_seller_to_conversation(): void
    {
        $s = $this->scenario();
        $admin = $this->signIn();

        $row = collect($this->getJson('/api/v1/admin/inquiries/sellers?keyword=' . urlencode($s['seller']->name))->assertOk()->json('result.data'))
            ->firstWhere('id', $s['seller']->id);
        $this->assertSame(1, $row['listings']);
        $this->assertSame(2, $row['threads']);
        $this->assertSame(3, $row['messages']);

        $listings = $this->getJson("/api/v1/admin/inquiries/sellers/{$s['seller']->id}/listings")->assertOk()->json('result.data');
        $this->assertCount(1, $listings);
        $this->assertSame(2, $listings[0]['threads']);

        $threads = collect($this->getJson("/api/v1/admin/inquiries/listings/{$s['product']->id}/threads")->assertOk()->json('result'));
        $this->assertEqualsCanonicalizing([$s['buyerA']->id, $s['buyerB']->id], $threads->pluck('buyer_id')->all());
        $this->assertSame(2, $threads->firstWhere('buyer_id', $s['buyerA']->id)['messages']);

        $thread = $this->getJson("/api/v1/admin/inquiries/listings/{$s['product']->id}/threads/{$s['buyerA']->id}")->assertOk()->json('result');
        $this->assertSame(['buyer', 'seller'], collect($thread['messages'])->pluck('from')->all());
        $this->assertSame($s['seller']->id, $thread['seller']['id']);
        $this->assertTrue(InquiryAdminLog::where('admin_id', $admin->id)->where('action', 'viewed')->exists());
    }

    public function test_edit_delete_restore_are_logged_and_visible_to_users(): void
    {
        $s = $this->scenario();
        $admin = $this->signIn();

        $this->putJson("/api/v1/admin/inquiries/{$s['b1']->id}", ['message' => 'What is the price? [number removed]'])->assertOk();
        $this->assertNotNull($s['b1']->fresh()->admin_edited_at);
        $this->assertDatabaseHas('inquiry_admin_logs', ['inquiry_id' => $s['b1']->id, 'action' => 'edited', 'old_message' => 'What is the lowest price?', 'buyer_id' => $s['buyerB']->id]);

        // The seller sees the edit flag on the customer site.
        Sanctum::actingAs($s['seller']);
        $mine = collect($this->getJson('/api/v1/inquiries')->assertOk()->json('data'))->firstWhere('id', $s['b1']->id);
        $this->assertTrue($mine['edited_by_admin']);

        $this->actingAs($admin, 'web');
        $this->deleteJson("/api/v1/admin/inquiries/{$s['a1']->id}")->assertOk();
        $this->assertSoftDeleted('inquiries', ['id' => $s['a1']->id]);
        $this->postJson("/api/v1/admin/inquiries/{$s['a1']->id}/restore")->assertOk();
        $this->assertNotSoftDeleted('inquiries', ['id' => $s['a1']->id]);

        // Whole conversation: only buyer A's messages go, buyer B's stay.
        $this->deleteJson("/api/v1/admin/inquiries/listings/{$s['product']->id}/threads/{$s['buyerA']->id}")->assertOk()->assertJsonPath('result.deleted', 2);
        $this->assertSoftDeleted('inquiries', ['id' => $s['a2']->id]);
        $this->assertNotSoftDeleted('inquiries', ['id' => $s['b1']->id]);

        $deleted = collect($this->getJson("/api/v1/admin/inquiries/listings/{$s['product']->id}/threads?scope=deleted")->assertOk()->json('result'));
        $this->assertSame([$s['buyerA']->id], $deleted->pluck('buyer_id')->all());

        $this->postJson("/api/v1/admin/inquiries/listings/{$s['product']->id}/threads/{$s['buyerA']->id}/restore")->assertOk()->assertJsonPath('result.restored', 2);
        $this->assertSame(5, InquiryAdminLog::where('admin_id', $admin->id)->whereIn('action', ['edited', 'deleted', 'restored', 'thread_deleted', 'thread_restored'])->count());
    }

    public function test_role_without_permission_is_blocked(): void
    {
        $s = $this->scenario();
        $role = Role::create(['name' => 'support_' . Str::random(4), 'display_name' => 'Support', 'status' => 1]);
        $this->signIn($role->id);

        $this->getJson('/api/v1/admin/inquiries/sellers')->assertForbidden();

        RolePermission::insert(['role_id' => $role->id, 'permission_id' => Permission::where('name', 'inquiry_view')->value('id')]);
        $this->getJson('/api/v1/admin/inquiries/sellers')->assertOk();
        $this->putJson("/api/v1/admin/inquiries/{$s['a1']->id}", ['message' => 'changed'])->assertForbidden();
        $this->deleteJson("/api/v1/admin/inquiries/{$s['a1']->id}")->assertForbidden();
    }

    public function test_members_cannot_use_admin_inquiry_api(): void
    {
        $s = $this->scenario();
        $this->actingAs($s['buyerA'], 'web');

        $this->getJson('/api/v1/admin/inquiries/sellers')->assertForbidden();
    }
}
