<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Delivery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    private function currentUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        return $user;
    }

    private function authorizeAdmin(User $user)
    {
        abort_unless($user->isAdmin(), 403, 'ليس لديك صلاحية للقيام بهذا الإجراء.');
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($this->currentUser($request));

        $deliveries = Delivery::with('user:id,name,phone')->get()->map(fn (Delivery $delivery) => [
            'id' => $delivery->id,
            'name' => $delivery->user->name,
            'phone' => $delivery->user->phone,
            'vehicle_type' => $delivery->vehicle_type,
            'vehicle_number' => $delivery->vehicle_number,
            'is_available' => $delivery->is_available,
        ]);

        return response()->json([
            'status' => true,
            'data' => $deliveries,
        ]);
    }

    public function store(StoreDeliveryRequest $request)
    {
        $delivery = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'role' => 'delivery',
                'email' => 'delivery_' . Str::uuid() . '@pharmacy.local',
                'password' => Hash::make(Str::random(32)),
            ]);

            return Delivery::create([
                'user_id' => $user->id,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'is_available' => true,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة مندوب التوصيل بنجاح.',
            'data' => $delivery->load('user:id,name,phone'),
        ], 201);
    }

    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        DB::transaction(function () use ($request, $delivery) {
            $delivery->user->update([
                'name' => $request->name,
                'phone' => $request->phone,
            ]);

            $delivery->update([
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'is_available' => $request->is_available,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'تم تعديل بيانات مندوب التوصيل بنجاح.',
            'data' => $delivery->fresh()->load('user:id,name,phone'),
        ]);
    }
}
