<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthAndAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['username' => $user->username, 'password' => 'salah'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_email_cannot_be_used_to_log_in(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('username');
        $this->post('/login', ['username' => $user->email, 'password' => 'password'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_repeated_login_attempts_are_throttled(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => $user->username, 'password' => 'salah']);
        }

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])->assertTooManyRequests();
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('avatar.store'), ['avatar' => UploadedFile::fake()->image('foto.png', 200, 200)])
            ->assertRedirect();

        $path = $user->fresh()->avatar_path;
        $this->assertStringStartsWith('avatars/', $path);
        $this->assertStringEndsNotWith('foto.png', $path);
        Storage::disk('local')->assertExists($path);

        $this->actingAs($user)->get(route('avatar.show', $user))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_script_disguised_as_image_is_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $shell = UploadedFile::fake()->createWithContent('shell.php.jpg', '<?php system($_GET["c"]); ?>');

        $this->actingAs($user)->post(route('avatar.store'), ['avatar' => $shell])->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_svg_is_rejected(): void
    {
        Storage::fake('local');
        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>');

        $this->actingAs(User::factory()->create())->post(route('avatar.store'), ['avatar' => $svg])->assertSessionHasErrors('avatar');
    }
}
