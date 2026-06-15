<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * Menampilkan riwayat pembelian yang sudah selesai dibayar.
     */
    public function sales()
    {
        $orders = Order::with('items', 'user')
            ->where('status', 'completed')
            ->latest()
            ->paginate(20);

        return view('reports.sales', compact('orders'));
    }

    /**
     * Menampilkan laporan pendapatan harian untuk 7 hari terakhir.
     */
    public function revenue(Request $request)
    {
        $start = Carbon::today()->subDays(6);
        $daily = Order::selectRaw('DATE(created_at) as day, COUNT(*) as orders_count, SUM(total) as revenue')
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->get();

        return view('reports.revenue', compact('daily'));
    }
}
