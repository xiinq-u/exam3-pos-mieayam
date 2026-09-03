<?php

namespace App\Http\Controllers\Api;

use App\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Order;
use Illuminate\Http\Request;

class CashierShiftController extends Controller
{
    public function index(Request $request)
    {
        $shifts = CashierShift::with('user:id,name')
            ->when(! $request->user()->hasRole('owner'), fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest('opened_at')
            ->get();

        return response()->json(['data' => $shifts]);
    }

    public function open(Request $request)
    {
        $data = $request->validate(['opening_cash' => ['required', 'numeric', 'min:0']]);

        if (CashierShift::where('user_id', $request->user()->id)->whereNull('closed_at')->exists()) {
            return response()->json(['message' => 'Anda masih memiliki shift yang terbuka.'], 422);
        }

        $shift = CashierShift::create([
            'user_id' => $request->user()->id,
            'opening_cash' => $data['opening_cash'],
            'opened_at' => now(),
        ]);
        AuditLogger::record($request->user(), 'shift.opened', $shift, ['opening_cash' => $shift->opening_cash]);

        return response()->json(['data' => $shift], 201);
    }

    public function close(Request $request, CashierShift $cashierShift)
    {
        if ($cashierShift->closed_at) {
            return response()->json(['message' => 'Shift sudah ditutup.'], 422);
        }

        if (! $request->user()->hasRole('owner') && $cashierShift->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak dapat menutup shift kasir lain.'], 403);
        }

        $data = $request->validate([
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'closing_note' => ['nullable', 'string', 'max:255'],
        ]);

        $cashSales = Order::where('user_id', $cashierShift->user_id)
            ->where('payment_method', 'cash')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$cashierShift->opened_at, now()])
            ->sum('total');
        $refunds = Order::where('user_id', $cashierShift->user_id)
            ->where('payment_method', 'cash')
            ->whereBetween('refunded_at', [$cashierShift->opened_at, now()])
            ->sum('refund_amount');
        $expectedCash = (float) $cashierShift->opening_cash + (float) $cashSales - (float) $refunds;

        $cashierShift->update([
            'expected_cash' => $expectedCash,
            'actual_cash' => $data['actual_cash'],
            'cash_difference' => (float) $data['actual_cash'] - $expectedCash,
            'closed_at' => now(),
            'closing_note' => $data['closing_note'] ?? null,
        ]);
        AuditLogger::record($request->user(), 'shift.closed', $cashierShift, ['cash_difference' => $cashierShift->cash_difference]);

        return response()->json(['data' => $cashierShift->fresh()]);
    }
}
