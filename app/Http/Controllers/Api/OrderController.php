<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\RejectionReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignDeliveryRequest;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Requests\RejectOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    private function currentUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        return $user;
    }
    public function index(Request $request): JsonResponse
    {
        $user = $this->currentUser($request);
        $query = Order::with(['orderItems.product', 'delivery', 'prescription', 'user']);
        if ($user->isAdmin()) {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'status' => true,
            'data' => OrderResource::collection($orders),
        ]);
    }


    public function store(PlaceOrderRequest $request)
    {
        $user = $this->currentUser($request);

        if (Order::where('user_id', $user->id)->where('status', OrderStatus::Pending->value)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'لديك طلب قيد الانتظار بالفعل، يجب إنهاؤه أولًا قبل تقديم طلب جديد.',
            ], 422);
        }
        $productIds = collect($request->items)->pluck('product_id')->toArray();

        $result = DB::transaction(function () use ($request, $productIds, $user) {
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
            $requiresPrescription = false;
            $totalPrice = 0;

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);

                if ($item['quantity'] > $product->quantity) {
                    return ['error' => "الكمية المتوفرة غير كافية للمنتج: {$product->name}"];
                }

                if ($product->is_required_prescription) {
                    $requiresPrescription = true;
                }

                $totalPrice += $item['price'] * $item['quantity'];
            }

            if ($requiresPrescription && ! $request->hasFile('prescription_image')) {
                return ['error' => 'أحد منتجات طلبك يتطلب وصفة طبية، يرجى رفع صورتها للمتابعة.'];
            }

            $prescriptionId = null;

            if ($request->hasFile('prescription_image')) {
                $prescription = Prescription::create([
                    'image_path' => $request->file('prescription_image')->store('prescriptions', 'public'),
                ]);
                $prescriptionId = $prescription->id;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'address' => $request->address,
                'prescription_id' => $prescriptionId,
                'status' => OrderStatus::Pending->value,
                'total_price' => $totalPrice,
                'delivery_price' => Order::DELIVERY_PRICE,
            ]);

            foreach ($request->items as $item) {
                $order->orderItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            return ['order' => $order];
        });

        if (isset($result['error'])) {
            return response()->json([
                'status' => false,
                'message' => $result['error'],
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم تقديم الطلب بنجاح، بانتظار موافقة الصيدلية.',
            'data' => new OrderResource($result['order']->load(['orderItems.product', 'prescription'])),
        ], 201);
    }


    public function show(Request $request, Order $order)
    {
        $user = $this->currentUser($request);
        $this->authorizeAccess($user, $order);
        $order->load(['orderItems.product', 'delivery.user', 'prescription', 'user']);

        return response()->json([
            'status' => true,
            'data' => new OrderResource($order),
        ]);
    }

    public function accept(Request $request, Order $order)
    {
        $this->authorizeAdmin($this->currentUser($request));

        if ($order->status !== OrderStatus::Pending->value) {
            return response()->json([
                'status' => false,
                'message' => 'يمكن قبول الطلبات قيد الانتظار فقط.',
            ], 422);
        }

        $result = DB::transaction(function () use ($order) {
            $order->load('orderItems');
            $productIds = $order->orderItems->pluck('product_id');
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()
                ->keyBy('id');

            foreach ($order->orderItems as $item) {
                $product = $products->get($item->product_id);
                if ($item->quantity > $product->quantity) {
                    return ['error' => "الكمية المتوفرة غير كافية حاليًا للمنتج: {$product->name}"];
                }
            }

            foreach ($order->orderItems as $item) {
                $products->get($item->product_id)->decrement('quantity', $item->quantity);
            }

            $order->update(['status' => OrderStatus::Accepted->value]);

            return ['ok' => true];
        });

        if (isset($result['error'])) {
            return response()->json(['status' => false, 'message' => $result['error']], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم قبول الطلب بنجاح.',
            'data' => new OrderResource($order->fresh(['orderItems.product'])),
        ]);
    }

    public function reject(RejectOrderRequest $request, Order $order)
    {
        $this->authorizeAdmin($this->currentUser($request));

        if ($order->status !== OrderStatus::Pending->value) {
            return response()->json([
                'status' => false,
                'message' => 'يمكن رفض الطلبات قيد الانتظار فقط.',
            ], 422);
        }

        $order->update([
            'status' => OrderStatus::Rejected->value,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم رفض الطلب بنجاح.',
            'data' => new OrderResource($order->fresh(['orderItems.product'])),
        ]);
    }

    public function assignDelivery(AssignDeliveryRequest $request, Order $order)
    {
        $this->authorizeAdmin($this->currentUser($request));

        if ($order->status !== OrderStatus::Accepted->value) {
            return response()->json([
                'status' => false,
                'message' => 'يمكن تعيين عامل التوصيل للطلبات المقبولة فقط.',
            ], 422);
        }

        $order->update([
            'delivery_id' => $request->delivery_id,
            'status' => OrderStatus::OnDelivery->value,
            'assigned_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تعيين عامل التوصيل بنجاح.',
            'data' => new OrderResource($order->fresh(['orderItems.product', 'delivery.user'])),
        ]);
    }

    public function markDelivered(Request $request, Order $order)
    {
        $this->authorizeAdmin($this->currentUser($request));

        if ($order->status !== OrderStatus::OnDelivery->value) {
            return response()->json([
                'status' => false,
                'message' => 'يمكن تأكيد التسليم للطلبات قيد التوصيل فقط.',
            ], 422);
        }

        $order->update([
            'status' => OrderStatus::Delivered->value,
            'delivered_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تأكيد تسليم الطلب بنجاح.',
            'data' => new OrderResource($order->fresh(['orderItems.product', 'delivery.user'])),
        ]);
    }

    public function statuses(Request $request): JsonResponse
    {
        $user = $this->currentUser($request);

        $query = Order::query()->select([
            'id',
            'user_id',
            'status',
            'rejection_reason',
            'delivery_id',
            'assigned_at',
            'delivered_at',
        ])->with('delivery.user:id,name,phone');

        if ($user->isAdmin()) {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $orders = $query->get()->map(fn($order) => [
            'id' => $order->id,
            'status' => $order->status,
            'rejection_reason' => $order->rejection_reason,
            'delivery' => $order->delivery ? [
                'name' => $order->delivery->user->name,
                'phone' => $order->delivery->user->phone,
            ] : null,
            'delivered_at' => $order->delivered_at,
        ]);

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }

    private function authorizeAdmin(User $user)
    {
        abort_unless($user->isAdmin(), 403, 'ليس لديك صلاحية للقيام بهذا الإجراء.');
    }

    private function authorizeAccess(User $user, Order $order)
    {
        abort_unless(
            $user->isAdmin() || $user->id === $order->user_id,
            403,
            'ليس لديك صلاحية للوصول إلى هذا الطلب.'
        );
    }
}
