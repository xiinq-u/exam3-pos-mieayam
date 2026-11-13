import { useEffect, useMemo, useState } from "react";
import axios from "axios";
import { Link } from "react-router-dom";
import DashboardStatCard from "../../components/DashboardStatCard";
import { getStoredToken } from "../../services/auth";
import formatCurrency from "../../utils/formatCurrency";

function OwnerIncomePage() {
    const token = getStoredToken();
    const today = new Date().toLocaleDateString("sv-SE");
    const [startDate, setStartDate] = useState(today);
    const [endDate, setEndDate] = useState(today);
    const [orders, setOrders] = useState([]);
    const [incomes, setIncomes] = useState([]);
    const [categories, setCategories] = useState([]);
    const [report, setReport] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [newCategory, setNewCategory] = useState("");
    const [form, setForm] = useState({ category: "", description: "", amount: "", payment_method: "cash", income_date: today });

    const headers = useMemo(() => ({ Authorization: `Bearer ${token}` }), [token]);

    const loadData = async () => {
        setLoading(true);
        setError("");
        const query = `start_date=${startDate}&end_date=${endDate}`;

        try {
            const [ordersResponse, incomesResponse, categoriesResponse, reportResponse] = await Promise.all([
                axios.get(`/api/reports/order-details?${query}&limit=100`, { headers }),
                axios.get(`/api/incomes?${query}`, { headers }),
                axios.get("/api/financial-categories?type=income", { headers }),
                axios.get(`/api/reports/profit-loss?${query}`, { headers }),
            ]);

            setOrders((ordersResponse.data.data ?? []).filter((order) => order.payment_status === "paid"));
            setIncomes(incomesResponse.data.data ?? []);
            setCategories((categoriesResponse.data.data ?? []).filter((category) => category.type === "income"));
            setReport(reportResponse.data);
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Data pendapatan gagal dimuat.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        void loadData();
    }, [token, startDate, endDate]);

    const saveIncome = async (event) => {
        event.preventDefault();
        try {
            await axios.post("/api/incomes", { ...form, amount: Number(form.amount) }, { headers });
            setForm({ category: "", description: "", amount: "", payment_method: "cash", income_date: today });
            await loadData();
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Pemasukan lain gagal disimpan.");
        }
    };

    const saveCategory = async (event) => {
        event.preventDefault();
        try {
            const response = await axios.post("/api/financial-categories", { name: newCategory, type: "income" }, { headers });
            const category = response.data.data;
            setCategories((current) => [...current.filter((item) => item.id !== category.id), category].sort((first, second) => first.name.localeCompare(second.name, "id")));
            setForm((current) => ({ ...current, category: category.name }));
            setNewCategory("");
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Kategori pemasukan gagal disimpan.");
        }
    };

    const paymentLabel = (method) => ({ cash: "Tunai", qris: "QRIS", transfer: "Transfer" })[method] || method || "-";
    const transactionRows = [
        ...orders.map((order) => ({
            id: `order-${order.id}`,
            date: order.created_at,
            number: order.order_number,
            detail: (order.items ?? []).map((item) => `${item.product_name} × ${item.quantity}`).join(", "),
            method: paymentLabel(order.payment_method),
            amount: Number(order.total),
            type: "Penjualan",
            cancelled: false,
        })),
        ...incomes.map((income) => ({
            id: `income-${income.id}`,
            date: income.income_date,
            number: income.category,
            detail: income.description,
            method: paymentLabel(income.payment_method),
            amount: Number(income.amount),
            type: "Pemasukan lain",
            cancelled: Boolean(income.cancelled_at),
        })),
    ].sort((first, second) => new Date(second.date) - new Date(first.date));

    return (
        <main className="min-h-screen bg-stone-50 px-4 py-8 sm:px-8">
            <div className="mx-auto max-w-[1500px] space-y-6">
                <header className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p className="text-xs font-black tracking-[0.2em] text-red-600 uppercase">Keuangan Usaha</p>
                        <h1 className="mt-1 text-4xl font-black tracking-tight text-stone-900">Pendapatan</h1>
                        <p className="mt-2 text-sm text-stone-500">Omzet penjualan dan pemasukan lain pada periode terpilih.</p>
                    </div>
                    <Link to="/owner" className="rounded-xl bg-stone-900 px-4 py-2 text-sm font-bold text-white">Kembali ke Dashboard</Link>
                </header>

                {error ? <p className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</p> : null}

                <section className="flex flex-wrap items-end gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <label className="grid gap-1 text-xs font-bold text-stone-500">Dari tanggal<input type="date" value={startDate} max={endDate} onChange={(event) => setStartDate(event.target.value)} className="rounded-lg border border-stone-300 p-2 text-sm text-stone-900" /></label>
                    <label className="grid gap-1 text-xs font-bold text-stone-500">Sampai tanggal<input type="date" value={endDate} min={startDate} onChange={(event) => setEndDate(event.target.value)} className="rounded-lg border border-stone-300 p-2 text-sm text-stone-900" /></label>
                    <span className="pb-2 text-xs text-stone-400">{loading ? "Memuat data…" : `Periode ${startDate} – ${endDate}`}</span>
                </section>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <DashboardStatCard label="Omzet Penjualan" value={formatCurrency(report?.sales)} tone="red" />
                    <DashboardStatCard label="Pemasukan Lain" value={formatCurrency(report?.other_income)} tone="emerald" />
                    <DashboardStatCard label="Refund" value={formatCurrency(report?.refunds)} />
                    <DashboardStatCard label="Total Pendapatan Bersih" value={formatCurrency(Number(report?.sales || 0) - Number(report?.refunds || 0) + Number(report?.other_income || 0))} tone="amber" />
                </section>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                    <section className="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                        <div className="border-b border-stone-100 p-5">
                            <h2 className="text-lg font-black text-stone-900">Riwayat Pendapatan</h2>
                            <p className="mt-1 text-sm text-stone-500">Transaksi penjualan lunas dan pemasukan lain.</p>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="bg-stone-50 text-xs uppercase text-stone-500"><tr><th className="p-4">Tanggal</th><th className="p-4">Sumber</th><th className="p-4">Keterangan</th><th className="p-4">Metode</th><th className="p-4 text-right">Nominal</th></tr></thead>
                                <tbody>
                                    {transactionRows.map((row) => <tr key={row.id} className="border-t border-stone-100"><td className="p-4 whitespace-nowrap">{new Date(row.date).toLocaleDateString("id-ID")}</td><td className="p-4"><span className={`rounded-full px-2 py-1 text-xs font-bold ${row.type === "Penjualan" ? "bg-red-50 text-red-700" : "bg-emerald-50 text-emerald-700"}`}>{row.type}</span><div className="mt-1 text-xs text-stone-400">{row.number}</div></td><td className="max-w-sm p-4 text-stone-600">{row.detail || "-"}{row.cancelled ? <span className="ml-2 text-red-600">Dibatalkan</span> : null}</td><td className="p-4">{row.method}</td><td className={`p-4 text-right font-bold ${row.cancelled ? "text-stone-400 line-through" : "text-stone-900"}`}>{formatCurrency(row.amount)}</td></tr>)}
                                    {!loading && transactionRows.length === 0 ? <tr><td colSpan="5" className="p-8 text-center text-stone-400">Belum ada pendapatan pada periode ini.</td></tr> : null}
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <form onSubmit={saveIncome} className="h-fit space-y-3 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div><h2 className="text-lg font-black text-stone-900">Catat Pemasukan Lain</h2><p className="mt-1 text-sm text-stone-500">Contoh: penjualan kemasan atau pendapatan di luar pesanan.</p></div>
                        <select required value={form.category} onChange={(event) => setForm({ ...form, category: event.target.value })} className="w-full rounded-lg border border-stone-300 p-2.5 text-sm"><option value="">Pilih kategori pemasukan</option>{categories.map((category) => <option key={category.id} value={category.name}>{category.name}</option>)}</select>
                        <div className="flex gap-2"><input value={newCategory} onChange={(event) => setNewCategory(event.target.value)} placeholder="Tambah kategori baru" className="min-w-0 flex-1 rounded-lg border border-stone-300 p-2.5 text-sm" /><button type="button" disabled={!newCategory.trim()} onClick={saveCategory} className="rounded-lg border border-stone-300 px-3 text-xs font-bold text-stone-600 disabled:opacity-50">Tambah</button></div>
                        <input required value={form.description} onChange={(event) => setForm({ ...form, description: event.target.value })} placeholder="Keterangan" className="w-full rounded-lg border border-stone-300 p-2.5 text-sm" />
                        <input required type="number" min="1" value={form.amount} onChange={(event) => setForm({ ...form, amount: event.target.value })} placeholder="Nominal rupiah" className="w-full rounded-lg border border-stone-300 p-2.5 text-sm" />
                        <select value={form.payment_method} onChange={(event) => setForm({ ...form, payment_method: event.target.value })} className="w-full rounded-lg border border-stone-300 p-2.5 text-sm"><option value="cash">Tunai</option><option value="qris">QRIS</option><option value="transfer">Transfer</option></select>
                        <input required type="date" value={form.income_date} onChange={(event) => setForm({ ...form, income_date: event.target.value })} className="w-full rounded-lg border border-stone-300 p-2.5 text-sm" />
                        <button className="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-black text-white hover:bg-emerald-800">Simpan Pemasukan</button>
                    </form>
                </div>
            </div>
        </main>
    );
}

export default OwnerIncomePage;
