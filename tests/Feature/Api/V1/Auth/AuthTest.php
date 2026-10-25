<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\ApiTestCase;

class AuthTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('auth-login|' . request()->ip());
    }

    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'auth.test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'auth.test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonStructure(['token', 'user', 'permissions']);
    }

    /**
     * Test login failure with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'bad.auth@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'bad.auth@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials');
    }

    /**
     * Test standardized 401 response for unauthenticated requests.
     */
    public function test_unauthenticated_request_returns_standardized_401(): void
    {
        $response = $this->getJson(route('auth.me'));

        $response->assertStatus(401)
            ->assertJsonStructure(['success', 'message'])
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * Test login rate limiter.
     */
    public function test_login_rate_limiter_blocks_after_five_attempts(): void
    {
        User::factory()->create([
            'email' => 'ratelimit@example.com',
            'password' => Hash::make('password123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('auth.login'), [
                'email' => 'ratelimit@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson(route('auth.login'), [
            'email' => 'ratelimit@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'retry_after']);
    }

    /**
     * Test retrieving the current user profile and status.
     */
    public function test_user_can_retrieve_current_user_profile(): void
    {
        $user = $this->actingAsRole('receptionist');

        $this->getJson(route('auth.me'))
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->getJson(route('auth.status'))
            ->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user_id', $user->id);
    }

    /**
     * Test logout.
     */
    public function test_user_can_logout_and_revoke_token(): void
    {
        $this->actingAsRole('receptionist');

        $this->postJson(route('auth.logout'))
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully');
    }

    /**
     * Test logout from all devices.
     */
    public function test_user_can_logout_all_devices(): void
    {
        $this->actingAsRole('receptionist');

        $this->postJson(route('auth.logout-all'))
            ->assertOk()
            ->assertJsonPath('message', 'Logged out from all devices');
    }

    /**
     * Test token refresh.
     */
    public function test_user_can_refresh_token(): void
    {
        $this->actingAsRole('receptionist');

        $this->postJson(route('auth.refresh-token'))
            ->assertOk()
            ->assertJsonPath('message', 'Token refreshed successfully')
            ->assertJsonStructure(['token']);
    }

    /**
     * Test password change with a valid new password.
     */
    public function test_user_can_change_password(): void
    {
        $tenant = $this->createTestTenant();
        tenancy()->initialize($tenant);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => Hash::make('OldPassword1!'),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson(route('auth.change-password'), [
            'current_password' => 'OldPassword1!',
            'new_password' => 'NewPassword2@',
            'new_password_confirmation' => 'NewPassword2@',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Password changed successfully');

        $user->refresh();

        $this->assertTrue(
            Hash::check('NewPassword2@', $user->password)
        );
    }

    /**
     * Test rejection of a weak new password.
     */
    public function test_weak_password_is_rejected_on_change_password(): void
    {
        $tenant = $this->createTestTenant();
        tenancy()->initialize($tenant);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => Hash::make('OldPassword1!'),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson(route('auth.change-password'), [
            'current_password' => 'OldPassword1!',
            'new_password' => 'simple123',
            'new_password_confirmation' => 'simple123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }
}
