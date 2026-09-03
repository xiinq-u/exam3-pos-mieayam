<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Material;
use App\Models\MaterialMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OwnerDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6);
        $paidOrdersToday = Order::query()
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $today);

        $todaySales = (float) (clone $paidOrdersToday)->sum('total');
        $todayRefunds = (float) Order::query()->whereDate('refunded_at', $today)->sum('refund_amount');
        $todayIncome = (float) Income::query()->whereNull('cancelled_at')->whereDate('income_date', $today)->sum('amount');
        $todayExpenses = (float) Expense::query()->whereNull('cancelled_at')->whereDate('expense_date', $today)->sum('amount');
        $costOfGoodsSold = (float) OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->where('orders.payment_status', 'paid')
            ->whereDate('orders.created_at', $today)
            ->sum(DB::raw('order_items.quantity * order_items.cost_price'));
        $grossProfit = $todaySales - $todayRefunds + $todayIncome - $costOfGoodsSold;

        $materials = Material::query()->where('is_active', true)->orderBy('name')->get()
            ->map(function (Material $material): array {
                $status = $material->stock === 0
                    ? 'out'
                    : ($material->stock <= $material->minimum_stock ? 'low' : 'safe');

                return [
                    'id' => $material->id,
                    'name' => $material->name,
                    'stock' => $material->stock,
                    'unit' => $material->unit,
                    'minimum_stock' => $material->minimum_stock,
                    'status' => $status,
                    'inventory_value' => $material->stock * (float) $material->purchase_price,
                    'updated_at' => $material->updated_at,
                ];
            });

        $revenueChart = collect(range(0, 6))->map(function (int $offset) use ($startDate): array {
            $date = $startDate->copy()->addDays($offset);
            $sales = (float) Order::query()
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('total');
            $income = (float) Income::query()->whereNull('cancelled_at')->whereDate('income_date', $date)->sum('amount');
            $expenses = (float) Expense::query()->whereNull('cancelled_at')->whereDate('expense_date', $date)->sum('amount');
            $cost = (float) OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'completed')
                ->where('orders.payment_status', 'paid')
                ->whereDate('orders.created_at', $date)
                ->sum(DB::raw('order_items.quantity * order_items.cost_price'));

            return [
                'date' => $date->toDateString(),
                'label' => $date->format('d M'),
                'revenue' => $sales + $income,
                'net_profit' => $sales + $income - $expenses - $cost,
                'income' => $income,
                'expenses' => $expenses,
            ];
        });

        $bestSellingProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->where('orders.payment_status', 'paid')
            ->whereDate('orders.created_at', '>=', $startDate)
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as quantity')
            ->groupBy('order_items.product_name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();
        $orderTypes = Order::query()
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $startDate)
            ->selectRaw('order_type, COUNT(*) as total')
            ->groupBy('order_type')
            ->pluck('total', 'order_type');

        return response()->json([
            'today_sales' => $todaySales,
            'today_income' => $todayIncome,
            'today_expenses' => $todayExpenses,
            'gross_profit' => $grossProfit,
            'net_profit' => $grossProfit - $todayExpenses,
            'today_orders' => (clone $paidOrdersToday)->count(),
            'active_products' => Product::query()->where('is_available', true)->count(),
            'active_employees' => User::query()->where('is_active', true)->count(),
            'inventory_value' => (float) $materials->sum('inventory_value'),
            'safe_materials' => $materials->where('status', 'safe')->count(),
            'low_stock_materials' => $materials->where('status', 'low')->count(),
            'out_of_stock_materials' => $materials->where('status', 'out')->count(),
            'materials' => $materials->values(),
            'low_stock_list' => $materials->whereIn('status', ['low', 'out'])->values(),
            'revenue_chart' => $revenueChart,
            'best_selling_products' => $bestSellingProducts,
            'order_type_summary' => [
                'dine_in' => (int) ($orderTypes['dine_in'] ?? 0),
                'take_away' => (int) ($orderTypes['take_away'] ?? 0),
            ],
            'income_expense_summary' => ['income' => $todayIncome, 'expenses' => $todayExpenses],
            'recent_transactions' => Order::query()->with('items')->latest()->limit(5)->get(),
            'recent_expenses' => Expense::query()->with('user:id,name')->latest()->limit(5)->get(),
            'recent_stock_changes' => MaterialMovement::query()->with(['material:id,name,unit', 'user:id,name'])->latest()->limit(5)->get(),
            'recent_activities' => AuditLog::query()->with('user:id,name')->latest()->limit(10)->get(),
        ]);
    }
}
