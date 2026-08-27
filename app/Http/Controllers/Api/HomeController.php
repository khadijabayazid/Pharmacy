<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    private const PREVIEW_LIMIT = 5;

    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'categories' => Category::select('id', 'name')->get(),
                'best_sellers' => $this->bestSellersQuery()->take(self::PREVIEW_LIMIT)->get(),
                'new_arrivals' => $this->newArrivalsQuery()->take(self::PREVIEW_LIMIT)->get(),
                'prescription_required' => $this->prescriptionRequiredQuery()->take(self::PREVIEW_LIMIT)->get(),
            ],
        ]);
    }

    public function bestSellers()
    {
        return response()->json([
            'status' => true,
            'data' => $this->bestSellersQuery()->get(),
        ]);
    }

    public function newArrivals()
    {
        return response()->json([
            'status' => true,
            'data' => $this->newArrivalsQuery()->get(),
        ]);
    }

    public function prescriptionRequired()
    {
        return response()->json([
            'status' => true,
            'data' => $this->prescriptionRequiredQuery()->get(),
        ]);
    }

    private function bestSellersQuery()
    {
        return Product::query()
            ->with('category')
            ->withSum(['orderItems as total_sold' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('status', '!=', OrderStatus::Rejected);
                });
            }], 'quantity')
            ->orderByDesc('total_sold')
            ->having('total_sold', '>', 0);
    }

    private function newArrivalsQuery()
    {
        return Product::query()->with('category')->latest();
    }

    private function prescriptionRequiredQuery()
    {
        return Product::query()->with('category')->where('is_required_prescription', true)->latest();
    }
}