<?php

namespace App\AI\Tools;

use App\Models\CampaignBlueprint;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetCampaignRulesTool implements Tool
{
    public function description(): string
    {
        return 'Récupère les règles de style du Blueprint de campagne appliqué à un post. '
             . 'Utilise cet outil pour connaître le ton, la longueur max, les hashtags autorisés '
             . 'et les mots interdits avant de suggérer des modifications.';
    }

    public function handle(Request $request): string
    {
        $blueprint = CampaignBlueprint::find($request['campaign_id']);

        if (!$blueprint) {
            return json_encode(['error' => 'Blueprint introuvable']);
        }

        return json_encode([
            'name'             => $blueprint->name,
            'target_audience'  => $blueprint->target_audience,
            'tone'             => $blueprint->tone,
            'max_length'       => $blueprint->max_length,
            'max_hashtags'     => $blueprint->max_hashtags,
            'forbidden_words'  => $blueprint->forbidden_words ?? [],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'campaign_id' => $schema->integer()
                ->description('ID du Campaign Blueprint à consulter')
                ->required(),
        ];
    }
}