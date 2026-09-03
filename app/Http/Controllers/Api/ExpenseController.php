<?php

namespace App\Http\Controllers\Api;

use App\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $expenses = Expense::with('user:id,name')
            ->when($data['start_date'] ?? null, fn ($query, $date) => $query->whereDate('expense_date', '>=', $date))
            ->when($data['end_date'] ?? null, fn ($query, $date) => $query->whereDate('expense_date', '<=', $date))
            ->latest('expense_date')
            ->latest('id')
            ->get();

        return response()->json(['data' => $expenses]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'expense_date' => ['required', 'date'],
        ]);

        $expense = Expense::create([...$data, 'user_id' => $request->user()->id]);
        AuditLogger::record($request->user(), 'expense.created', $expense, ['amount' => $expense->amount]);

        return response()->json(['data' => $expense], 201);
    }

    public function cancel(Request $request, Expense $expense)
    {
        if ($expense->cancelled_at) {
            return response()->json(['message' => 'Pengeluaran sudah dibatalkan.'], 422);
        }

        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        $expense->update([
            'cancelled_at' => now(),
            'cancellation_reason' => $data['reason'],
        ]);
        AuditLogger::record($request->user(), 'expense.cancelled', $expense, ['reason' => $data['reason']]);

        return response()->json(['data' => $expense->fresh()]);
    }
}
