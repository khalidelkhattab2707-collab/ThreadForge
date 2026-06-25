<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RawContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'status'                => $this->status->value,
            'campaign_blueprint_id' => $this->campaign_blueprint_id,
            'created_at'            => $this->created_at->toDateTimeString(),
            // content volontairement absent — trop lourd, l'utilisateur l'a déjà
        ];
    }
}