<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Laravel\Prompts\error;

class AuthController extends Controller
{
    public function login(UserLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }
        $user = auth('api')->user();
        if ($user->role !== "admin") {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);
        $cookie = cookie(
            "token",
            $token,
            60 * 24 * 7,
            "/",
            null,
            false,
            true
        );

        return  response()->json([
            'access_token' => $token,
            'status' => 200,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60 // seconds
        ])->cookie($cookie);
    }

    public function logout()
    {
        try {
            auth()->logout();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout, please try again'], 500);
        }

        // Clear refresh token cookie
        return response()
            ->json(['message' => 'Successfully logged out', 'status' => 200])
            ->cookie('token', '', -1);
    }

    public function me()
    {
        try {
            $user = auth('api')->user();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unauthorized',
                'error'   => $e->getMessage(),
                'status'  => 401
            ], 401);
        }

        return response()->json([
            'message' => 'success',
            'user'    => $user,
            'isAuthenticated' => $user !== null ? true : false,
            'status'  => 200
        ]);
    }
}
