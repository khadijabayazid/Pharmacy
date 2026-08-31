<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
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

        $base = User::where('role', 'customer');

        $total = (clone $base)->count();
        $active = (clone $base)->whereHas('tokens')->count();
        $inactive = $total - $active;

        $customers = $base->withCount('tokens')->latest()->get()->map(fn(User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'is_active' => $user->tokens_count > 0,
        ]);

        return response()->json([
            'status' => true,
            'stats' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
            ],
            'data' => $customers,
        ]);
    }

    public function destroy(Request $request, User $customer)
    {
        $this->authorizeAdmin($this->currentUser($request));

        abort_unless($customer->isCustomer(), 422, 'يمكن حذف حسابات العملاء فقط.');

        $customer->tokens()->delete();
        $customer->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف حساب العميل بنجاح.',
        ]);
    }
}
