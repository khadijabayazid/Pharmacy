<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(DashboardRequest $request)
    {
        return response()->json([
            'today_orders'       => $this->getTodayOrdersCount(),
            'total_orders'       => Order::count(),
            'available_products' => Product::where('quantity', '>', 0)->count(),
            'customers_count'    => User::count(),
            'sales_last_7_days'  => $this->getSalesLast7Days(),
            'top_products'       => $this->getTopProducts(),
        ]);
    }

    private function getTodayOrdersCount(): int
    {
        return Order::where('status', OrderStatus::Delivered->value)
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    private function getSalesLast7Days(): array
    {
        $startDate = Carbon::today()->subDays(6); // آخر 7 أيام شاملة اليوم

        $sales = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::Delivered->value)
            ->whereDate('orders.created_at', '>=', $startDate)
            ->selectRaw('DATE(orders.created_at) as sale_date, SUM(order_items.quantity) as total_quantity')
            ->groupBy('sale_date')
            ->pluck('total_quantity', 'sale_date');

        $result = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            $result[] = [
                'date'     => $date,
                'quantity' => (int) ($sales[$date] ?? 0),
            ];
        }
        return $result;
    }

    private function getTopProducts(): array
    {
        $productSales = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::Delivered->value)
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_quantity')
            ->groupBy('order_items.product_id')
            ->orderByDesc('total_quantity')
            ->get();

        $grandTotal = $productSales->sum('total_quantity');

        if ($grandTotal == 0) {
            return [];
        }

        $top4 = $productSales->take(4);
        $productNames = Product::whereIn('id', $top4->pluck('product_id'))->pluck('name', 'id');

        $result = $top4->map(function ($item) use ($productNames, $grandTotal) {
            return [
                'name'       => $productNames[$item->product_id] ?? 'غير معروف',
                'quantity'   => (int) $item->total_quantity,
                'percentage' => round(($item->total_quantity / $grandTotal) * 100, 1),
            ];
        })->values()->toArray();

        $otherTotal = $grandTotal - $top4->sum('total_quantity');

        if ($otherTotal > 0) {
            $result[] = [
                'name'       => 'أخرى',
                'quantity'   => (int) $otherTotal,
                'percentage' => round(($otherTotal / $grandTotal) * 100, 1),
            ];
        }

        return $result;
    }
}
