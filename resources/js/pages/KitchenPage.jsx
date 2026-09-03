import { useEffect, useState } from "react";
import axios from "axios";
import { Link, Navigate, useNavigate } from "react-router-dom";
import { getStoredToken } from "../services/auth";
import {
    getPageCache,
    invalidateOrderPageCaches,
    removePageCache,
    setPageCache,
} from "../services/pageCache";

function KitchenPage() {
    const navigate = useNavigate();
    const token = getStoredToken();
    const cachedOrders = getPageCache("kitchen_orders");
    const [orders, setOrders] = useState(Array.isArray(cachedOrders?.data?.data) ? cachedOrders.data.data : []);
    const [loading, setLoading] = useState(!cachedOrders?.data);
    const [error, setError] = useState("");

    const loadOrders = async () => {
        try {
            const response = await axios.get(
                "/api/kitchen/orders?status=pending,processed",
                {
                    headers: { Authorization: `Bearer ${token}` },
                },
            );
            setOrders(Array.isArray(response.data.data) ? response.data.data : []);
            setPageCache("kitchen_orders", response.data);
        } catch (requestError) {
            console.error("Failed to load kitchen orders:", requestError);
            setError("Tidak dapat memuat antrean dapur.");
        }
    };

    useEffect(() => {
        if (!token) {
            navigate("/login");
            return;
        }

        const fetchOrders = async () => {
            await loadOrders();
            setLoading(false);
        };

        fetchOrders();
    }, [token, navigate]);

    const updateStatus = async (orderId, nextStatus) => {
        try {
            setError("");
            await axios.post(
                `/api/orders/${orderId}/status`,
                {
                    status: nextStatus,
                    note: "Status diperbarui dari dashboard dapur",
                },
                {
                    headers: { Authorization: `Bearer ${token}` },
                },
            );

            removePageCache("kitchen_orders");
            invalidateOrderPageCaches();
            await loadOrders();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Gagal memperbarui status.",
            );
        }
    };

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    return (
        <div className="min-h-screen bg-slate-100 p-8">
            <div className="mx-auto max-w-6xl space-y-6">
                <div className="flex items-center justify-between rounded-2xl bg-white p-6 shadow">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-amber-600">
                            POS Mie Ayam
                        </p>
                        <h1 className="mt-2 text-3xl font-bold text-slate-800">
                            Antrean Dapur
                        </h1>
                    </div>

                    <Link
                        to="/"
                        className="rounded-xl bg-slate-800 px-4 py-2 font-medium text-white hover:bg-slate-900"
                    >
                        Dashboard
                    </Link>
                </div>

                {error ? (
                    <div className="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
                        {error}
                    </div>
                ) : null}

                {selectedMaterial ? (
                    <div className="rounded-2xl bg-white p-6 shadow">
                        <div className="flex items-center justify-between">
                            <h2 className="text-xl font-bold">
                                Riwayat: {selectedMaterial.name}
                            </h2>
                            <button
                                type="button"
                                onClick={() => setSelectedMaterial(null)}
                            >
                                Tutup
                            </button>
                        </div>
                        <div className="mt-3 flex gap-2">
                            <input
                                type="date"
                                value={movementStartDate}
                                onChange={(event) =>
                                    setMovementStartDate(event.target.value)
                                }
                                className="rounded-xl border p-2"
                            />
                            <input
                                type="date"
                                value={movementEndDate}
                                onChange={(event) =>
                                    setMovementEndDate(event.target.value)
                                }
                                className="rounded-xl border p-2"
                            />
                            <button
                                type="button"
                                onClick={() => loadMovements(selectedMaterial)}
                                className="rounded-xl bg-violet-600 px-4 py-2 text-white"
                            >
                                Filter
                            </button>
                        </div>
                        <div className="mt-3 space-y-2">
                            {movements.map((movement) => (
                                <p
                                    key={movement.id}
                                    className="rounded-xl bg-slate-50 p-3 text-sm"
                                >
                                    {new Date(
                                        movement.created_at,
                                    ).toLocaleString("id-ID")}{" "}
                                    · {movement.loss_reason || movement.type} ·{" "}
                                    {movement.quantity} · {movement.note || "-"}
                                </p>
                            ))}
                        </div>
                    </div>
                ) : null}

                {loading ? (
                    <div className="space-y-4">
                        {[1, 2, 3].map((item) => (
                            <div key={item} className="h-40 animate-pulse rounded-2xl bg-white shadow" />
                        ))}
                    </div>
                ) : (
                    <div className="space-y-4">
                        {orders.length === 0 ? (
                            <div className="rounded-2xl bg-white p-6 shadow text-slate-600">
                                Tidak ada pesanan aktif di dapur.
                            </div>
                        ) : (
                            orders.map((order) => (
                                <div
                                    key={order.id}
                                    className="rounded-2xl bg-white p-6 shadow"
                                >
                                    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <p className="text-sm text-slate-500">
                                                #
                                                {order.order_number || order.id}
                                            </p>
                                            <h3 className="text-xl font-bold text-slate-800">
                                                {order.customer_name}
                                            </h3>
                                        </div>

                                        <span className="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">
                                            {order.status}
                                        </span>
                                    </div>

                                    <div className="mt-4 grid gap-2 md:grid-cols-2">
                                        {order.items?.map((item) => (
                                            <div
                                                key={`${order.id}-${item.id}`}
                                                className="rounded-xl bg-slate-50 p-3 text-sm text-slate-700"
                                            >
                                                <span className="font-semibold">
                                                    {item.quantity}x
                                                </span>{" "}
                                                {item.product_name}
                                            </div>
                                        ))}
                                    </div>

                                    <div className="mt-4 flex flex-wrap gap-3">
                                        {order.status === "pending" ? (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    updateStatus(
                                                        order.id,
                                                        "processed",
                                                    )
                                                }
                                                className="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                                            >
                                                Proses
                                            </button>
                                        ) : null}

                                        {order.status === "processed" ? (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    updateStatus(
                                                        order.id,
                                                        "ready",
                                                    )
                                                }
                                                className="rounded-xl bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700"
                                            >
                                                Siap
                                            </button>
                                        ) : null}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

export default KitchenPage;
