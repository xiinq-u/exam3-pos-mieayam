import { useEffect, useState } from "react";
import axios from "axios";
import { Link } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import {
    invalidateKitchenDashboardCaches,
    getPageCache,
    setPageCache,
} from "../../services/pageCache";

const nextStatus = { pending: "processed", processed: "ready", ready: "completed" };
const buttonLabels = { pending: "Terima / Mulai Diproses", processed: "Pesanan Siap", ready: "Selesai" };

function KitchenOrdersPage() {
    const token = getStoredToken();
    const headers = { Authorization: `Bearer ${token}` };
    const cachedOrders = getPageCache("kitchen_orders");
    const [orders, setOrders] = useState(cachedOrders?.data?.data ?? []);
    const [loading, setLoading] = useState(!cachedOrders?.data);
    const [refreshing, setRefreshing] = useState(Boolean(cachedOrders?.data));
    const [error, setError] = useState("");
    const [updatingId, setUpdatingId] = useState(null);

    const loadOrders = async () => {
        try {
            const response = await axios.get("/api/kitchen/orders?status=pending,processed,ready", { headers });
            setOrders(response.data?.data ?? []);
            setPageCache("kitchen_orders", response.data);
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Antrean dapur tidak dapat dimuat.");
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        if (token) {
            void loadOrders();
        }
    }, [token]);

    const updateStatus = async (order) => {
        setUpdatingId(order.id);
        setError("");

        try {
            await axios.post(`/api/orders/${order.id}/status`, { status: nextStatus[order.status] }, { headers });
            invalidateKitchenDashboardCaches();
            await loadOrders();
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Status pesanan tidak dapat diperbarui.");
        } finally {
            setUpdatingId(null);
        }
    };

    return (
        <main className="min-h-screen bg-stone-50 px-4 py-8 sm:px-8">
            <div className="mx-auto max-w-[1500px] space-y-6">
                <header className="flex flex-wrap items-end justify-between gap-4"><div><p className="text-xs font-black tracking-[0.2em] text-amber-600 uppercase">Dapur</p><h1 className="text-4xl font-black text-stone-900">Antrean Dapur</h1></div><div className="text-right"><Link to="/kitchen" className="rounded-xl bg-stone-900 px-5 py-3 text-xs font-black text-white uppercase">Dashboard Dapur</Link>{refreshing ? <p className="mt-2 text-xs text-stone-400">Memperbarui...</p> : null}</div></header>
                {error ? <div className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</div> : null}
                {loading ? <OrderSkeleton /> : (
                    <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        {orders.length ? orders.map((order) => (
                            <article key={order.id} className="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                                <div className="flex items-start justify-between gap-3"><div><p className="text-4xl font-black text-red-600">#{order.queue_number ?? "-"}</p><h2 className="mt-1 text-lg font-black text-stone-900">{order.customer_name || "Tanpa nama"}</h2></div><span className="rounded-full bg-amber-100 px-3 py-1 text-[10px] font-black text-amber-700 uppercase">{order.status}</span></div>
                                <div className="mt-3 flex justify-between text-xs text-stone-500"><span className="uppercase">{String(order.order_type || "-").replaceAll("_", " ")}</span><span>{waitingMinutes(order.created_at)} menit menunggu</span></div>
                                {order.order_note ? <p className="mt-3 rounded-xl bg-red-50 p-3 text-xs font-bold text-red-700">Catatan: {order.order_note}</p> : null}
                                <div className="mt-4 space-y-2">{(order.items ?? []).map((item) => <div key={item.id} className="flex justify-between rounded-xl bg-stone-50 p-3 text-sm"><span className="font-bold">{item.product_name}</span><span className="font-black">{item.quantity}x</span></div>)}</div>
                                {nextStatus[order.status] ? <button type="button" disabled={updatingId === order.id} onClick={() => updateStatus(order)} className="mt-5 w-full rounded-xl bg-stone-900 py-3 text-xs font-black tracking-wider text-white uppercase hover:bg-red-600 disabled:opacity-50">{updatingId === order.id ? "Memperbarui..." : buttonLabels[order.status]}</button> : null}
                            </article>
                        )) : <p className="col-span-full rounded-2xl border border-dashed border-stone-200 bg-white p-12 text-center text-stone-400">Tidak ada antrean dapur.</p>}
                    </div>
                )}
            </div>
        </main>
    );
}

function waitingMinutes(createdAt) {
    return Math.max(0, Math.floor((Date.now() - new Date(createdAt).getTime()) / 60000));
}

function OrderSkeleton() {
    return <div className="grid animate-pulse gap-5 md:grid-cols-2 xl:grid-cols-3">{[1, 2, 3].map((item) => <div key={item} className="h-80 rounded-2xl bg-stone-200" />)}</div>;
}

export default KitchenOrdersPage;
