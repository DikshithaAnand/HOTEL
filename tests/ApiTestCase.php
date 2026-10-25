<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected ?Tenant $testTenant = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

   
    protected function createTestTenant(): Tenant
    {
        if ($this->testTenant) {
            return $this->testTenant;
        }

        $this->testTenant = Tenant::create([
            'id' => 'test-' . uniqid(),
            'name' => 'Test Hotel Group',
            'email' => 'test-' . uniqid() . '@example.com',
            'subscription_plan' => 'test',
            'is_active' => true,
        ]);

        return $this->testTenant;
    }

    /**
     * Authenticate a user via Sanctum.
     */
    protected function actingAsUser(User $user, array $abilities = ['*']): self
    {
        Sanctum::actingAs($user, $abilities);

        return $this;
    }

    /**
     * Create and authenticate a user with a specific role.
     */
    protected function actingAsRole(string $role): User
    {
        $tenant = $this->createTestTenant();

        tenancy()->initialize($tenant);

        $this->artisan('db:seed', [
            '--class' => 'RolesAndPermissionsSeeder',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $user->assignRole($role);

        $this->actingAsUser($user);

        return $user;
    }
}
