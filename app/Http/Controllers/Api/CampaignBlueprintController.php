<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blueprint\StoreBlueprintRequest;
use App\Http\Resources\CampaignBlueprintResource;
use App\Models\CampaignBlueprint;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignBlueprintController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $blueprints = $request->user()
            ->campaignBlueprints()
            ->withCount('rawContents')
            ->latest()
            ->get();

        return CampaignBlueprintResource::collection($blueprints);
    }

    public function store(StoreBlueprintRequest $request): JsonResponse
    {
        $blueprint = $request->user()
            ->campaignBlueprints()
            ->create($request->validated());

        return response()->json(
            new CampaignBlueprintResource($blueprint),
            201
        );
    }

    public function show(Request $request, CampaignBlueprint $blueprint): JsonResponse
    {
        $this->authorizeOwnership($request->user(), $blueprint);

        return response()->json(
            new CampaignBlueprintResource($blueprint)
        );
    }

    private function authorizeOwnership(User $user, CampaignBlueprint $blueprint): void
    {
        if ($blueprint->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
    }
}
