<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\StoreChatMessageRequest;
use App\Models\GeneratedPost;
use App\Services\GhostwriterAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        private GhostwriterAgentService $agentService
    ) {}

    public function store(int $id,
        StoreChatMessageRequest $request
    ): JsonResponse {
        $post = GeneratedPost::with('rawContent')->findOrFail($id);

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