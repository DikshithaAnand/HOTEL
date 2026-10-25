<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = new User();
        $customer->first_name = $data['first_name'];
        $customer->last_name = $data['last_name'];
        $customer->email = $data['email'];
        $customer->phone = $data['phone'];
        $customer->password = Hash::make($data['password']);
        $customer->is_active = true;
        $customer->tenant_id = null;
        $customer->save();

        $token = $customer->createToken(
            'customer-booking',
            ['customer']
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Customer account created successfully.',
            'token' => $token,
            'user' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = User::where('email', $data['email'])->first();

        if (
            ! $customer ||
            ! Hash::check($data['password'], $customer->password) ||
            ! $customer->is_active ||
            $customer->tenant_id !== null
        ) {
            throw ValidationException::withMessages([
                'email' => ['The customer credentials are incorrect.'],
            ]);
        }

        $token = $customer->createToken(
            'customer-booking',
            ['customer']
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer || $customer->tenant_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Customer account not found.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer || $customer->tenant_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Customer account not found.',
            ], 403);
        }

        $customer->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
