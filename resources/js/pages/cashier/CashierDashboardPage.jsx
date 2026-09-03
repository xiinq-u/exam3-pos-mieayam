import { useEffect, useState } from "react";
import axios from "axios";
import { Link } from "react-router-dom";
import DashboardStatCard from "../../components/DashboardStatCard";
import { getStoredToken } from "../../services/auth";
import { getPageCache, removePageCache, setPageCache } from "../../services/pageCache";
import formatCurrency from "../../utils/formatCurrency";

const orderGroups = [
    ["Pesanan Baru", "new_orders"],
    ["Sedang Diproses", "processing_orders"],
    ["Siap Disajikan", "ready_orders"],
    ["Belum Dibayar", "unpaid_orders"],
    ["Sudah Selesai", "completed_orders"],
];

function CashierDashboardPage() {
    const token = getStoredToken();
    const headers = { Authorization: `Bearer ${token}` };
    const cachedDashboard = getPageCache("cashier_dashboard");
    const [dashboard, setDashboard] = useState(cachedDashboard?.data ?? null);
    const [loading, setLoading] = useState(!cachedDashboard?.data);
    const [refreshing, setRefreshing] = useState(Boolean(cachedDashboard?.data));
    const [error, setError] = useState("");
    const [openingCash, setOpeningCash] = useState("");
    const [actualCash, setActualCash] = useState("");
    const [savingShift, setSavingShift] = useState(false);

    const loadDashboard = async () => {
        try {
            const response = await axios.get("/api/cashier/dashboard", { headers });
            setDashboard(response.data);
            setPageCache("cashier_dashboard", response.data);
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Dashboard kasir tidak dapat dimuat.");
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        if (token) {
            void loadDashboard();
        }
    }, [token]);

    const openShift = async (event) => {
        event.preventDefault();
        setSavingShift(true);
        setError("");

        try {
            await axios.post("/api/shifts/open", { opening_cash: Number(openingCash || 0) }, { headers });
            removePageCache("cashier_dashboard");
            setOpeningCash("");
            await loadDashboard();
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Shift tidak dapat dibuka.");
        } finally {
            setSavingShift(false);
        }
    };

    const closeShift = async (event) => {
        event.preventDefault();
        setSavingShift(true);
        setError("");

        try {
            await axios.post(`/api/shifts/${dashboard.shift.id}/close`, { actual_cash: Number(actualCash || 0) }, { headers });
            removePageCache("cashier_dashboard");
            setActualCash("");
            await loadDashboard();
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Shift tidak dapat ditutup.");
        } finally {
            setSavingShift(false);
        }
    };

    if (loading && !dashboard) {
        return <DashboardSkeleton />;
    }

    return (
        <main className="min-h-screen bg-stone-50 px-4 py-8 sm:px-8">
            <div className="mx-auto max-w-[1500px] space-y-8">
                <header className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p className="text-xs font-black tracking-[0.2em] text-red-600 uppercase">Meja Transaksi</p>
                        <h1 className="mt-1 text-4xl font-black tracking-tight text-stone-900">Dashboard Kasir</h1>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Link to="/cashier/transaction" className="rounded-xl bg-red-600 px-5 py-3 text-xs font-black tracking-wider text-white uppercase">Buat Transaksi Baru</Link>
                        <Link to="/orders" className="rounded-xl bg-stone-900 px-5 py-3 text-xs font-black tracking-wider text-white uppercase">Lihat Pesanan Aktif</Link>
                        <Link to="/orders" className="rounded-xl border border-red-200 bg-white px-5 py-3 text-xs font-black tracking-wider text-red-600 uppercase">Pesanan Belum Dibayar</Link>
                        <Link to="/orders" className="rounded-xl border border-stone-200 bg-white px-5 py-3 text-xs font-black tracking-wider text-stone-600 uppercase">Cetak Ulang Struk</Link>
                    </div>
                </header>

                {refreshing ? <p className="text-right text-xs text-stone-400">Memperbarui data...</p> : null}
                {error ? <div className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</div> : null}

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <DashboardStatCard label="Status Shift" value={dashboard?.shift ? "Sedang Buka" : "Tutup"} detail={dashboard?.shift?.opened_at ? new Date(dashboard.shift.opened_at).toLocaleString("id-ID") : "Belum ada shift aktif"} tone={dashboard?.shift ? "emerald" : "amber"} />
                    <DashboardStatCard label="Saldo Awal Kas" value={formatCurrency(dashboard?.opening_cash)} />
                    <DashboardStatCard label="Transaksi Selesai" value={dashboard?.completed_transactions ?? 0} />
                    <DashboardStatCard label="Pembayaran Tunai" value={formatCurrency(dashboard?.cash_payments)} tone="emerald" />
                    <DashboardStatCard label="Pembayaran QRIS" value={formatCurrency(dashboard?.qris_payments)} tone="sky" />
                    <DashboardStatCard label="Pelanggan Hari Ini" value={dashboard?.today_customers ?? 0} />
                    <DashboardStatCard label="Belum Dibayar" value={dashboard?.unpaid_orders_count ?? 0} tone="red" />
                    <DashboardStatCard label="Sedang Diproses" value={dashboard?.processing_orders_count ?? 0} tone="amber" />
                    <DashboardStatCard label="Antrean Terakhir" value={`#${dashboard?.last_queue_number ?? 0}`} />
                </section>

                <section id="shift" className="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <h2 className="text-lg font-black text-stone-900">Shift Saya</h2>
                    {dashboard?.shift ? (
                        <form onSubmit={closeShift} className="mt-4 flex flex-col gap-3 sm:flex-row">
                            <input type="number" min="0" value={actualCash} onChange={(event) => setActualCash(event.target.value)} required placeholder="Kas aktual saat tutup" className="min-w-0 flex-1 rounded-xl border border-stone-200 px-4 py-3" />
                            <button disabled={savingShift} className="rounded-xl bg-stone-900 px-5 py-3 text-xs font-black text-white uppercase disabled:opacity-50">Tutup Shift</button>
                        </form>
                    ) : (
                        <form onSubmit={openShift} className="mt-4 flex flex-col gap-3 sm:flex-row">
                            <input type="number" min="0" value={openingCash} onChange={(event) => setOpeningCash(event.target.value)} required placeholder="Saldo awal kas" className="min-w-0 flex-1 rounded-xl border border-stone-200 px-4 py-3" />
                            <button disabled={savingShift} className="rounded-xl bg-emerald-600 px-5 py-3 text-xs font-black text-white uppercase disabled:opacity-50">Buka Shift</button>
                        </form>
                    )}
                </section>

                <section className="space-y-6">
                    {orderGroups.map(([label, key]) => (
                        <OrderGroup key={key} title={label} orders={dashboard?.[key] ?? []} emphasize={key === "unpaid_orders"} />
                    ))}
                </section>
            </div>
        </main>
    );
}

