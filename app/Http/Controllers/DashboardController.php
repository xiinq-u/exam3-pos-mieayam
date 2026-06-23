<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard admin.
     * Data di halaman ini dihitung dari pesanan dan menu yang ada di database.
     */
    public function index(): View
    {
        // Pesanan completed berarti transaksi sudah selesai dibayar.
        $completedOrders = Order::where('status', '=', 'completed', 'and');
        $today = Carbon::today();
        $todayDate = $today->toDateString();
        $chartStart = $today->copy()->subDays(6);
        $chartStartDate = $chartStart->toDateString();
        // dd($chartStart);

        // Angka ringkasan untuk kartu dashboard.
        $totalSales = (clone $completedOrders)->sum('total');
        $todaySales = (clone $completedOrders)
            ->whereDate('created_at', '=', $todayDate, 'and')
            ->sum('total');
        $todayOrdersCount = (clone $completedOrders)
            ->whereDate('created_at', '=', $todayDate, 'and')
            ->count('*');
        $completedOrdersCount = (clone $completedOrders)->count('*');
        $pendingOrdersCount = Order::where('status', '=', 'pending', 'and')->count('*');
        $availableProductsCount = Product::where('is_available', '=', true, 'and')->count('*');

        // Data pendapatan 7 hari terakhir untuk grafik.
        $dailyRevenue = (clone $completedOrders)
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->whereDate('created_at', '>=', $chartStartDate, 'and')
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->pluck('revenue', 'day');
            // dd($dailyRevenue);

        $chartLabels = collect(range(0, 6))
            ->map(fn (int $day): string => $chartStart->copy()->addDays($day)->format('d M'));
            // dd($chartLabels);

        // Jika ada hari yang belum punya transaksi, nilainya dibuat 0 agar grafik tetap rapi.
        $chartData = collect(range(0, 6))
            ->map(function (int $day) use ($chartStart, $dailyRevenue): float {
                $date = $chartStart->copy()->addDays($day)->toDateString();

                return (float) ($dailyRevenue[$date] ?? 0);
            });
            // dd($chartData);

        return view('dashboard', [
            'totalSales' => $totalSales,
            'todaySales' => $todaySales,
            'todayOrdersCount' => $todayOrdersCount,
            'completedOrdersCount' => $completedOrdersCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'availableProductsCount' => $availableProductsCount,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }
}
