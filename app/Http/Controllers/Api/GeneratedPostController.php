<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\UpdatePostStatusRequest;
use App\Http\Resources\GeneratedPostResource;
use App\Models\GeneratedPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Posts Générés
 *
 * Gestion du cycle de vie des posts générés par l'IA.
 */

class GeneratedPostController extends Controller
{

    /**
     * Liste des posts générés
     *
     * Retourne tous les posts générés appartenant à l'utilisateur authentifié.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "hook_propose": "Tu bloques tes users 30s pour un appel API ?",
     *       "body_points": ["Les Jobs Laravel exécutent les tâches en background"],
     *       "technical_readability_score": 85,
     *       "suggested_hashtags": ["#Laravel", "#Backend"],
     *       "tone_compliance_justification": "Ton direct et professionnel...",
     *       "status": "draft",
     *       "raw_content": {
     *         "id": 1,
     *         "status": "done"
     *       },
     *       "created_at": "2026-06-25 10:00:00"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = GeneratedPost::whereHas('rawContent', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with('rawContent')
            ->latest()
            ->get();

        return GeneratedPostResource::collection($posts);
    }

    /**
     * Détail d'un post généré
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "hook_propose": "Tu bloques tes users 30s pour un appel API ?",
     *     "body_points": ["Les Jobs Laravel exécutent les tâches en background"],
     *     "technical_readability_score": 85,
     *     "suggested_hashtags": ["#Laravel", "#Backend"],
     *     "tone_compliance_justification": "Ton direct et professionnel...",
     *     "status": "draft",
     *     "raw_content": {
     *       "id": 1,
     *       "status": "done"
     *     },
     *     "created_at": "2026-06-25 10:00:00"
     *   }
     * }
     * @response 403 {
     *   "message": "Forbidden"
     * }
     */

    public function show(int $id, Request $request): JsonResponse
    {
        $post = GeneratedPost::with('rawContent')->findOrFail($id);
        $this->authorizeOwnership($request->user(), $post);

        return response()->json(new GeneratedPostResource($post));
    }
    /**
     * Mettre à jour le statut
     *
     * Change le statut éditorial d'un post généré.
     * Statuts disponibles : draft, posted, archived.
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "status": "posted"
     *   }
     * }
     * @response 422 {
     *   "message": "The selected status is invalid.",
     *   "errors": {
     *     "status": ["The selected status is invalid."]
     *   }
     * }
     */

    public function updateStatus(
        UpdatePostStatusRequest $request,
        GeneratedPost $post
    ): JsonResponse {
        $post->load('rawContent');

        $this->authorizeOwnership($request->user(), $post);

        $post->update(['status' => $request->validated('status')]);

        return response()->json(new GeneratedPostResource($post->fresh('rawContent')));
    }

    private function authorizeOwnership(User $user, GeneratedPost $post): void
    {
        if ($post->rawContent->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }
    }
}