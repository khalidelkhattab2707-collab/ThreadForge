<?php

namespace App\Http\Controllers\Api;

use App\Enums\RawContentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Content\StoreRawContentRequest;
use App\Http\Resources\RawContentResource;
use App\Jobs\ProcessRawContentJob;
use App\Models\CampaignBlueprint;
use Illuminate\Http\JsonResponse;

class RawContentController extends Controller
{
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