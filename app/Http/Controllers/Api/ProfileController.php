<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(UpdateProfileRequest $request)
    {

        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'user' => $user,
        ]);
    }
}
