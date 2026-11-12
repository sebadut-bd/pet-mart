<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UploadImageTest extends TestCase
{
    private const URL = '/api/v1/uploads/images';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function actAsUser(): void
    {
        $user = new User();
        $user->id = 999999;
        Sanctum::actingAs($user);
    }

    public function test_guest_cannot_upload(): void
    {
        $this->postJson(self::URL, ['image' => UploadedFile::fake()->image('a.jpg', 400, 300)])
            ->assertUnauthorized();
    }

    public function test_user_can_upload_jpeg_and_gets_path_and_url(): void
    {
        $this->actAsUser();

        $res = $this->postJson(self::URL, ['image' => UploadedFile::fake()->image('../../evil name.jpg', 800, 600)])
            ->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['path', 'url']]);

        $path = $res->json('data.path');
        $this->assertMatchesRegularExpression('#^products/\d{4}/\d{2}/[0-9a-f-]{36}\.jpg$#', $path);
        $this->assertStringNotContainsString('evil', $path);
        $this->assertLessThanOrEqual(255, strlen($path));
        Storage::disk('public')->assertExists($path);
    }

    public function test_png_and_webp_are_accepted(): void
    {
        $this->actAsUser();
        $this->postJson(self::URL, ['image' => UploadedFile::fake()->image('a.png', 400, 300)])->assertCreated();
        $this->postJson(self::URL, ['image' => UploadedFile::fake()->image('a.webp', 400, 300)])->assertCreated();
    }

    public function test_svg_is_rejected(): void
    {
        $this->actAsUser();
        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->postJson(self::URL, ['image' => $svg])->assertUnprocessable()->assertJsonValidationErrors('image');
    }

    public function test_non_image_is_rejected(): void
    {
        $this->actAsUser();
        $file = UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf');
        $this->postJson(self::URL, ['image' => $file])->assertUnprocessable()->assertJsonValidationErrors('image');
    }

    public function test_too_small_image_is_rejected(): void
    {
        $this->actAsUser();
        $this->postJson(self::URL, ['image' => UploadedFile::fake()->image('a.jpg', 50, 50)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_file_over_5mb_is_rejected(): void
    {
        $this->actAsUser();
        $big = UploadedFile::fake()->image('big.jpg', 1000, 1000)->size(6000);
        $this->postJson(self::URL, ['image' => $big])->assertUnprocessable()->assertJsonValidationErrors('image');
    }
}
