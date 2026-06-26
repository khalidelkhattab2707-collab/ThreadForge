<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

/**
     * Inscription
     *
     * Crée un nouveau compte créateur et retourne un Bearer Token Sanctum.
     *
     * @unauthenticated
     *
     * @response 201 {
     *   "user": {
     *     "id": 1,
     *     "name": "Khalid Dev",
     *     "email": "khalid@threadforge.io"
     *   },
     *   "token": "1|abc123xyz..."
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'password' => bcrypt($request->validated('password')),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Connexion
     *
     * Authentifie un créateur et retourne un Bearer Token Sanctum.
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "Khalid Dev",
     *     "email": "khalid@threadforge.io"
     *   },
     *   "token": "1|abc123xyz..."
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */

    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ]);
    }
     /**
     * Déconnexion
     *
     * Révoque le Bearer Token courant.
     *
     * @response 200 {
     *   "message": "Logged out successfully"
     * }
     */

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
    
}