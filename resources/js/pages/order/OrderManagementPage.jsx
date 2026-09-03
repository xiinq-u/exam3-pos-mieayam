import { useEffect, useState } from "react";
import axios from "axios";
import { Link, Navigate } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import {
    getPageCache,
    invalidateOrderPageCaches,
    setPageCache,
} from "../../services/pageCache";
import formatCurrency from "../../utils/formatCurrency";

function OrderManagementPage() {
    const token = getStoredToken();
    const [orders, setOrders] = useState([]);
    const [status, setStatus] = useState("completed");
    const [error, setError] = useState("");
    const [reason, setReason] = useState({});
    const [amount, setAmount] = useState({});
    const [isInitialLoading, setIsInitialLoading] = useState(true);
    const [isRefreshing, setIsRefreshing] = useState(false);
    const headers = { Authorization: `Bearer ${token}` };

    const loadOrders = async () => {
        const cacheKey = `managed_orders_${status}`;
        const cachedOrders = getPageCache(cacheKey);

        if (cachedOrders?.data) {
            setOrders(Array.isArray(cachedOrders.data) ? cachedOrders.data : []);
            setIsInitialLoading(false);
            setIsRefreshing(true);
        } else if (orders.length === 0) {
            setIsInitialLoading(true);
        } else {
            setIsRefreshing(true);
        }

        try {
            const response = await axios.get(`/api/orders?status=${status}`, {
                headers,
            });
            const freshOrders = Array.isArray(response.data.data)
                ? response.data.data
                : [];
            setOrders(freshOrders);
            setPageCache(cacheKey, freshOrders);
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Pesanan tidak dapat dimuat.",
            );
        } finally {
            setIsInitialLoading(false);
            setIsRefreshing(false);
        }
    };

    const updateEdit = (material, field, value) =>
        setEdits((current) => ({
            ...current,
            [material.id]: {
                ...(current[material.id] || {
                    minimum_stock: material.minimum_stock,
                    purchase_price: material.purchase_price,
                }),
                [field]: value,
            },
        }));
    const saveMaterial = async (material) => {
        try {
            const data = edits[material.id] || material;
            await axios.patch(
                `/api/materials/${material.id}`,
                {
                    minimum_stock: Number(data.minimum_stock),
                    purchase_price: Number(data.purchase_price),
                },
                { headers: { Authorization: `Bearer ${token}` } },
            );
            await fetchMaterials();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Bahan gagal diperbarui.",
            );
        }
    };
    const deactivateMaterial = async (material) => {
        if (!window.confirm(`Nonaktifkan ${material.name}?`)) {
            return;
        }
        try {
            await axios.delete(`/api/materials/${material.id}`, {
                headers: { Authorization: `Bearer ${token}` },
            });
            await fetchMaterials();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Bahan gagal dinonaktifkan.",
            );
        }
    };

    const loadMovements = async (material) => {
        try {
            const query = new URLSearchParams({
                start_date: movementStartDate,
                end_date: movementEndDate,
            });
            const response = await axios.get(
                `/api/materials/${material.id}?${query.toString()}`,
                { headers: { Authorization: `Bearer ${token}` } },
            );
            setSelectedMaterial(response.data.material);
            setMovements(response.data.movements || []);
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Riwayat stok tidak dapat dimuat.",
            );
        }
    };

    useEffect(() => {
        if (token) {
            loadOrders();
        }
    }, [token, status]);

    const action = async (order, actionName) => {
        try {
            const payload =
                actionName === "refund"
                    ? {
                          amount: Number(amount[order.id]),
                          reason: reason[order.id],
                      }
                    : { reason: reason[order.id] };
            await axios.post(`/api/orders/${order.id}/${actionName}`, payload, {
                headers,
            });
            invalidateOrderPageCaches();
            await loadOrders();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Tindakan gagal diproses.",
            );
        }
    };

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    return (
        <div className="min-h-screen bg-slate-100 p-8">
            <div className="mx-auto max-w-5xl space-y-6">
                <div className="flex items-center justify-between rounded-2xl bg-white p-6 shadow">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">
                            POS Mie Ayam
                        </p>
                        <h1 className="mt-2 text-3xl font-bold text-slate-800">
                            Pembatalan & Refund
                        </h1>
                    </div>
                    <Link
                        to="/"
                        className="rounded-xl bg-slate-800 px-4 py-2 text-white"
                    >
                        Dashboard
                    </Link>
                </div>
                {error ? (
                    <p className="rounded-xl bg-red-50 p-3 text-red-600">
                        {error}
                    </p>
                ) : null}
                <select
                    value={status}
                    onChange={(event) => setStatus(event.target.value)}
                    className="rounded-xl border p-2"
                >
                    <option value="completed">Pesanan selesai (refund)</option>
                    <option value="pending">Pesanan pending (batal)</option>
                    <option value="processed">Pesanan diproses (batal)</option>
                    <option value="ready">Pesanan siap (batal)</option>
                </select>
                {isRefreshing ? <span className="text-xs text-slate-400">Memperbarui...</span> : null}
                {isInitialLoading ? (
                    <div className="space-y-4">
                        {[1, 2, 3].map((item) => <div key={item} className="h-44 animate-pulse rounded-2xl bg-white shadow" />)}
                    </div>
                ) : null}
                {!isInitialLoading ? orders.map((order) => (
                    <div
                        key={order.id}
                        className="space-y-3 rounded-2xl bg-white p-5 shadow"
                    >
                        <div className="flex justify-between">
                            <div>
                                <p className="font-bold">
                                    #{order.queue_number || order.id} —{" "}
                                    {order.customer_name}
                                </p>
                                <p className="text-sm text-slate-500">
                                    {formatCurrency(order.total)} ·{" "}
                                    {order.payment_status}
                                </p>
                            </div>
                            <span className="text-sm">{order.status}</span>
                        </div>
                        <input
                            value={reason[order.id] || ""}
                            onChange={(event) =>
                                setReason({
                                    ...reason,
                                    [order.id]: event.target.value,
                                })
                            }
                            placeholder="Alasan wajib"
                            className="w-full rounded-xl border p-2"
                        />
                        {order.status === "completed" ? (
                            <div className="flex gap-2">
                                <input
                                    type="number"
                                    min="1"
                                    max={order.paid_amount}
                                    value={amount[order.id] || ""}
                                    onChange={(event) =>
                                        setAmount({
                                            ...amount,
                                            [order.id]: event.target.value,
                                        })
                                    }
                                    placeholder="Jumlah refund"
                                    className="rounded-xl border p-2"
                                />
                                <button
                                    type="button"
                                    onClick={() => action(order, "refund")}
                                    className="rounded-xl bg-orange-600 px-4 py-2 text-white"
                                >
                                    Refund
                                </button>
                            </div>
                        ) : (
                            <button
                                type="button"
                                onClick={() => action(order, "cancel")}
                                className="rounded-xl bg-red-600 px-4 py-2 text-white"
                            >
                                Batalkan Pesanan
                            </button>
                        )}
                    </div>
                )) : null}
            </div>
        </div>
    );
}

export default OrderManagementPage;
