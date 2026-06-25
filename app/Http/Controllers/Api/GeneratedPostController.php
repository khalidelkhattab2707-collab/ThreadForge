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

class GeneratedPostController extends Controller
{
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

    public function show(Request $request, GeneratedPost $post): JsonResponse
    {
        $post->load('rawContent');

        $this->authorizeOwnership($request->user(), $post);

        return response()->json(new GeneratedPostResource($post));
    }

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