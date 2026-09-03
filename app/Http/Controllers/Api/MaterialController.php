<?php

namespace App\Http\Controllers\Api;

use App\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Material::query()->orderBy('name');

        if ($request->user()->role === 'kitchen') {
            return response()->json($query->get([
                'id',
                'name',
                'sku',
                'unit',
                'stock',
                'minimum_stock',
                'is_active',
                'updated_at',
            ]));
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:materials,sku'],
            'unit' => ['required', 'string', 'max:50'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $material = Material::create([
            'name' => $data['name'],
            'sku' => $data['sku'],
            'unit' => $data['unit'],
            'stock' => $data['initial_stock'] ?? 0,
            'minimum_stock' => $data['minimum_stock'] ?? 0,
            'is_active' => true,
            'purchase_price' => $data['purchase_price'] ?? 0,
        ]);

        if (($data['initial_stock'] ?? 0) > 0) {
            MaterialMovement::create([
                'material_id' => $material->id,
                'user_id' => Auth::id(),
                'type' => 'in',
                'quantity' => $data['initial_stock'],
                'note' => 'Stok awal',
            ]);
        }

        return response()->json($material, 201);
    }

    public function show(Request $request, Material $material): JsonResponse
    {
        $materialData = $request->user()->role === 'kitchen'
            ? $material->only(['id', 'name', 'sku', 'unit', 'stock', 'minimum_stock', 'is_active', 'updated_at'])
            : $material;

        return response()->json([
            'material' => $materialData,
            'movements' => $material->movements()
                ->when(request('start_date'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                ->when(request('end_date'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
                ->latest()
                ->get(),
        ]);
    }

    public function adjust(Request $request, Material $material): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:in,out,adjustment,damaged,expired'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->user()->role === 'kitchen' && ! in_array($data['type'], ['damaged', 'expired'], true)) {
            return response()->json(['message' => 'Dapur hanya dapat mencatat bahan rusak atau kedaluwarsa.'], 403);
        }

        $movementType = in_array($data['type'], ['damaged', 'expired'], true) ? 'out' : $data['type'];
        $newStock = match ($movementType) {
            'in' => $material->stock + $data['quantity'],
            'out' => $material->stock - $data['quantity'],
            'adjustment' => $data['quantity'],
        };

        if ($movementType === 'out' && $material->stock < $data['quantity']) {
            return response()->json(['message' => 'Stok tidak mencukupi.'], 422);
        }

        $material->update(['stock' => max(0, $newStock)]);

        MaterialMovement::create([
            'material_id' => $material->id,
            'user_id' => Auth::id(),
            'type' => $movementType,
            'loss_reason' => in_array($data['type'], ['damaged', 'expired'], true) ? $data['type'] : null,
            'quantity' => $data['quantity'],
            'note' => $data['note'] ?? match ($data['type']) {
                'damaged' => 'Bahan rusak',
                'expired' => 'Bahan kedaluwarsa',
                default => 'Penyesuaian stok',
            },
        ]);
        AuditLogger::record($request->user(), 'material.adjusted', $material, ['type' => $data['type'], 'quantity' => $data['quantity']]);

        return response()->json([
            'message' => 'Stok berhasil disesuaikan.',
            'material' => $material->fresh(),
        ]);
    }

    public function update(Request $request, Material $material)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'sku' => ['sometimes', 'string', 'max:100', 'unique:materials,sku,'.$material->id],
            'unit' => ['sometimes', 'string', 'max:50'],
            'minimum_stock' => ['sometimes', 'integer', 'min:0'],
            'purchase_price' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $material->update($data);

        return response()->json(['data' => $material->fresh()]);
    }

    public function destroy(Material $material)
    {
        $material->update(['is_active' => false]);
        $material->delete();

        return response()->noContent();
    }

    public function lowStock(Request $request): JsonResponse
    {
        $columns = $request->user()->role === 'kitchen'
            ? ['id', 'name', 'unit', 'stock', 'minimum_stock', 'updated_at']
            : ['*'];

        return response()->json([
            'data' => Material::where('is_active', true)
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->orderBy('stock', 'asc')
                ->get($columns),
        ]);
    }

    public function inventoryReport()
    {
        $materials = Material::where('is_active', true)->orderBy('name')->get()->map(function (Material $material): array {
            return [
                'id' => $material->id,
                'name' => $material->name,
                'unit' => $material->unit,
                'stock' => $material->stock,
                'purchase_price' => (float) $material->purchase_price,
                'inventory_value' => $material->stock * (float) $material->purchase_price,
            ];
        });

        return response()->json([
            'data' => $materials,
            'total_inventory_value' => $materials->sum('inventory_value'),
        ]);
    }
}
