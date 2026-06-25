<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignBlueprintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'target_audience'    => $this->target_audience,
            'tone'               => $this->tone,
            'max_length'         => $this->max_length,
            'max_hashtags'       => $this->max_hashtags,
            'forbidden_words'    => $this->forbidden_words ?? [],
            'raw_contents_count' => $this->whenCounted('rawContents'),
            'created_at'         => $this->created_at->toDateTimeString(),
        ];
    }
}
