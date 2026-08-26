<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|in:customer,delivery',
            'vehicle_type' => 'required_if:role,delivery|string|max:100',
            'vehicle_number' => 'required_if:role,delivery|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'role' => $request->input('role', 'customer'),
        ]);

        // لو كان الحساب لمندوب توصيل، ننشئ سجل Delivery المرتبط تلقائيًا (علاقة 1:1)
        if ($user->role === 'delivery') {
            Delivery::create([
                'user_id' => $user->id,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'is_available' => true,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();


        $user->delete();

        return response()->json([
            'message' => 'تم حذف الحساب بنجاح',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
