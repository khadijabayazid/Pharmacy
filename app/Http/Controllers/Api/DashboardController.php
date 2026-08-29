<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    private const SPARKLINE_DAYS = 4;

    public function index(DashboardRequest $request)
    {
        return response()->json([
            'today_orders' => $this->getTodayOrdersCount(),
            'today_orders_trend' => $this->getOrdersTrendByStatus(OrderStatus::Delivered->value),

            'total_orders' => Order::count(),
            'total_orders_trend' => $this->getOrdersTrendByStatus(),

            'available_products' => Product::where('quantity', '>', 0)->count(),
            'available_products_trend' => $this->getProductsTrend(),

            'customers_count' => User::count(),
            'customers_trend' => $this->getCustomersTrend(),

            'sales_last_7_days' => $this->getSalesLast7Days(),
            'top_products' => $this->getTopProducts(),

        ]);
    }

    private function getTodayOrdersCount(): int
    {
        return Order::where('status', OrderStatus::Delivered->value)
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    /**
     * عدد الطلبات بكل يوم لآخر 4 أيام.
     * بدون تمرير $status: بيرجع كل الطلبات بكل حالاتها (لبطاقة "إجمالي الطلبات").
     * بتمرير $status: بيرجع الطلبات المفلترة فقط (لبطاقة "طلبات اليوم" بحالة delivered).
     */
    private function getOrdersTrendByStatus(?string $status = null): array
    {
        $startDate = Carbon::today()->subDays(self::SPARKLINE_DAYS - 1);

        $query = Order::query()->whereDate('created_at', '>=', $startDate);

        if ($status !== null) {
            $query->where('status', $status);
        }

        $counts = $query->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillMissingDays($counts, $startDate);
    }

    /**
     * عدد المنتجات الجديدة المضافة بكل يوم لآخر 4 أيام (بغض النظر عن الكمية).
     */
    private function getProductsTrend(): array
    {
        $startDate = Carbon::today()->subDays(self::SPARKLINE_DAYS - 1);

        $counts = Product::query()
            ->whereDate('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillMissingDays($counts, $startDate);
    }

    /**
     * المجموع التراكمي للعملاء حتى كل يوم من آخر 4 أيام.
     */
    private function getCustomersTrend(): array
    {
        $startDate = Carbon::today()->subDays(self::SPARKLINE_DAYS - 1);

        // عدد العملاء المسجلين قبل بداية الفترة (نقطة انطلاق صحيحة للتراكم)
        $baseCount = User::query()->whereDate('created_at', '<', $startDate)->count();

        $counts = User::query()
            ->whereDate('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $daily = $this->fillMissingDays($counts, $startDate);

        $cumulative = [];
        $running = $baseCount;

        foreach ($daily as $item) {
            $running += $item['count'];
            $cumulative[] = [
                'date'  => $item['date'],
                'count' => $running,
            ];
        }

        return $cumulative;
    }

    /**
     * ميثود مشتركة: تعبئة أي أيام ناقصة (يلي ما كان فيها بيانات) بصفر،
     * لضمان رجوع عدد نقاط ثابت (SPARKLINE_DAYS) دايماً بغض النظر عن وجود بيانات فيهم أو لأ.
     */
    private function fillMissingDays($counts, Carbon $startDate): array
    {
        $result = [];

        for ($i = 0; $i < self::SPARKLINE_DAYS; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            $result[] = [
                'date'  => $date,
                'count' => (int) ($counts[$date] ?? 0),
            ];
        }

        return $result;
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
