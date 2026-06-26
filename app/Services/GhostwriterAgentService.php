<?php

namespace App\Services;

use App\AI\Agents\GhostwriterAgent;
use App\Models\GeneratedPost;
use App\Models\User;

class GhostwriterAgentService
{
    public function __construct(
        private GhostwriterAgent $agent
    ) {}

    public function chat(
        GeneratedPost $post,
        User $user,
        string $message
    ): array {
        // Nouvelle conversation ou continuation ?
        if ($post->ai_conversation_id) {
            // Continue la conversation existante
            $this->agent->continue($post->ai_conversation_id, $user);
        } else {
            // Démarre une nouvelle conversation pour cet utilisateur
            $this->agent->forUser($user);
        }

        // Envoie le message à l'agent
        $response = $this->agent->prompt($message);

        // Sauvegarde le conversationId pour les prochains échanges
        if (!$post->ai_conversation_id && $response->conversationId) {
            $post->update([
                'ai_conversation_id' => $response->conversationId,
            ]);
        }

        return [
            'message'         => $response->text,
            'conversation_id' => $response->conversationId ?? $post->ai_conversation_id,
        ];
    }
}