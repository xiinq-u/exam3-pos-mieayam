<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Material;
use App\Models\MaterialMovement;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:pending,processed,ready,completed,cancelled'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $orders = Order::with('items')
            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->limit($data['limit'] ?? 50)
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function kitchenOrders(Request $request): JsonResponse
    {
        $statuses = $request->query('status');

        $query = Order::with('items', 'statusHistories')
            ->latest('created_at');

        if ($statuses) {
            $statusArray = explode(',', $statuses);
            $query->whereIn('status', $statusArray);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function pendingOrders(): JsonResponse
    {
        $orders = Order::with('items')
            ->where('payment_status', 'unpaid')
            ->where('status', '!=', 'cancelled')
            ->latest('created_at')
            ->paginate(12);

        return response()->json($orders);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,processed,ready,completed'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $allowedStatuses = ['pending', 'processed', 'ready', 'completed'];
        $currentStatus = $order->status ?? 'pending';
        $newStatus = $data['status'];

        $currentIndex = array_search($currentStatus, $allowedStatuses, true);
        $newIndex = array_search($newStatus, $allowedStatuses, true);

        if ($newIndex === false || $currentIndex === false || $newIndex <= $currentIndex) {
            return response()->json(['message' => 'Transisi status tidak valid.'], 422);
        }

        DB::transaction(function () use ($data, $newStatus, $order, $request): void {
            $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);
            $previousStatus = $lockedOrder->status;
            $lockedOrder->update(['status' => $newStatus]);
            $lockedOrder->statusHistories()->create([
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
                'changed_by_user_id' => $request->user()?->id,
                'note' => $data['note'] ?? null,
            ]);

            if ($newStatus === 'completed') {
                $this->consumeMaterialsForOrder($lockedOrder, $request);
            }

            AuditLog::create(['user_id' => $request->user()->id, 'action' => 'order.status_updated', 'auditable_type' => Order::class, 'auditable_id' => $lockedOrder->id, 'properties' => ['from' => $previousStatus, 'to' => $newStatus]]);
        });

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui.',
            'order' => $order->fresh(),
        ]);
    }

    protected function consumeMaterialsForOrder(Order $order, Request $request): void
    {
        $requirements = [];
        $itemRequirements = [];

        foreach ($order->items()->with('product.productMaterials')->get() as $item) {
            $product = $item->product;

            if (! $product) {
                continue;
            }

            foreach ($product->productMaterials as $productMaterial) {
                $quantityNeeded = $productMaterial->quantity_per_unit * $item->quantity;
                $requirements[$productMaterial->material_id] = ($requirements[$productMaterial->material_id] ?? 0) + $quantityNeeded;
                $itemRequirements[$item->id][$productMaterial->material_id] = $quantityNeeded;
            }
        }

        $itemCosts = [];
        foreach ($requirements as $materialId => $quantityNeeded) {
            $material = Material::lockForUpdate()->find($materialId);

            if (! $material || $material->stock < $quantityNeeded) {
                throw new \RuntimeException('Stok bahan baku tidak mencukupi.');
            }

            $material->decrement('stock', $quantityNeeded);
            foreach ($itemRequirements as $itemId => $itemMaterials) {
                if (isset($itemMaterials[$materialId])) {
                    $itemCosts[$itemId] = ($itemCosts[$itemId] ?? 0) + ($itemMaterials[$materialId] * (float) $material->purchase_price);
                }
            }
            MaterialMovement::create([
                'material_id' => $material->id,
                'user_id' => $request->user()?->id,
                'type' => 'out',
                'quantity' => $quantityNeeded,
                'note' => 'Pemakaian untuk pesanan #'.$order->id,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
            ]);
        }

        foreach ($itemCosts as $itemId => $costPrice) {
            OrderItem::whereKey($itemId)->update(['cost_price' => $costPrice / $order->items()->find($itemId)->quantity]);
        }
    }

    public function cancelOrder(Request $request, Order $order): JsonResponse
    {
        if ($order->cancelled_at) {
            return response()->json(['message' => 'Pesanan sudah dibatalkan sebelumnya.'], 422);
        }

        if ($order->status === 'completed') {
            return response()->json(['message' => 'Pesanan selesai harus diproses melalui refund, bukan pembatalan.'], 422);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $order->update([
            'cancelled_at' => now(),
            'cancellation_reason' => $data['reason'],
            'status' => 'cancelled',
        ]);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'order.cancelled', 'auditable_type' => Order::class, 'auditable_id' => $order->id, 'properties' => ['reason' => $data['reason']]]);

        return response()->json([
            'message' => 'Pesanan berhasil dibatalkan.',
            'order' => $order->fresh(),
        ]);
    }

    public function refundOrder(Request $request, Order $order): JsonResponse
    {
        if ($order->refunded_at) {
            return response()->json(['message' => 'Pesanan sudah di-refund sebelumnya.'], 422);
        }

        if ($order->payment_status === 'refunded') {
            return response()->json(['message' => 'Status pembayaran sudah di-refund.'], 422);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $refundAmount = (float) $data['amount'];

        if ($order->payment_status !== 'paid' || $refundAmount > (float) $order->paid_amount) {
            return response()->json(['message' => 'Jumlah refund tidak boleh melebihi jumlah yang dibayarkan.'], 422);
        }

        $order->update([
            'refund_amount' => $refundAmount,
            'refunded_at' => now(),
            'refund_reason' => $data['reason'],
            'payment_status' => $refundAmount >= (float) $order->paid_amount ? 'refunded' : 'partial_refund',
        ]);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'order.refunded', 'auditable_type' => Order::class, 'auditable_id' => $order->id, 'properties' => ['amount' => $refundAmount, 'reason' => $data['reason']]]);

        return response()->json([
            'message' => 'Refund berhasil diproses.',
            'order' => $order->fresh(),
        ]);
    }

    public function getRefundedOrders(Request $request): JsonResponse
    {
        $query = Order::whereNotNull('refunded_at')
            ->latest('refunded_at');

        if ($request->has('start_date')) {
            $query->whereDate('refunded_at', '>=', $request->query('start_date'));
        }

        if ($request->has('end_date')) {
            $query->whereDate('refunded_at', '<=', $request->query('end_date'));
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function getCancelledOrders(Request $request): JsonResponse
    {
        $query = Order::whereNotNull('cancelled_at')
            ->latest('cancelled_at');

        if ($request->has('start_date')) {
            $query->whereDate('cancelled_at', '>=', $request->query('start_date'));
        }

        if ($request->has('end_date')) {
            $query->whereDate('cancelled_at', '<=', $request->query('end_date'));
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }
}
