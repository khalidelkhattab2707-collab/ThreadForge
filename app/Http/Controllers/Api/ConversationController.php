<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\StoreChatMessageRequest;
use App\Models\GeneratedPost;
use App\Services\GhostwriterAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
/**
 * @group Ghostwriter Agent
 *
 * Agent conversationnel avec mémoire pour affiner les posts générés.
 */

class ConversationController extends Controller
{
    public function __construct(
        private GhostwriterAgentService $agentService
    ) {}
   /**
     * Envoyer un message à l'agent
     *
     * Envoie un message à l'agent Ghostwriter pour affiner un post généré.
     * L'agent utilise des Tools PHP réels pour éviter les hallucinations
     * et maintient la mémoire de conversation via le SDK laravel/ai.
     *
     * @response 200 {
     *   "data": {
     *     "message": "Voici 3 variantes plus agressives pour le hook...",
     *     "conversation_id": "01922d4e-ab12-7000-8000-abc123def456"
     *   }
     * }
     * @response 403 {
     *   "message": "Forbidden"
     * }
     */
    public function store(
        StoreChatMessageRequest $request,
        int $postId
    ): JsonResponse {
        $post = GeneratedPost::with('rawContent')->findOrFail($postId);

        // Vérifie que le post appartient à l'utilisateur
        if ($post->rawContent->user_id !== $request->user()->id) {
            abort(403, 'Forbidden');
        }

        $result = $this->agentService->chat(
            post: $post,
            user: $request->user(),
            message: $request->validated('message')
        );

        return response()->json([
            'data' => [
                'message'         => $result['message'],
                'conversation_id' => $result['conversation_id'],
            ]
        ]);
    }
}