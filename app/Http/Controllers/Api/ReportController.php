<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProfitLossExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function salesSummary(Request $request): JsonResponse
    {
        $data = $request->validate([
            'period' => ['nullable', 'in:daily,weekly,monthly'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $period = $data['period'] ?? 'daily';
        $startDate = isset($data['start_date']) && $data['start_date'] ? Carbon::parse($data['start_date']) : Carbon::today()->subDays(6);
        $endDate = isset($data['end_date']) && $data['end_date'] ? Carbon::parse($data['end_date']) : Carbon::today();

        $query = Order::where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $summary = match ($period) {
            'weekly' => $query->selectRaw("DATE_FORMAT(created_at, '%x-W%v') as period, COUNT(*) as orders_count, SUM(total) as total_revenue")
                ->groupBy('period')
                ->orderBy('period', 'asc')
                ->get(),
            'monthly' => $query->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as orders_count, SUM(total) as total_revenue")
                ->groupBy('period')
                ->orderBy('period', 'asc')
                ->get(),
            default => $query->selectRaw('DATE(created_at) as period, COUNT(*) as orders_count, SUM(total) as total_revenue')
                ->groupBy('period')
                ->orderBy('period', 'asc')
                ->get(),
        };

        return response()->json([
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $summary,
        ]);
    }

    public function productSales(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = isset($data['start_date']) && $data['start_date'] ? Carbon::parse($data['start_date']) : Carbon::today();
        $endDate = isset($data['end_date']) && $data['end_date'] ? Carbon::parse($data['end_date']) : Carbon::today();

        $productSales = OrderItem::selectRaw('order_items.product_id, products.name, COUNT(*) as quantity_sold, SUM(order_items.subtotal) as total_revenue')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', '>=', $startDate)
            ->whereDate('orders.created_at', '<=', $endDate)
            ->groupBy('order_items.product_id', 'products.name')
            ->orderByDesc('total_revenue')
            ->get();

        return response()->json([
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $productSales,
        ]);
    }

    public function orderTypeSales(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = isset($data['start_date']) && $data['start_date'] ? Carbon::parse($data['start_date']) : Carbon::today();
        $endDate = isset($data['end_date']) && $data['end_date'] ? Carbon::parse($data['end_date']) : Carbon::today();

        $sales = Order::selectRaw('order_type, COUNT(*) as count, SUM(total) as revenue')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('order_type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->order_type === 'dine_in' ? 'Makan di Tempat' : 'Bungkus',
                    'count' => $item->count,
                    'revenue' => (float) $item->revenue,
                ];
            });

        return response()->json([
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $sales,
        ]);
    }

    public function orderDetails(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $startDate = isset($data['start_date']) && $data['start_date'] ? Carbon::parse($data['start_date']) : Carbon::today()->subDays(6);
        $endDate = isset($data['end_date']) && $data['end_date'] ? Carbon::parse($data['end_date']) : Carbon::today();
        $limit = $data['limit'] ?? 50;

        $orders = Order::with('items.product')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest('created_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $orders,
        ]);
    }

    public function profitLoss(Request $request): JsonResponse
    {
        return response()->json($this->profitLossData($request));
    }

    public function exportProfitLossPdf(Request $request): Response
    {
        $report = $this->profitLossData($request);
        $filename = sprintf('laporan-laba-rugi-%s-sampai-%s.pdf', $report['start_date'], $report['end_date']);

        return Pdf::loadView('exports.profit-loss', ['report' => $report])
            ->setPaper('a4')
            ->download($filename);
    }

    public function exportProfitLossExcel(Request $request): BinaryFileResponse
    {
        $report = $this->profitLossData($request);
        $filename = sprintf('laporan-laba-rugi-%s-sampai-%s.xlsx', $report['start_date'], $report['end_date']);

        return Excel::download(new ProfitLossExport($report), $filename);
    }

    /**
     * @return array{start_date: string, end_date: string, sales: float, refunds: float, other_income: float, expenses: float, cost_of_goods_sold: float, gross_profit: float, net_profit: float}
     */
    private function profitLossData(Request $request): array
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $sales = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $data['start_date'])
            ->whereDate('created_at', '<=', $data['end_date'])
            ->sum('total');
        $refunds = Order::whereNotNull('refunded_at')
            ->whereDate('refunded_at', '>=', $data['start_date'])
            ->whereDate('refunded_at', '<=', $data['end_date'])
            ->sum('refund_amount');
        $expenses = Expense::whereNull('cancelled_at')
            ->whereDate('expense_date', '>=', $data['start_date'])
            ->whereDate('expense_date', '<=', $data['end_date'])
            ->sum('amount');
        $otherIncome = Income::whereNull('cancelled_at')
            ->whereDate('income_date', '>=', $data['start_date'])
            ->whereDate('income_date', '<=', $data['end_date'])
            ->sum('amount');
        $costOfGoodsSold = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->where('orders.payment_status', 'paid')
            ->whereDate('orders.created_at', '>=', $data['start_date'])
            ->whereDate('orders.created_at', '<=', $data['end_date'])
            ->sum(DB::raw('order_items.quantity * order_items.cost_price'));
        $netSales = (float) $sales - (float) $refunds;
        $grossProfit = $netSales + (float) $otherIncome - (float) $costOfGoodsSold;

        return [
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'sales' => (float) $sales,
            'refunds' => (float) $refunds,
            'other_income' => (float) $otherIncome,
            'expenses' => (float) $expenses,
            'cost_of_goods_sold' => (float) $costOfGoodsSold,
            'gross_profit' => $grossProfit,
            'net_profit' => $grossProfit - (float) $expenses,
        ];
    }
}
