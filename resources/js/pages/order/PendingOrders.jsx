import { useEffect, useState } from "react";
import axios from "axios";
import { Link, Navigate } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import { getPageCache, setPageCache } from "../../services/pageCache";

function PendingOrders() {
    const token = getStoredToken();
    const headers = { Authorization: `Bearer ${token}` };
    const cachedPendingOrders = getPageCache("pending_orders");
    const [orders, setOrders] = useState(
        Array.isArray(cachedPendingOrders?.data?.data)
            ? cachedPendingOrders.data.data
            : [],
    );
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(
        cachedPendingOrders?.data?.last_page || 1,
    );
    const [isInitialLoading, setIsInitialLoading] = useState(
        !cachedPendingOrders?.data,
    );
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [error, setError] = useState("");

    const loadOrders = async () => {
        const cacheKey = page === 1 ? "pending_orders" : `pending_orders_${page}`;
        const cachedPage = getPageCache(cacheKey);

        if (cachedPage?.data) {
            setOrders(Array.isArray(cachedPage.data.data) ? cachedPage.data.data : []);
            setLastPage(cachedPage.data.last_page || 1);
            setIsInitialLoading(false);
            setIsRefreshing(true);
        } else if (orders.length === 0) {
            setIsInitialLoading(true);
        } else {
            setIsRefreshing(true);
        }
        setError("");

        try {
            const response = await axios.get(
                `/api/cashier/orders/pending?page=${page}`,
                { headers },
            );
            setOrders(Array.isArray(response.data.data) ? response.data.data : []);
            setLastPage(response.data.last_page || 1);
            setPageCache(cacheKey, response.data);
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Antrean pesanan tidak dapat dimuat.",
            );
        } finally {
            setIsInitialLoading(false);
            setIsRefreshing(false);
        }
    };

    useEffect(() => {
        if (token) {
            loadOrders();
        }
    }, [token, page]);

    const formatNumber = (value) =>
        Number(value || 0).toLocaleString("id-ID", {
            maximumFractionDigits: 0,
        });
    const formatDate = (value) =>
        new Intl.DateTimeFormat("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        }).format(new Date(value));

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    return (
        <main className="mx-auto min-h-screen max-w-[1500px] bg-stone-50 px-4 py-8 sm:px-8">
            <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 className="text-4xl font-extrabold tracking-tighter text-stone-900">Pesanan</h1>
                    <p className="mt-1 text-sm font-medium text-stone-400">Pesanan yang belum diselesaikan</p>
                </div>
                <div className="flex w-fit flex-col items-center gap-1.5 sm:items-end">
                    <Link to="/cashier" className="w-fit rounded-xl bg-stone-900 px-5 py-2.5 text-center font-bold text-white transition-all hover:bg-red-600">
                        Kembali ke Kasir
                    </Link>
                    {isRefreshing ? <span className="text-xs text-stone-400">Memperbarui...</span> : null}
                </div>
            </div>

            {error ? (
                <div className="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">{error}</div>
            ) : null}
            {isInitialLoading ? <OrderGridSkeleton /> : null}

            {!isInitialLoading && orders.length === 0 ? (
                <div className="mx-auto max-w-xl rounded-xl border border-stone-200 bg-[#FFFDF9] p-16 text-center shadow-sm">
                    <div className="mb-4 text-4xl opacity-50">📜</div>
                    <p className="text-xs font-bold tracking-[0.2em] text-stone-400 uppercase">Tidak ada antrean pesanan</p>
                    <Link to="/cashier" className="mt-8 inline-block rounded-lg bg-red-600 px-8 py-3 text-[10px] font-black tracking-widest text-white uppercase transition-all hover:bg-red-700">
                        Kembali ke Kasir
                    </Link>
                </div>
            ) : null}

            {orders.length > 0 ? (
                <div className="grid grid-cols-1 items-start gap-6 md:grid-cols-2 xl:grid-cols-3">
                    {orders.map((order) => (
                        <article key={order.id} className="relative min-h-full border-t-4 border-red-600 bg-[#FFFDF9] p-6 shadow-sm">
                            <div className="mb-6 text-center">
                                <h2 className="text-xl font-black tracking-tighter text-stone-900 uppercase">Mie Ayam Puput</h2>
                                <p className="mt-1 text-[9px] font-bold text-stone-400 uppercase">{formatDate(order.created_at)}</p>
                            </div>

                            <div className="mb-4 border-b-2 border-dashed border-stone-200 pb-4 text-stone-700">
                                <OrderDetail label="No. Order" value={order.order_number} />
                                <OrderDetail label="Pembeli" value={order.customer_name || "-"} />
                                <OrderDetail label="Tipe" value={String(order.order_type || "-").replaceAll("_", " ")} uppercase />
                            </div>

                            <div className="mb-6 space-y-3 text-stone-700">
                                {(order.items || []).map((item) => (
                                    <div key={item.id} className="flex justify-between gap-3 text-xs">
                                        <span className="font-bold">{item.quantity} x {item.product_name}</span>
                                        <span className="shrink-0 font-mono font-bold text-red-600">
                                            Rp{formatNumber(Number(item.price) * Number(item.quantity))}
                                        </span>
                                    </div>
                                ))}
                            </div>

                            <div className="mb-6 border-t-2 border-dashed border-stone-200 pt-4">
                                <div className="flex items-center justify-between text-stone-900">
                                    <span className="text-sm font-black uppercase">Total</span>
                                    <span className="text-xl font-black text-red-600">Rp{formatNumber(order.total)}</span>
                                </div>
                            </div>

                            <Link
                                to={`/orders/${order.id}`}
                                className="block w-full bg-red-600 py-3 text-center text-[10px] font-black tracking-widest text-white uppercase transition-all hover:bg-red-700"
                            >
                                Selesaikan Pembayaran
                            </Link>
                            <div className="order-receipt-edge pointer-events-none absolute -bottom-2 left-0 h-4 w-full" />
                        </article>
                    ))}
                </div>
            ) : null}

            {lastPage > 1 ? (
                <div className="mt-8 flex items-center justify-center gap-3">
                    <button type="button" onClick={() => setPage((current) => Math.max(1, current - 1))} disabled={page === 1} className="rounded-lg border border-stone-200 bg-white px-4 py-2 text-xs font-bold disabled:opacity-40">Sebelumnya</button>
                    <span className="text-xs text-stone-500">Halaman {page} dari {lastPage}</span>
                    <button type="button" onClick={() => setPage((current) => Math.min(lastPage, current + 1))} disabled={page === lastPage} className="rounded-lg border border-stone-200 bg-white px-4 py-2 text-xs font-bold disabled:opacity-40">Berikutnya</button>
                </div>
            ) : null}
        </main>
    );
}

function OrderDetail({ label, value, uppercase = false }) {
    return (
        <div className="mb-1 flex justify-between gap-4 text-xs font-bold last:mb-0">
            <span>{label}</span>
            <span className={`text-right text-stone-900 ${uppercase ? "uppercase" : ""}`}>{value}</span>
        </div>
    );
}

function OrderGridSkeleton() {
    return (
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            {[1, 2, 3].map((item) => (
                <div key={item} className="h-80 animate-pulse rounded-xl border-t-4 border-stone-200 bg-white shadow-sm" />
            ))}
        </div>
    );
}

export default PendingOrders;
