<?php

namespace App\Services;

use App\Models\CampaignBlueprint;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\AnonymousAgent;

class GrokRepurposingService
{
    /**
     * Transforme un contenu brut en post structuré via Grok.
     *
     * @throws \RuntimeException si le contrat JSON n'est pas respecté
     */
    public function repurpose(string $rawContent, CampaignBlueprint $blueprint): array
    {
        $agent = AnonymousAgent::make(
            instructions: $this->buildInstructions($blueprint),
            messages: [],
            tools: []
        );

        $response = $agent->prompt($this->buildPrompt($rawContent, $blueprint));

        $raw = $response->text;

        // Nettoie les backticks markdown si Grok en ajoute
        $cleaned = preg_replace('/^```json\s*/i', '', $raw);
        $cleaned = preg_replace('/```$/', '', $cleaned);
        $cleaned = trim($cleaned);

        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'Grok response is not valid JSON: ' . json_last_error_msg() . ' | Raw: ' . $raw
            );
        }

        $this->validateContract($data);

        return $data;
    }

    private function buildInstructions(CampaignBlueprint $blueprint): string
    {
        $forbidden = !empty($blueprint->forbidden_words)
            ? implode(', ', $blueprint->forbidden_words)
            : 'aucun';

        return <<<INSTRUCTIONS
        Tu es un expert en personal branding pour développeurs sur X (Twitter).

        RÈGLES STRICTES DU BLUEPRINT "{$blueprint->name}" :
        - Audience cible : {$blueprint->target_audience}
        - Ton rédactionnel : {$blueprint->tone}
        - Longueur maximale du hook : {$blueprint->max_length} caractères
        - Nombre maximum de hashtags : {$blueprint->max_hashtags}
        - Mots interdits : {$forbidden}

        RÈGLE ABSOLUE DE FORMAT :
        Tu dois TOUJOURS répondre UNIQUEMENT avec un objet JSON valide.
        Aucun texte avant ou après le JSON.
        Aucun bloc markdown (pas de ```json).
        Voici le schéma exact que tu dois respecter :

        {
            "hook_propose": "string (max {$blueprint->max_length} caractères)",
            "body_points": ["string", "string"],
            "technical_readability_score": 0,
            "suggested_hashtags": ["string"],
            "tone_compliance_justification": "string"
        }
        INSTRUCTIONS;
    }

    private function buildPrompt(string $content, CampaignBlueprint $blueprint): string
    {
        return <<<PROMPT
        CONTENU BRUT À TRANSFORMER :
        {$content}

        Génère le JSON de transformation en respectant EXACTEMENT les règles de tes instructions.
        PROMPT;
    }

    private function validateContract(array $data): void
    {
        $requiredKeys = [
            'hook_propose',
            'body_points',
            'technical_readability_score',
            'suggested_hashtags',
            'tone_compliance_justification',
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \RuntimeException(
                    "Structured Output invalide : clé manquante '{$key}'"
                );
            }
        }

        if (!is_string($data['hook_propose'])) {
            throw new \RuntimeException('hook_propose doit être une string');
        }

        if (strlen($data['hook_propose']) > 280) {
            throw new \RuntimeException(
                'hook_propose dépasse 280 caractères (' . strlen($data['hook_propose']) . ')'
            );
        }

        if (!is_array($data['body_points'])) {
            throw new \RuntimeException('body_points doit être un tableau');
        }

        if (!is_int($data['technical_readability_score'])) {
            throw new \RuntimeException('technical_readability_score doit être un entier');
        }

        if (!is_array($data['suggested_hashtags'])) {
            throw new \RuntimeException('suggested_hashtags doit être un tableau');
        }
    }
}