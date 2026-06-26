<?php

namespace App\Http\Controllers\Api;

use App\Enums\RawContentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Content\StoreRawContentRequest;
use App\Http\Resources\RawContentResource;
use App\Jobs\ProcessRawContentJob;
use App\Models\CampaignBlueprint;
use Illuminate\Http\JsonResponse;

/**
 * @group Repurposing IA
 *
 * Soumission de contenu brut et transformation asynchrone via Grok.
 */

class RawContentController extends Controller
{
    /**
     * Soumettre un contenu brut
     *
     * Enregistre le contenu et déclenche un Job asynchrone de transformation via Grok.
     * La réponse est immédiate (202 Accepted) — le post généré sera disponible
     * via GET /api/posts une fois le traitement terminé.
     *
     * @response 202 {
     *   "message": "Content submitted. Processing in background.",
     *   "raw_content": {
     *     "id": 1,
     *     "status": "pending",
     *     "campaign_blueprint_id": 1,
     *     "created_at": "2026-06-25 10:00:00"
     *   }
     * }
     * @response 403 {
     *   "message": "This blueprint does not belong to you"
     * }
     * @response 422 {
     *   "message": "The content field is required.",
     *   "errors": {
     *     "content": ["The content field is required."]
     *   }
     * }
     */
    public function store(StoreRawContentRequest $request)
    {
        $blueprint = CampaignBlueprint::findOrFail(
            $request->validated('campaign_blueprint_id')
        );

        if ($blueprint->user_id !== $request->user()->id) {
            abort(403, 'This blueprint does not belong to you');
        }

        $rawContent = $request->user()->rawContents()->create([
            'content'               => $request->validated('content'),
            'campaign_blueprint_id' => $blueprint->id,
            'status'                => RawContentStatusEnum::Pending,
        ]);

        ProcessRawContentJob::dispatch($rawContent);

        return response()->json([
            'message'     => 'Content submitted. Processing in background.',
            'raw_content' => new RawContentResource($rawContent),
        ], 202);
    }
}