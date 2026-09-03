<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashierDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $todayOrders = Order::query()->where('user_id', $userId)->whereDate('created_at', today());
        $activeOrders = fn (): Builder => Order::query()->with('items')->where('status', '!=', 'cancelled');
        $completedOrders = (clone $todayOrders)->where('payment_status', 'paid');
        $shift = CashierShift::query()->where('user_id', $userId)->whereNull('closed_at')->latest('opened_at')->first();

        return response()->json([
            'shift' => $shift,
            'opening_cash' => (float) ($shift?->opening_cash ?? 0),
            'completed_transactions' => (clone $completedOrders)->count(),
            'cash_payments' => (float) (clone $completedOrders)->where('payment_method', 'cash')->sum('total'),
            'qris_payments' => (float) (clone $completedOrders)->where('payment_method', 'qris')->sum('total'),
            'today_customers' => (clone $todayOrders)->whereNotNull('customer_name')->distinct()->count('customer_name'),
            'unpaid_orders_count' => Order::query()->where('payment_status', 'unpaid')->where('status', '!=', 'cancelled')->count(),
            'processing_orders_count' => Order::query()->whereIn('status', ['pending', 'processed'])->count(),
            'last_queue_number' => (int) (Order::query()->whereDate('created_at', today())->max('queue_number') ?? 0),
            'new_orders' => $activeOrders()->where('status', 'pending')->oldest()->get(),
            'processing_orders' => $activeOrders()->where('status', 'processed')->oldest()->get(),
            'ready_orders' => $activeOrders()->where('status', 'ready')->oldest()->get(),
            'unpaid_orders' => $activeOrders()->where('payment_status', 'unpaid')
                ->orderByRaw("CASE WHEN status = 'completed' THEN 0 ELSE 1 END")
                ->oldest()
                ->get(),
            'completed_orders' => $activeOrders()->where('status', 'completed')->where('payment_status', 'paid')->latest()->limit(20)->get(),
        ]);
    }
}
