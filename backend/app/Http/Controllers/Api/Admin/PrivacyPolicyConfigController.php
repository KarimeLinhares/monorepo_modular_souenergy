<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePrivacyPolicyRequest;
use App\Services\PrivacyPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrivacyPolicyConfigController extends Controller
{
    public function __construct(private readonly PrivacyPolicyService $privacyService) {}

    public function show(): JsonResponse
    {
        $policy = $this->privacyService->getCurrentPolicy();

        if (! $policy) {
            return response()->json(['message' => 'Política não configurada.'], 404);
        }

        return response()->json(['data' => $policy]);
    }

    public function update(UpdatePrivacyPolicyRequest $request): JsonResponse
    {
        $policy = $this->privacyService->updateConfiguration($request->validated());

        return response()->json([
            'message' => 'Configuração da política atualizada com sucesso.',
            'data' => $policy,
        ]);
    }
}
