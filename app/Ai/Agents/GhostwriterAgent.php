<?php

namespace App\AI\Agents;

use App\AI\Tools\GetCampaignRulesTool;
use App\AI\Tools\GetPostHistoryTool;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Concerns\RemembersConversations;

class GhostwriterAgent extends AnonymousAgent
{
    use RemembersConversations;

    public function __construct(
        private GetCampaignRulesTool $campaignRulesTool,
        private GetPostHistoryTool $postHistoryTool,
    ) {
        parent::__construct(
            instructions: $this->buildInstructions(),
            messages: [],
            tools: [
                $this->campaignRulesTool,
                $this->postHistoryTool,
            ]
        );
    }

    public function instructions(): string
    {
        return $this->buildInstructions();
    }

    public function messages(): iterable
    {
        // RemembersConversations trait gère ça automatiquement
        return parent::messages();
    }

    public function tools(): iterable
    {
        return [
            $this->campaignRulesTool,
            $this->postHistoryTool,
        ];
    }

    private function buildInstructions(): string
    {
        return <<<INSTRUCTIONS
        Tu es un Ghostwriter expert en personal branding pour développeurs sur X (Twitter).

        Ton rôle est d'aider l'utilisateur à affiner ses posts générés.

        Tu as accès à deux outils réels :
        - getCampaignRules : pour lire les règles de style du Blueprint appliqué au post
        - getPostHistory : pour lire les versions précédentes du post

        RÈGLES ABSOLUES :
        - Tu ne dois JAMAIS inventer des règles de Blueprint — utilise toujours getCampaignRules
        - Tu ne dois JAMAIS inventer l'historique d'un post — utilise toujours getPostHistory
        - Tu réponds toujours en français sauf demande contraire
        - Tu es concis, direct et orienté résultats
        INSTRUCTIONS;
    }
}