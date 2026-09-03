<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialCategory;
use Illuminate\Http\Request;

class FinancialCategoryController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['type' => ['nullable', 'in:income,expense']]);

        return response()->json(['data' => FinancialCategory::when($data['type'] ?? null, fn ($query, $type) => $query->where('type', $type))->orderBy('type')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'type' => ['required', 'in:income,expense']]);
        $category = FinancialCategory::firstOrCreate($data);

        return response()->json(['data' => $category], $category->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(FinancialCategory $financialCategory)
    {
        $financialCategory->delete();

        return response()->noContent();
    }
}
