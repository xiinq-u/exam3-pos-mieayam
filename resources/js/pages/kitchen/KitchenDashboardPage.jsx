import { useEffect, useState } from "react";
import axios from "axios";
import { Link } from "react-router-dom";
import DashboardStatCard from "../../components/DashboardStatCard";
import { getStoredToken } from "../../services/auth";
import { getPageCache, setPageCache } from "../../services/pageCache";

function KitchenDashboardPage() {
    const token = getStoredToken();
    const cachedDashboard = getPageCache("kitchen_dashboard");
    const [dashboard, setDashboard] = useState(cachedDashboard?.data ?? null);
    const [loading, setLoading] = useState(!cachedDashboard?.data);
    const [refreshing, setRefreshing] = useState(Boolean(cachedDashboard?.data));
    const [error, setError] = useState("");

    useEffect(() => {
        const loadDashboard = async () => {
            try {
                const response = await axios.get("/api/kitchen/dashboard", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                setDashboard(response.data);
                setPageCache("kitchen_dashboard", response.data);
            } catch (requestError) {
                setError(requestError?.response?.data?.message || "Dashboard dapur tidak dapat dimuat.");
            } finally {
                setLoading(false);
                setRefreshing(false);
            }
        };

        if (token) {
            void loadDashboard();
        }
    }, [token]);

    if (loading && !dashboard) {
        return <DashboardSkeleton />;
    }

    const activeOrders = [
        ...(dashboard?.new_orders ?? []),
        ...(dashboard?.processing_orders ?? []),
        ...(dashboard?.ready_orders ?? []),
    ].slice(0, 6);

    return (
        <main className="min-h-screen bg-stone-50 px-4 py-8 sm:px-8">
            <div className="mx-auto max-w-[1500px] space-y-8">
                <header className="flex flex-wrap items-end justify-between gap-4">
                    <div><p className="text-xs font-black tracking-[0.2em] text-amber-600 uppercase">Operasional Masak</p><h1 className="mt-1 text-4xl font-black tracking-tight text-stone-900">Dashboard Dapur</h1></div>
                    <Link to="/kitchen/orders" className="rounded-xl bg-stone-900 px-5 py-3 text-xs font-black tracking-wider text-white uppercase">Buka Antrean Dapur</Link>
                </header>
                {refreshing ? <p className="text-right text-xs text-stone-400">Memperbarui data...</p> : null}
                {error ? <div className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</div> : null}

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <DashboardStatCard label="Pesanan Baru" value={dashboard?.new_orders_count ?? 0} tone="red" />
                    <DashboardStatCard label="Sedang Diproses" value={dashboard?.processing_orders_count ?? 0} tone="amber" />
                    <DashboardStatCard label="Pesanan Siap" value={dashboard?.ready_orders_count ?? 0} tone="emerald" />
                    <DashboardStatCard label="Tunggu Terlama" value={`${dashboard?.longest_waiting_minutes ?? 0} menit`} />
                    <DashboardStatCard label="Bahan Menipis" value={dashboard?.low_stock_count ?? 0} tone="amber" />
                    <DashboardStatCard label="Bahan Habis" value={dashboard?.out_of_stock_count ?? 0} tone="red" />
                </section>

                <section>
                    <div className="mb-4 flex items-center justify-between"><h2 className="text-xl font-black text-stone-900">Antrean Aktif</h2><Link to="/kitchen/orders" className="text-sm font-bold text-red-600">Lihat semua →</Link></div>
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {activeOrders.length ? activeOrders.map((order) => <KitchenOrderPreview key={order.id} order={order} />) : <p className="col-span-full rounded-2xl border border-dashed border-stone-200 bg-white p-8 text-center text-stone-400">Tidak ada antrean aktif.</p>}
                    </div>
                </section>

                <section className="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <div className="mb-4 flex items-center justify-between"><div><h2 className="text-xl font-black text-stone-900">Kebutuhan Bahan</h2><p className="text-sm text-stone-500">Bahan pada atau di bawah batas minimum.</p></div><Link to="/materials" className="text-sm font-bold text-red-600">Buka bahan baku →</Link></div>
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {(dashboard?.low_stock_materials ?? []).length ? dashboard.low_stock_materials.map((material) => (
                            <div key={material.id} className={`rounded-xl border p-4 ${Number(material.stock) === 0 ? "border-red-200 bg-red-50" : "border-amber-200 bg-amber-50"}`}><p className="font-black text-stone-900">{material.name}</p><p className="mt-1 text-sm text-stone-600">{material.stock} {material.unit} · minimum {material.minimum_stock}</p></div>
                        )) : <p className="text-sm text-stone-400">Seluruh bahan dalam kondisi aman.</p>}
                    </div>
                </section>
            </div>
        </main>
    );
}

function KitchenOrderPreview({ order }) {
    return <article className="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><div className="flex justify-between"><p className="text-3xl font-black text-red-600">#{order.queue_number ?? "-"}</p><span className="h-fit rounded-full bg-amber-100 px-3 py-1 text-[10px] font-black text-amber-700 uppercase">{order.status}</span></div><h3 className="mt-2 font-black text-stone-900">{order.customer_name || "Tanpa nama"}</h3><p className="mt-2 text-xs text-stone-500">{(order.items ?? []).map((item) => `${item.quantity}x ${item.product_name}`).join(", ")}</p></article>;
}

function DashboardSkeleton() {
    return <main className="min-h-screen bg-stone-50 p-8"><div className="mx-auto max-w-[1500px] animate-pulse space-y-6"><div className="h-16 rounded-2xl bg-stone-200" /><div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">{Array.from({ length: 6 }, (_, index) => <div key={index} className="h-32 rounded-2xl bg-stone-200" />)}</div><div className="h-72 rounded-2xl bg-stone-200" /></div></main>;
}

export default KitchenDashboardPage;
