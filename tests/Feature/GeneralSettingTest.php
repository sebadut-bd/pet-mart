<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Admin General Settings + public settings API.
 * Every test runs inside a transaction that is rolled back, and uses a fake disk.
 */
class GeneralSettingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        GeneralSetting::updateOrCreate(['key' => 'home_hero_banner'], ['type' => 'image', 'group' => 'homepage', 'display_name' => 'Homepage banner image', 'is_public' => true, 'value' => null]);
        GeneralSetting::updateOrCreate(['key' => 'home_hero_title'], ['type' => 'text', 'group' => 'homepage', 'display_name' => 'Banner title', 'is_public' => true, 'value' => 'Old title']);
        GeneralSetting::updateOrCreate(['key' => 'contact_email'], ['type' => 'email', 'group' => 'contact', 'display_name' => 'Support email', 'is_public' => true, 'value' => null]);
        GeneralSetting::updateOrCreate(['key' => 'test_private_secret'], ['type' => 'text', 'group' => 'general', 'display_name' => 'Private', 'is_public' => false, 'value' => 'hidden']);
    }

    private function admin(): User
    {
        $u = new User();
        $u->id = 999999;
        $u->type = 1;

        return $u;
    }

    private function customer(): User
    {
        $u = new User();
        $u->id = 999998;
        $u->type = 2;

        return $u;
    }

    public function test_public_api_returns_only_public_settings(): void
    {
        $res = $this->getJson('/api/v1/settings')->assertOk();
        $res->assertJsonPath('data.home_hero_title', 'Old title');
        $this->assertArrayNotHasKey('test_private_secret', $res->json('data'));
    }

    public function test_guest_and_customer_cannot_use_admin_endpoints(): void
    {
        $this->getJson('/api/general_setting')->assertUnauthorized();
        $this->actingAs($this->customer())->getJson('/api/general_setting')->assertForbidden();
        $this->actingAs($this->customer())
            ->post('/api/general_setting/home_hero_banner/image', ['image' => UploadedFile::fake()->image('b.jpg', 1600, 600)], ['Accept' => 'application/json'])
            ->assertForbidden();
    }

    public function test_admin_can_list_and_update_text_settings(): void
    {
        $this->actingAs($this->admin())->getJson('/api/general_setting')
            ->assertOk()->assertJsonPath('status', 2000)->assertJsonStructure(['result' => ['homepage']]);

        $this->actingAs($this->admin())
            ->postJson('/api/general_setting', ['settings' => ['home_hero_title' => 'New title', 'not_a_real_key' => 'x']])
            ->assertOk()->assertJsonPath('status', 2000);

        $this->assertSame('New title', GeneralSetting::get('home_hero_title'));
        $this->assertNull(GeneralSetting::where('key', 'not_a_real_key')->first(), 'unknown keys must not be created');
        $this->getJson('/api/v1/settings')->assertJsonPath('data.home_hero_title', 'New title');
    }

    public function test_invalid_email_is_rejected_with_field_error(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/general_setting', ['settings' => ['contact_email' => 'not-an-email']])
            ->assertOk()->assertJsonPath('status', 3000)->assertJsonStructure(['result' => ['contact_email']]);
    }

    public function test_admin_can_upload_replace_and_remove_banner(): void
    {
        $res = $this->actingAs($this->admin())
            ->postJson('/api/general_setting/home_hero_banner/image', ['image' => UploadedFile::fake()->image('banner.jpg', 1920, 700)])
            ->assertOk()->assertJsonPath('status', 2000);

        $first = GeneralSetting::get('home_hero_banner');
        $this->assertMatchesRegularExpression('#^settings/[0-9a-f-]{36}\.jpg$#', $first);
        Storage::disk('public')->assertExists($first);
        $this->assertStringContainsString('/storage/' . $first, $res->json('result.image_url'));

        // Replacing deletes the old file
        $this->actingAs($this->admin())
            ->postJson('/api/general_setting/home_hero_banner/image', ['image' => UploadedFile::fake()->image('banner2.png', 1920, 700)])
            ->assertOk();
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($this->admin())->deleteJson('/api/general_setting/home_hero_banner/image')->assertOk();
        $this->assertNull(GeneralSetting::get('home_hero_banner'));
        $this->getJson('/api/v1/settings')->assertJsonPath('data.home_hero_banner', null);
    }

    public function test_banner_upload_rejects_small_and_non_images(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/general_setting/home_hero_banner/image', ['image' => UploadedFile::fake()->image('tiny.jpg', 300, 200)])
            ->assertUnprocessable()->assertJsonValidationErrors('image');

        $this->actingAs($this->admin())
            ->postJson('/api/general_setting/home_hero_banner/image', ['image' => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>')])
            ->assertUnprocessable()->assertJsonValidationErrors('image');
    }

    public function test_image_upload_only_for_image_type_keys(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/api/general_setting/home_hero_title/image', ['image' => UploadedFile::fake()->image('b.jpg', 1600, 600)])
            ->assertNotFound();
    }
}
