<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PrivacyPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function __construct(private readonly PrivacyPolicyService $privacyService) {}

    public function status(Request $request): JsonResponse
    {
        return response()->json(
            $this->privacyService->getStatusForUser($request->user())
        );
    }

    public function accept(Request $request): JsonResponse
    {
        $acceptance = $this->privacyService->acceptCurrentPolicy(
            $request->user(),
            $request->ip()
        );

        if (! $acceptance) {
            return response()->json([
                'message' => 'Nenhuma política de privacidade vigente encontrada.',
            ], 404);
        }

        return response()->json([
            'message' => 'Termos de privacidade aceitos com sucesso.',
            'accepted_at' => $acceptance->accepted_at->toIso8601String(),
        ]);
    }
}
