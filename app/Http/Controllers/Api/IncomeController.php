<?php

namespace App\Http\Controllers\Api;

use App\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date']]);

        return response()->json(['data' => Income::with('user:id,name')->when($data['start_date'] ?? null, fn ($query, $date) => $query->whereDate('income_date', '>=', $date))->when($data['end_date'] ?? null, fn ($query, $date) => $query->whereDate('income_date', '<=', $date))->latest('income_date')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['category' => ['required', 'string', 'max:100'], 'description' => ['required', 'string', 'max:255'], 'amount' => ['required', 'numeric', 'gt:0'], 'income_date' => ['required', 'date']]);
        $income = Income::create([...$data, 'user_id' => $request->user()->id]);
        AuditLogger::record($request->user(), 'income.created', $income, ['amount' => $income->amount]);

        return response()->json(['data' => $income], 201);
    }

    public function cancel(Request $request, Income $income)
    {
        if ($income->cancelled_at) {
            return response()->json(['message' => 'Pemasukan sudah dibatalkan.'], 422);
        }
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        $income->update(['cancelled_at' => now(), 'cancellation_reason' => $data['reason']]);
        AuditLogger::record($request->user(), 'income.cancelled', $income, ['reason' => $data['reason']]);

        return response()->json(['data' => $income->fresh()]);
    }
}
