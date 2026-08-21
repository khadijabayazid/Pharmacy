<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignDeliveryRequest;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Order;
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
        $query = Order::with(['orderItems.product', 'delivery', 'prescription']);
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
            'data' => $orders,
        ]);
    }


    public function store(PlaceOrderRequest $request)
    {
        $user = $this->currentUser($request);
        $productIds = collect($request->items)->pluck('product_id')->toArray();
        $result = DB::transaction(function () use ($request, $productIds, $user) {
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()
            ->keyBy('id');
            $requiresPrescription = false;

            foreach($request->items as $item){
                $product = $products->get($item['product_id']);
                if($item['quantity'] > $product->quantity){
                    return ['error' => "Insufficient stock for product: {$product->name}"];
                }

                if($product->is_requires_prescription){
                    $requiresPrescription = true;

                }
            }
            if($requiresPrescription && !$request->filled('prescription_id')){
                return ['error' => 'An item in your order requires a prescription. Please upload it to proceed.'];
            }
            $order = Order::create([
                'user_id' => $user->id,
                'address' => $request->address,
                'prescription_id' => $request->prescription_id,
                'status' => OrderStatus::Pending->value,
            ]);
            foreach($request->items as $item){
                $product = $products->get($item['product_id']);
                $order->orderItems()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
                $product->decrement('quantity', $item['quantity']);
            }
            return ['order' => $order];
        });

        if(isset($result['error'])){
            return response()->json([
                'status' => false,
                'message' => $result['error'],
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order placed successfully! Pending pharmacist approval.',
            'data' => $result['order']->load(['orderItems.product']),
        ], 201);

    }


    public function show(Request $request, Order $order)
    {
        $user = $this->currentUser($request);
        $this->authorizeAccess($user, $order);
        $order->load(['orderItems.product', 'delivery', 'prescription', 'user']);

        return response()->json([
            'status' => true,
            'data' => $order,
        ]);
    }

    public function accept(Request $request, Order $order)
    {
        $this->authorizeAdmin($this->currentUser($request));
        if ($order->status !== OrderStatus::Pending->value) {
            return response()->json([
                'status' => false,
                'message' => 'Only pending orders can be accepted.',
            ], 422);
        }

        $order->update(['status' => OrderStatus::Accepted->value]);
        return response()->json([
                'status' => true,
                'message' => 'Order accepted successfully.',
                'data' => $order->fresh(['orderItems.product'])
            ]);
    }

    public function reject(Request $request, Order $order) {
        $this->authorizeAdmin($this->currentUser($request));

        if($order->status !== OrderStatus::Pending->value){
            return response()->json([
                'status' => false,
                'message' => 'Only pending orders can be rejected.',
            ], 422);
        }

        DB::transaction(function () use ($order){
            foreach($order->orderItems as $item){
                $item->product->increment('quantity', $item->quntity);
            }
            $order->update(['status' => OrderStatus::Rejected->value]);
            
        });
        return response()->json([
            'status' => true,
            'message' => 'Order rejected successfully.',
            'data' => $order->fresh(['orderItems.product'])
        ]);
    }

    public function assignDelivery(AssignDeliveryRequest $request, Order $order)
    {
        $this->authorizeAdmin($this->currentUser($request));

        if ($order->status !== OrderStatus::Accepted->value) {
            return response()->json([
                'status' => false,
                'message' => 'Only accepted orders can be assigned for delivery.',
            ], 422);
        }

        $order->update([
            'delivery_id' => $request->delivery_id,
            'status' => OrderStatus::OnDelivery->value,
            'assigned_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Delivery assigned successfully.',
            'data' => $order->fresh(['orderItems.product', 'delivery']),
        ]);
    }

    public function markDelivered(Request $request, Order $order)
    {
        $this->authorizeAdmin($this->currentUser($request));

        if($order->status !== OrderStatus::OnDelivery->value){
            return response()->json([
                'status' => false,
                'message' => 'Only orders that are on delivery can be marked as delivered.',
            ], 422);
        }

        $order->update([
            'status' => OrderStatus::Delivered->value,
            'delivered_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order marked as delivered successfully.',
            'data' => $order->fresh(['orderItems.product', 'delivery']),
        ]);
    }

    private function authorizeAdmin(User $user)
    {
        abort_unless($user->isAdmin(), 403, 'You do not have permission to perform this action.');
    }

    private function authorizeAccess(User $user, Order $order)
    {
        abort_unless(
            $user->isAdmin() || $user->id === $order->user_id,
            403,
            'You do not have permission to access this order.'
        );
    }
}
