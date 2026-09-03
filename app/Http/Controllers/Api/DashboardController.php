<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $completedOrders = Order::where('status', 'completed');
        $today = Carbon::today();
        $todayDate = $today->toDateString();

        $totalSales = (clone $completedOrders)->sum('total');
        $todaySales = (clone $completedOrders)->whereDate('created_at', $todayDate)->sum('total');
        $completedOrdersCount = (clone $completedOrders)->count();
        $todayOrdersCount = (clone $completedOrders)->whereDate('created_at', $todayDate)->count();
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $availableProductsCount = Product::where('is_available', true)->count();

        $dailyRevenue = (clone $completedOrders)
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->whereDate('created_at', '>=', $today->copy()->subDays(6)->toDateString())
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->pluck('revenue', 'day');

        $labels = collect(range(0, 6))->map(fn ($day) => $today->copy()->subDays(6 - $day)->format('d M'));

        $chartData = collect(range(0, 6))->map(function ($day) use ($today, $dailyRevenue) {
            $date = $today->copy()->subDays(6 - $day)->toDateString();

            return (float) ($dailyRevenue[$date] ?? 0);
        });

        return response()->json([
            'total_sales' => (float) $totalSales,
            'today_sales' => (float) $todaySales,
            'completed_orders' => $completedOrdersCount,
            'today_orders' => $todayOrdersCount,
            'pending_orders' => $pendingOrdersCount,
            'available_products' => $availableProductsCount,
            'chart_labels' => $labels,
            'chart_data' => $chartData,
        ]);
    }
}
