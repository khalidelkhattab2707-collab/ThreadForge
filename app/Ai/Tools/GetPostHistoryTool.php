<?php

namespace App\AI\Tools;

use App\Models\GeneratedPost;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetPostHistoryTool implements Tool
{
    public function description(): string
    {
        return 'Récupère les informations complètes d\'un post généré par son ID. '
             . 'Utilise cet outil pour accéder au hook, aux body points, au score de lisibilité '
             . 'et aux hashtags suggérés avant de proposer des variantes.';
    }

    public function handle(Request $request): string
    {
        $post = GeneratedPost::with('rawContent')->find($request['post_id']);

        if (!$post) {
            return json_encode(['error' => 'Post introuvable']);
        }

        return json_encode([
            'id'                            => $post->id,
            'hook_propose'                  => $post->hook_propose,
            'body_points'                   => $post->body_points,
            'technical_readability_score'   => $post->technical_readability_score,
            'suggested_hashtags'            => $post->suggested_hashtags,
            'tone_compliance_justification' => $post->tone_compliance_justification,
            'status'                        => $post->status->value,
            'raw_content'                   => $post->rawContent->content,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'post_id' => $schema->integer()
                ->description('ID du post généré à consulter')
                ->required(),
        ];
    }
}