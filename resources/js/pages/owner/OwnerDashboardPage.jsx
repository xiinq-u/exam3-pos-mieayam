import { useEffect, useState } from "react";
import axios from "axios";
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from "chart.js";
import { Bar, Line } from "react-chartjs-2";
import DashboardStatCard from "../../components/DashboardStatCard";
import { getStoredToken } from "../../services/auth";
import { getPageCache, setPageCache } from "../../services/pageCache";
import formatCurrency from "../../utils/formatCurrency";

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Tooltip, Legend);

const statusLabels = { safe: "Aman", low: "Menipis", out: "Habis" };
const statusStyles = {
    safe: "bg-emerald-100 text-emerald-700",
    low: "bg-amber-100 text-amber-700",
    out: "bg-red-100 text-red-700",
};

function OwnerDashboardPage() {
    const token = getStoredToken();
    const cachedDashboard = getPageCache("owner_dashboard");
    const [dashboard, setDashboard] = useState(cachedDashboard?.data ?? null);
    const [loading, setLoading] = useState(!cachedDashboard?.data);
    const [refreshing, setRefreshing] = useState(Boolean(cachedDashboard?.data));
    const [error, setError] = useState("");

    useEffect(() => {
        const loadDashboard = async () => {
            try {
                const response = await axios.get("/api/owner/dashboard", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                setDashboard(response.data);
                setPageCache("owner_dashboard", response.data);
            } catch (requestError) {
                setError(requestError?.response?.data?.message || "Dashboard owner tidak dapat dimuat.");
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

    const chart = dashboard?.revenue_chart ?? [];
    const lineData = {
        labels: chart.map((item) => item.label),
        datasets: [
            { label: "Pendapatan", data: chart.map((item) => item.revenue), borderColor: "#dc2626", backgroundColor: "#fecaca", tension: 0.35 },
            { label: "Laba Bersih", data: chart.map((item) => item.net_profit), borderColor: "#059669", backgroundColor: "#a7f3d0", tension: 0.35 },
        ],
    };
    const bestSelling = dashboard?.best_selling_products ?? [];
    const bestSellingData = {
        labels: bestSelling.map((item) => item.product_name),
        datasets: [{ label: "Terjual", data: bestSelling.map((item) => item.quantity), backgroundColor: "#f59e0b" }],
    };
    const chartOptions = { animation: false, responsive: true, maintainAspectRatio: false };

    return (
        <main className="min-h-screen bg-stone-50 px-4 py-8 sm:px-8">
            <div className="mx-auto max-w-[1500px] space-y-8">
                <header className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p className="text-xs font-black tracking-[0.2em] text-red-600 uppercase">Pengawasan Usaha</p>
                        <h1 className="mt-1 text-4xl font-black tracking-tight text-stone-900">Dashboard Owner</h1>
                    </div>
                    {refreshing ? <span className="text-xs text-stone-400">Memperbarui data...</span> : null}
                </header>

                {error ? <div className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</div> : null}

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <DashboardStatCard label="Penjualan Hari Ini" value={formatCurrency(dashboard?.today_sales)} tone="red" />
                    <DashboardStatCard label="Pendapatan Lain" value={formatCurrency(dashboard?.today_income)} tone="emerald" />
                    <DashboardStatCard label="Laba Kotor" value={formatCurrency(dashboard?.gross_profit)} tone="amber" />
                    <DashboardStatCard label="Laba Bersih" value={formatCurrency(dashboard?.net_profit)} tone="emerald" />
                    <DashboardStatCard label="Pengeluaran Hari Ini" value={formatCurrency(dashboard?.today_expenses)} />
                    <DashboardStatCard label="Transaksi Hari Ini" value={dashboard?.today_orders ?? 0} detail="Transaksi lunas" />
                    <DashboardStatCard label="Produk Aktif" value={dashboard?.active_products ?? 0} />
                    <DashboardStatCard label="Pegawai Aktif" value={dashboard?.active_employees ?? 0} />
                </section>

                <section>
                    <div className="mb-4">
                        <h2 className="text-xl font-black text-stone-900">Kondisi Bahan Baku</h2>
                        <p className="text-sm text-stone-500">Seluruh stok dan nilai persediaan saat ini.</p>
                    </div>
                    <div className="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <DashboardStatCard label="Bahan Aman" value={dashboard?.safe_materials ?? 0} tone="emerald" />
                        <DashboardStatCard label="Bahan Menipis" value={dashboard?.low_stock_materials ?? 0} tone="amber" />
                        <DashboardStatCard label="Bahan Habis" value={dashboard?.out_of_stock_materials ?? 0} tone="red" />
                        <DashboardStatCard label="Nilai Persediaan" value={formatCurrency(dashboard?.inventory_value)} tone="sky" />
                    </div>
                    <div className="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
                        <table className="w-full min-w-[760px] text-left text-sm">
                            <thead className="bg-stone-900 text-[10px] tracking-widest text-white uppercase">
                                <tr><th className="p-4">Bahan</th><th className="p-4">Stok</th><th className="p-4">Minimum</th><th className="p-4">Status</th><th className="p-4">Nilai</th><th className="p-4">Diperbarui</th></tr>
                            </thead>
                            <tbody>
                                {(dashboard?.materials ?? []).map((material) => (
                                    <tr key={material.id} className="border-t border-stone-100">
                                        <td className="p-4 font-bold text-stone-900">{material.name}</td>
                                        <td className="p-4">{material.stock} {material.unit}</td>
                                        <td className="p-4">{material.minimum_stock} {material.unit}</td>
                                        <td className="p-4"><span className={`rounded-full px-3 py-1 text-[10px] font-black uppercase ${statusStyles[material.status]}`}>{statusLabels[material.status]}</span></td>
                                        <td className="p-4 font-bold">{formatCurrency(material.inventory_value)}</td>
                                        <td className="p-4 text-xs text-stone-500">{new Date(material.updated_at).toLocaleString("id-ID")}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="grid gap-6 xl:grid-cols-2">
                    <ChartPanel title="Pendapatan & Laba Bersih 7 Hari"><Line data={lineData} options={chartOptions} /></ChartPanel>
                    <ChartPanel title="Menu Paling Laris"><Bar data={bestSellingData} options={chartOptions} /></ChartPanel>
                </section>

                <section className="grid gap-6 xl:grid-cols-3">
                    <Panel title="Stok yang Harus Dibeli">
                        {(dashboard?.low_stock_list ?? []).length ? dashboard.low_stock_list.map((material) => (
                            <ListRow key={material.id} primary={material.name} secondary={`${material.stock} ${material.unit} / minimum ${material.minimum_stock}`} />
                        )) : <EmptyText text="Tidak ada bahan yang perlu dibeli." />}
                    </Panel>
                    <Panel title="Dine-in vs Take Away">
                        <ListRow primary="Dine-in" secondary={`${dashboard?.order_type_summary?.dine_in ?? 0} transaksi`} />
                        <ListRow primary="Take Away" secondary={`${dashboard?.order_type_summary?.take_away ?? 0} transaksi`} />
                        <ListRow primary="Pemasukan lain" secondary={formatCurrency(dashboard?.income_expense_summary?.income)} />
                        <ListRow primary="Pengeluaran" secondary={formatCurrency(dashboard?.income_expense_summary?.expenses)} />
                    </Panel>
                    <Panel title="Aktivitas Terbaru">
                        {(dashboard?.recent_activities ?? []).length ? dashboard.recent_activities.map((activity) => (
                            <ListRow key={activity.id} primary={String(activity.action).replaceAll(".", " ")} secondary={activity.user?.name ?? "Sistem"} />
                        )) : <EmptyText text="Belum ada aktivitas." />}
                    </Panel>
                </section>

                <section className="grid gap-6 xl:grid-cols-3">
                    <Panel title="Transaksi Terbaru">
                        {(dashboard?.recent_transactions ?? []).length ? dashboard.recent_transactions.map((order) => (
                            <ListRow key={order.id} primary={order.order_number} secondary={formatCurrency(order.total)} />
                        )) : <EmptyText text="Belum ada transaksi." />}
                    </Panel>
                    <Panel title="Pengeluaran Terbaru">
                        {(dashboard?.recent_expenses ?? []).length ? dashboard.recent_expenses.map((expense) => (
                            <ListRow key={expense.id} primary={expense.description} secondary={formatCurrency(expense.amount)} />
                        )) : <EmptyText text="Belum ada pengeluaran." />}
                    </Panel>
                    <Panel title="Perubahan Stok Terbaru">
                        {(dashboard?.recent_stock_changes ?? []).length ? dashboard.recent_stock_changes.map((movement) => (
                            <ListRow key={movement.id} primary={movement.material?.name ?? "Bahan"} secondary={`${movement.type} ${movement.quantity} ${movement.material?.unit ?? ""}`} />
                        )) : <EmptyText text="Belum ada perubahan stok." />}
                    </Panel>
                </section>
            </div>
        </main>
    );
}

function ChartPanel({ title, children }) {
    return <div className="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><h2 className="mb-5 font-black text-stone-900">{title}</h2><div className="h-72">{children}</div></div>;
}

function Panel({ title, children }) {
    return <div className="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><h2 className="mb-4 font-black text-stone-900">{title}</h2><div className="space-y-2">{children}</div></div>;
}

function ListRow({ primary, secondary }) {
    return <div className="flex justify-between gap-4 rounded-xl bg-stone-50 p-3 text-sm"><span className="font-bold text-stone-800">{primary}</span><span className="text-right text-stone-500">{secondary}</span></div>;
}

function EmptyText({ text }) {
    return <p className="py-5 text-center text-sm text-stone-400">{text}</p>;
}

function DashboardSkeleton() {
    return <main className="min-h-screen bg-stone-50 p-8"><div className="mx-auto max-w-[1500px] animate-pulse space-y-6"><div className="h-16 rounded-2xl bg-stone-200" /><div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{Array.from({ length: 8 }, (_, index) => <div key={index} className="h-32 rounded-2xl bg-stone-200" />)}</div><div className="h-96 rounded-2xl bg-stone-200" /></div></main>;
}

export default OwnerDashboardPage;
