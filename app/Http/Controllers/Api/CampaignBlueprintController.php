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

/**
 * @group Campaign Blueprints
 *
 * Gestion des configurations de style réutilisables pour la génération de posts.
 */

class CampaignBlueprintController extends Controller
{
    /**
     * Liste des Blueprints
     *
     * Retourne tous les Blueprints de l'utilisateur authentifié
     * avec le nombre de contenus bruts associés.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Tech Twitter FR",
     *       "target_audience": "Développeurs Laravel francophones",
     *       "tone": "Professionnel mais décontracté",
     *       "max_length": 280,
     *       "max_hashtags": 2,
     *       "forbidden_words": ["facile", "simple"],
     *       "raw_contents_count": 3,
     *       "created_at": "2026-06-25 10:00:00"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $blueprints = $request->user()
            ->campaignBlueprints()
            ->withCount('rawContents')
            ->latest()
            ->get();

        return CampaignBlueprintResource::collection($blueprints);
    }
    /**
     * Créer un Blueprint
     *
     * Crée une nouvelle configuration de style pour les futures générations.
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "name": "Tech Twitter FR",
     *     "target_audience": "Développeurs Laravel francophones",
     *     "tone": "Professionnel mais décontracté",
     *     "max_length": 280,
     *     "max_hashtags": 2,
     *     "forbidden_words": ["facile", "simple"],
     *     "created_at": "2026-06-25 10:00:00"
     *   }
     * }
     * @response 422 {
     *   "message": "The name field is required.",
     *   "errors": {
     *     "name": ["The name field is required."]
     *   }
     * }
     */

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
     /**
     * Détail d'un Blueprint
     *
     * Retourne les détails d'un Blueprint spécifique appartenant à l'utilisateur.
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Tech Twitter FR",
     *     "target_audience": "Développeurs Laravel francophones",
     *     "tone": "Professionnel mais décontracté",
     *     "max_length": 280,
     *     "max_hashtags": 2,
     *     "forbidden_words": ["facile", "simple"],
     *     "created_at": "2026-06-25 10:00:00"
     *   }
     * }
     * @response 403 {
     *   "message": "Forbidden"
     * }
     */

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
