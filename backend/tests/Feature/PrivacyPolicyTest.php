<?php

namespace Tests\Feature;

use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
  use RefreshDatabase;

    private function createActivePolicy(array $overrides = []): PrivacyPolicy
    {
        return PrivacyPolicy::create(array_merge([
            'version' => '1.0',
            'title' => 'Política de Privacidade',
            'content' => 'Texto vigente dos termos de privacidade.',
            'is_current' => true,
            'is_enabled' => true,
        ], $overrides));
    }

    public function test_customer_without_acceptance_must_see_modal_after_login(): void
    {
        $this->createActivePolicy();

        User::factory()->create([
            'email' => 'cliente@teste.com',
            'password' => 'senha123',
            'role' => 'customer',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'cliente@teste.com',
            'password' => 'senha123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user', 'privacy_policy'])
            ->assertJsonPath('privacy_policy.should_show', true)
            ->assertJsonPath('privacy_policy.has_accepted', false)
            ->assertJsonPath('privacy_policy.policy.version', '1.0');
    }

    public function test_customer_acceptance_is_persisted_and_prevents_repeated_notifications(): void
    {
        $policy = $this->createActivePolicy();
        $customer = User::factory()->create(['role' => 'customer']);

        Sanctum::actingAs($customer);

        // Primeiro aceite
        $this->postJson('/api/privacy-policy/accept')
            ->assertOk()
            ->assertJsonStructure(['message', 'accepted_at']);

        // Chamada repetida não deve duplicar registro no banco
        $this->postJson('/api/privacy-policy/accept')->assertOk();

        $this->assertDatabaseCount('privacy_policy_acceptances', 1);
        $this->assertDatabaseHas('privacy_policy_acceptances', [
            'user_id' => $customer->id,
            'privacy_policy_id' => $policy->id,
        ]);

        // Status subsequente não deve exibir o modal novamente
        $this->getJson('/api/privacy-policy/status')
            ->assertOk()
            ->assertJsonPath('should_show', false)
            ->assertJsonPath('has_accepted', true);
    }

    public function test_modal_is_hidden_when_admin_disables_notification(): void
    {
        $this->createActivePolicy(['is_enabled' => true]);
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        Sanctum::actingAs($admin);
        $this->putJson('/api/admin/privacy-policy', ['is_enabled' => false])
            ->assertOk()
            ->assertJsonPath('data.is_enabled', false);

        Sanctum::actingAs($customer);
        $this->getJson('/api/privacy-policy/status')
            ->assertOk()
            ->assertJsonPath('should_show', false);
    }

    public function test_new_policy_version_requires_new_acceptance_from_previous_acceptors(): void
    {
        $policyV1 = $this->createActivePolicy(['version' => '1.0']);
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        PrivacyPolicyAcceptance::create([
            'user_id' => $customer->id,
            'privacy_policy_id' => $policyV1->id,
            'accepted_at' => now(),
        ]);

        // Admin publica versão 2.0
        Sanctum::actingAs($admin);
        $this->putJson('/api/admin/privacy-policy', [
            'version' => '2.0',
            'title' => 'Política Atualizada v2.0',
            'content' => 'Novos termos vigentes.',
            'is_enabled' => true,
        ])->assertOk();

        // Cliente que aceitou a 1.0 precisa aceitar a 2.0
        Sanctum::actingAs($customer);
        $this->getJson('/api/privacy-policy/status')
            ->assertOk()
            ->assertJsonPath('should_show', true)
            ->assertJsonPath('has_accepted', false)
            ->assertJsonPath('policy.version', '2.0');
    }

    public function test_admin_grid_returns_users_acceptance_status_and_supports_filters(): void
    {
        $policy = $this->createActivePolicy();
        $admin = User::factory()->create(['role' => 'admin']);
        $acceptedUser = User::factory()->create(['name' => 'Ana Aceita', 'role' => 'customer']);
        $pendingUser = User::factory()->create(['name' => 'Carlos Pendente', 'role' => 'customer']);

        PrivacyPolicyAcceptance::create([
            'user_id' => $acceptedUser->id,
            'privacy_policy_id' => $policy->id,
            'accepted_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        // Listagem completa
        $this->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonPath('summary.total', 2)
            ->assertJsonPath('summary.accepted', 1)
            ->assertJsonPath('summary.pending', 1);

        // Filtro apenas pendentes
        $this->getJson('/api/admin/users?status=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', $pendingUser->email)
            ->assertJsonPath('data.0.accepted', false);
    }

    public function test_customer_cannot_access_admin_endpoints(): void
    {
        $this->createActivePolicy();
        $customer = User::factory()->create(['role' => 'customer']);

        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/users')->assertForbidden();
        $this->putJson('/api/admin/privacy-policy', ['is_enabled' => false])->assertForbidden();
    }

    public function test_unauthenticated_api_request_returns_json_401(): void
    {
        $this->get('/api/privacy-policy/status')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }
}
