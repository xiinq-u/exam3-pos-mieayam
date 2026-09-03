<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class KitchenDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $activeOrders = fn (): Builder => Order::query()->with('items')->where('status', '!=', 'cancelled');
        $newOrders = $activeOrders()->where('status', 'pending')->oldest()->get();
        $processingOrders = $activeOrders()->where('status', 'processed')->oldest()->get();
        $readyOrders = $activeOrders()->where('status', 'ready')->oldest()->get();
        $oldestOrder = $activeOrders()->whereIn('status', ['pending', 'processed', 'ready'])->oldest()->first();
        $lowStockMaterials = Material::query()
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->get(['id', 'name', 'unit', 'stock', 'minimum_stock', 'updated_at']);

        return response()->json([
            'new_orders_count' => $newOrders->count(),
            'processing_orders_count' => $processingOrders->count(),
            'ready_orders_count' => $readyOrders->count(),
            'longest_waiting_minutes' => $oldestOrder
                ? (int) Carbon::parse($oldestOrder->created_at)->diffInMinutes(now())
                : 0,
            'low_stock_count' => $lowStockMaterials->where('stock', '>', 0)->count(),
            'out_of_stock_count' => $lowStockMaterials->where('stock', 0)->count(),
            'new_orders' => $newOrders,
            'processing_orders' => $processingOrders,
            'ready_orders' => $readyOrders,
            'low_stock_materials' => $lowStockMaterials,
        ]);
    }
}
