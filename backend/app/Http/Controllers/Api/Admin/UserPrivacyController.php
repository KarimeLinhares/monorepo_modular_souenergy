<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\PrivacyPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPrivacyController extends Controller
{
    public function __construct(private readonly PrivacyPolicyService $privacyService) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status'); // 'accepted' | 'pending' | null
        $search = $request->query('search');

        $currentPolicy = $this->privacyService->getCurrentPolicy();
        $users = $this->privacyService->getUsersWithAcceptanceStatus($status, $search);

        return response()->json([
            'current_policy' => $currentPolicy ? [
                'version' => $currentPolicy->version,
                'is_enabled' => $currentPolicy->is_enabled,
            ] : null,
            'summary' => [
                'total' => $users->count(),
                'accepted' => $users->where('accepted', true)->count(),
                'pending' => $users->where('accepted', false)->count(),
            ],
            'data' => $users->values(),
        ]);
    }
}
