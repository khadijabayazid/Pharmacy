<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        if (! $request->has('name') && ! $request->has('phone')) {
            return response()->json([
                'message' => 'At least one of the fields (name or phone) is required.',
            ], 422);
        }

        $user = $request->user();

        $user->update($validator->validated());

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }
}