function OrderGroup({ title, orders, emphasize }) {
    return (
        <div>
            <div className="mb-3 flex items-center gap-3"><h2 className="text-xl font-black text-stone-900">{title}</h2><span className="rounded-full bg-stone-200 px-2 py-1 text-[10px] font-black">{orders.length}</span></div>
            {orders.length ? (
                <div className="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
                    {orders.map((order) => (
                        <article key={order.id} className={`rounded-2xl border bg-white p-5 shadow-sm ${emphasize && order.status === "completed" ? "border-red-400 ring-2 ring-red-100" : "border-stone-200"}`}>
                            {emphasize && order.status === "completed" ? <p className="mb-3 rounded-lg bg-red-100 px-3 py-2 text-[10px] font-black tracking-wider text-red-700 uppercase">Pesanan selesai tetapi belum dibayar</p> : null}
                            <div className="flex justify-between gap-3"><div><p className="text-2xl font-black text-red-600">#{order.queue_number ?? "-"}</p><h3 className="font-black text-stone-900">{order.customer_name || "Tanpa nama"}</h3></div><div className="text-right text-xs text-stone-500"><p className="uppercase">{String(order.order_type || "-").replaceAll("_", " ")}</p><p>{new Date(order.created_at).toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })}</p></div></div>
                            <p className="mt-3 line-clamp-2 text-xs text-stone-600">{(order.items ?? []).map((item) => `${item.quantity}x ${item.product_name}`).join(", ") || "Belum ada item"}</p>
                            <div className="mt-4 flex items-center justify-between"><div><p className="text-[10px] font-black uppercase text-stone-400">{order.status} · {order.payment_status}</p><p className="font-black text-stone-900">{formatCurrency(order.total)}</p></div><Link to={`/orders/${order.id}`} className="rounded-xl bg-stone-900 px-4 py-2 text-[10px] font-black text-white uppercase">{order.payment_status === "unpaid" ? "Bayar" : "Detail"}</Link></div>
                        </article>
                    ))}
                </div>
            ) : <p className="rounded-2xl border border-dashed border-stone-200 bg-white p-5 text-sm text-stone-400">Tidak ada pesanan pada kelompok ini.</p>}
        </div>
    );
}

function DashboardSkeleton() {
    return <main className="min-h-screen bg-stone-50 p-8"><div className="mx-auto max-w-[1500px] animate-pulse space-y-6"><div className="h-16 rounded-2xl bg-stone-200" /><div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{Array.from({ length: 8 }, (_, index) => <div key={index} className="h-32 rounded-2xl bg-stone-200" />)}</div><div className="h-72 rounded-2xl bg-stone-200" /></div></main>;
}

export default CashierDashboardPage;
