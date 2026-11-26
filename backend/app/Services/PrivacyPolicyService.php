<?php

namespace App\Services;

use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyAcceptance;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PrivacyPolicyService
{
    public function getCurrentPolicy(): ?PrivacyPolicy
    {
        return PrivacyPolicy::current()->latest('id')->first();
    }

    public function hasUserAcceptedCurrentPolicy(User $user, ?PrivacyPolicy $policy = null): bool
    {
        $policy ??= $this->getCurrentPolicy();

        if (! $policy) {
            return true;
        }

        return PrivacyPolicyAcceptance::where('user_id', $user->id)
            ->where('privacy_policy_id', $policy->id)
            ->exists();
    }

    public function getStatusForUser(User $user): array
    {
        $policy = $this->getCurrentPolicy();

        if (! $policy || ! $policy->is_enabled || $user->isAdmin()) {
            return [
                'should_show' => false,
                'has_accepted' => $policy ? $this->hasUserAcceptedCurrentPolicy($user, $policy) : true,
                'policy' => null,
            ];
        }

        $hasAccepted = $this->hasUserAcceptedCurrentPolicy($user, $policy);

        return [
            'should_show' => ! $hasAccepted,
            'has_accepted' => $hasAccepted,
            'policy' => [
                'id' => $policy->id,
                'version' => $policy->version,
                'title' => $policy->title,
                'content' => $policy->content,
            ],
        ];
    }

    public function acceptCurrentPolicy(User $user, ?string $ipAddress = null): ?PrivacyPolicyAcceptance
    {
        $policy = $this->getCurrentPolicy();

        if (! $policy) {
            return null;
        }

        return PrivacyPolicyAcceptance::firstOrCreate(
            [
                'user_id' => $user->id,
                'privacy_policy_id' => $policy->id,
            ],
            [
                'accepted_at' => now(),
                'ip_address' => $ipAddress,
            ]
        );
    }

    public function updateConfiguration(array $data): PrivacyPolicy
    {
        return DB::transaction(function () use ($data) {
            $current = $this->getCurrentPolicy();

            // Se uma nova versão diferente da atual for enviada, cria um novo registro vigente
            if (! $current || (isset($data['version']) && $data['version'] !== $current->version)) {
                PrivacyPolicy::current()->update(['is_current' => false]);

                return PrivacyPolicy::create([
                    'version' => $data['version'] ?? '1.0',
                    'title' => $data['title'] ?? ($current?->title ?? 'Política de Privacidade'),
                    'content' => $data['content'] ?? ($current?->content ?? ''),
                    'is_enabled' => $data['is_enabled'] ?? ($current?->is_enabled ?? true),
                    'is_current' => true,
                ]);
            }

            $current->fill([
                'title' => $data['title'] ?? $current->title,
                'content' => $data['content'] ?? $current->content,
                'is_enabled' => array_key_exists('is_enabled', $data) ? (bool) $data['is_enabled'] : $current->is_enabled,
            ]);
            $current->save();

            return $current->fresh();
        });
    }

    public function getUsersWithAcceptanceStatus(?string $status = null, ?string $search = null): Collection
    {
        $currentPolicy = $this->getCurrentPolicy();
        $policyId = $currentPolicy?->id;

        $query = User::query()
            ->where('role', 'customer')
            ->with(['privacyAcceptances' => function ($q) use ($policyId) {
                if ($policyId) {
                    $q->where('privacy_policy_id', $policyId);
                }
            }])
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($policyId && $status === 'accepted') {
            $query->whereHas('privacyAcceptances', fn ($q) => $q->where('privacy_policy_id', $policyId));
        } elseif ($policyId && $status === 'pending') {
            $query->whereDoesntHave('privacyAcceptances', fn ($q) => $q->where('privacy_policy_id', $policyId));
        }

        return $query->get()->map(function (User $user) use ($currentPolicy) {
            $acceptance = $user->privacyAcceptances->first();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'accepted' => (bool) $acceptance,
                'accepted_at' => $acceptance?->accepted_at?->toIso8601String(),
                'policy_version' => $acceptance ? $currentPolicy?->version : null,
                'created_at' => $user->created_at?->toIso8601String(),
            ];
        });
    }
}
