<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\ContactMessage;
use App\Models\Inquiry;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(array $attrs = []): User
    {
        static $n = 0;
        $n++;

        return User::create($attrs + [
            'name'     => 'Test User ' . $n,
            'phone'    => '+88017' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'password' => Hash::make('secret123'),
            'type'     => 2,
            'status'   => true,
        ]);
    }

    private function makeCategory(): Category
    {
        $cat = Category::create(['name' => 'Test Cats ' . Str::random(4), 'slug' => 'test-cats-' . Str::lower(Str::random(6)), 'status' => true]);
        CategoryAttribute::create(['category_id' => $cat->id, 'name' => 'Breed', 'attribute_key' => 'breed', 'type' => 'select', 'options' => ['Persian', 'Local'], 'is_required' => true]);

        return $cat;
    }

    private function makeProduct(User $owner, string $status = 'active'): Product
    {
        return Product::create([
            'user_id'      => $owner->id,
            'category_id'  => $this->makeCategory()->id,
            'title'        => 'Test listing',
            'slug'         => 'test-listing-' . Str::lower(Str::random(8)),
            'description'  => 'A test listing description that is long enough.',
            'listing_type' => 'sell',
            'status'       => $status,
        ]);
    }

    public function test_guest_cannot_read_or_change_admin_profile(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
        $this->postJson('/api/profile', ['email' => 'x@example.com', 'password' => 'hacked123'])->assertUnauthorized();
    }

    public function test_admin_api_rejects_guests_and_customers(): void
    {
        foreach (['/api/v1/admin/dashboard-stats', '/api/v1/admin/users', '/api/v1/admin/moderation', '/api/modules', '/api/roles'] as $url) {
            $this->getJson($url)->assertUnauthorized();
        }

        $this->actingAs($this->makeUser(), 'web');
        $this->getJson('/api/v1/admin/users')->assertForbidden();
        $this->postJson('/api/configurations')->assertForbidden();
    }

    public function test_admin_can_use_admin_api(): void
    {
        $this->actingAs($this->makeUser(['type' => 1]), 'web');
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->getJson('/api/v1/admin/dashboard-stats')->assertOk()->assertJsonPath('status', 2000)->assertJsonStructure(['result' => ['cards', 'trend_chart']]);
        $this->getJson('/api/v1/admin/users?keyword=Test')->assertOk()->assertJsonStructure(['result' => ['data', 'current_page', 'last_page', 'total']]);
    }

    public function test_admin_profile_password_change_needs_current_password(): void
    {
        $admin = $this->makeUser(['type' => 1, 'email' => 'admin-' . Str::random(6) . '@example.com']);
        $this->actingAs($admin, 'web');
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->postJson('/api/profile', ['name' => 'Admin', 'email' => $admin->email, 'password' => 'newpass123', 'password_confirmation' => 'newpass123'])
            ->assertUnprocessable();
        $this->postJson('/api/profile', ['name' => 'Admin', 'email' => $admin->email, 'password' => 'newpass123', 'password_confirmation' => 'newpass123', 'current_password' => 'wrong'])
            ->assertUnprocessable();
        $this->assertTrue(Hash::check('secret123', $admin->fresh()->password));
    }

    public function test_pending_listing_is_hidden_from_public_but_visible_to_owner(): void
    {
        $owner = $this->makeUser();
        $product = $this->makeProduct($owner, 'pending');

        $this->getJson('/api/v1/products/' . $product->slug)->assertNotFound();

        Sanctum::actingAs($owner);
        $this->getJson('/api/v1/products/' . $product->slug)->assertOk()->assertJsonPath('data.is_owner', true);
    }

    public function test_product_list_caps_per_page_and_hides_seller_phone(): void
    {
        $this->makeProduct($this->makeUser());

        $res = $this->getJson('/api/v1/products?per_page=100000')->assertOk();
        $this->assertLessThanOrEqual(50, $res->json('meta.per_page'));
        $this->assertArrayNotHasKey('phone', $res->json('data.0.seller') ?? []);
    }

    public function test_create_listing_validates_attributes_and_images(): void
    {
        Storage::fake('public');
        $user = $this->makeUser();
        $category = $this->makeCategory();
        $division = Location::where('type', 'division')->first();
        $district = Location::where('type', 'district')->where('parent_id', $division->id)->first();
        Sanctum::actingAs($user);

        $payload = [
            'title'        => 'Persian kitten for sale',
            'description'  => 'Healthy, playful Persian kitten, litter trained.',
            'category_id'  => $category->id,
            'listing_type' => 'sell',
            'division_id'  => $division->id,
            'district_id'  => $district?->id,
            'images'       => ['../../.env'],
            'attributes'   => ['breed' => 'Siamese', 'hacker' => 'x'],
        ];

        $this->postJson('/api/v1/products', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['images.0', 'attributes.breed', 'attributes.hacker']);

        Storage::disk('public')->put('products/2026/11/test.jpg', 'x');
        $payload['images'] = ['products/2026/11/test.jpg'];
        $payload['attributes'] = ['breed' => 'Persian'];

        $this->postJson('/api/v1/products', $payload)->assertCreated()->assertJsonPath('data.status', 'pending');
    }

    public function test_forgot_password_flow(): void
    {
        Log::spy();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $user = $this->makeUser();

        $this->postJson('/api/v1/auth/forgot-password', ['phone' => '01999999999'])->assertOk()->assertJsonPath('status', true);

        $this->postJson('/api/v1/auth/forgot-password', ['phone' => substr($user->phone, 3)])->assertOk();
        $this->assertTrue(Cache::has("password_reset:otp:{$user->id}"));

        $this->postJson('/api/v1/auth/verify-otp', ['phone' => $user->phone, 'otp' => '000000'])->assertUnprocessable();

        Cache::put("password_reset:otp:{$user->id}", ['hash' => Hash::make('123123'), 'attempts' => 0], now()->addMinutes(10));
        $token = $this->postJson('/api/v1/auth/verify-otp', ['phone' => $user->phone, 'otp' => '123123'])->assertOk()->json('data.token');

        $this->postJson('/api/v1/auth/reset-password', ['token' => $token, 'password' => 'brandnew1'])->assertOk();
        $this->assertTrue(Hash::check('brandnew1', $user->fresh()->password));

        $this->postJson('/api/v1/auth/reset-password', ['token' => $token, 'password' => 'another1'])->assertUnprocessable();
    }

    public function test_seller_can_reply_to_buyer(): void
    {
        $seller = $this->makeUser();
        $buyer = $this->makeUser();
        $product = $this->makeProduct($seller);

        Sanctum::actingAs($buyer);
        $id = $this->postJson('/api/v1/inquiries', ['product_id' => $product->id, 'message' => 'Is it available?'])
            ->assertCreated()->json('data.id');

        Sanctum::actingAs($seller);
        $this->postJson("/api/v1/inquiries/{$id}/reply", ['message' => 'Yes, it is.'])
            ->assertCreated()
            ->assertJsonPath('data.receiver_id', $buyer->id)
            ->assertJsonPath('data.is_mine', true);
        $this->postJson("/api/v1/inquiries/{$id}/read")->assertOk()->assertJsonPath('data.updated', 1);

        Sanctum::actingAs($this->makeUser());
        $this->postJson("/api/v1/inquiries/{$id}/reply", ['message' => 'Hi'])->assertNotFound();
        $this->assertSame(2, Inquiry::where('product_id', $product->id)->count());
    }

    public function test_contact_form_is_stored(): void
    {
        $this->postJson('/api/v1/contact', ['name' => 'Rahim', 'phone' => '01711111111', 'message' => 'I need help with my account.'])
            ->assertCreated();
        $this->assertDatabaseHas('contact_messages', ['phone' => '+8801711111111', 'status' => 'new']);

        $this->postJson('/api/v1/contact', ['name' => 'R', 'phone' => '123', 'message' => 'short'])
            ->assertUnprocessable();
        $this->assertSame(1, ContactMessage::where('name', 'Rahim')->count());
    }
}
