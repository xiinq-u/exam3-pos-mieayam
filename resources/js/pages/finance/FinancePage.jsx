import { useEffect, useState } from "react";
import axios from "axios";
import { Link, Navigate } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import {
    getPageCache,
    removePageCache,
    removePageCacheByPrefix,
    setPageCache,
} from "../../services/pageCache";
import formatCurrency from "../../utils/formatCurrency";
import ProfitLoss from "./ProfitLoss";

function FinancePage() {
    const token = getStoredToken();
    const today = new Date().toISOString().split("T")[0];
    const cachedExpenses = getPageCache(`expenses_${today}_${today}`);
    const cachedIncomes = getPageCache(`incomes_${today}_${today}`);
    const cachedCategories = getPageCache("financial_categories");
    const cachedShifts = getPageCache("cashier_shifts");
    const cachedProfitLoss = getPageCache(`profit_loss_${today}_${today}`);
    const [expenses, setExpenses] = useState(Array.isArray(cachedExpenses?.data) ? cachedExpenses.data : []);
    const [incomes, setIncomes] = useState(Array.isArray(cachedIncomes?.data) ? cachedIncomes.data : []);
    const [financialCategories, setFinancialCategories] = useState(Array.isArray(cachedCategories?.data) ? cachedCategories.data : []);
    const [shifts, setShifts] = useState(Array.isArray(cachedShifts?.data) ? cachedShifts.data : []);
    const [profitLoss, setProfitLoss] = useState(cachedProfitLoss?.data ?? null);
    const [isInitialLoading, setIsInitialLoading] = useState(!cachedExpenses?.data && !cachedIncomes?.data && !cachedProfitLoss?.data);
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [error, setError] = useState("");
    const [expense, setExpense] = useState({
        category: "",
        description: "",
        amount: "",
        expense_date: today,
    });
    const [income, setIncome] = useState({
        category: "",
        description: "",
        amount: "",
        income_date: today,
    });
    const [openingCash, setOpeningCash] = useState("0");
    const [startDate, setStartDate] = useState(today);
    const [endDate, setEndDate] = useState(today);
    const [actualCash, setActualCash] = useState("");
    const [closingNote, setClosingNote] = useState("");
    const [newCategory, setNewCategory] = useState("");
    const [categoryType, setCategoryType] = useState("expense");

    const headers = { Authorization: `Bearer ${token}` };
    const loadData = async () => {
        const expenseKey = `expenses_${startDate}_${endDate}`;
        const incomeKey = `incomes_${startDate}_${endDate}`;
        const profitLossKey = `profit_loss_${startDate}_${endDate}`;
        const expenseCache = getPageCache(expenseKey);
        const incomeCache = getPageCache(incomeKey);
        const categoryCache = getPageCache("financial_categories");
        const shiftCache = getPageCache("cashier_shifts");
        const profitLossCache = getPageCache(profitLossKey);

        if (expenseCache?.data || incomeCache?.data || profitLossCache?.data) {
            if (expenseCache?.data) setExpenses(Array.isArray(expenseCache.data) ? expenseCache.data : []);
            if (incomeCache?.data) setIncomes(Array.isArray(incomeCache.data) ? incomeCache.data : []);
            if (categoryCache?.data) setFinancialCategories(Array.isArray(categoryCache.data) ? categoryCache.data : []);
            if (shiftCache?.data) setShifts(Array.isArray(shiftCache.data) ? shiftCache.data : []);
            if (profitLossCache?.data) setProfitLoss(profitLossCache.data);
            setIsInitialLoading(false);
            setIsRefreshing(true);
        } else if (expenses.length || incomes.length || profitLoss) {
            setIsRefreshing(true);
        } else {
            setIsInitialLoading(true);
        }

        try {
            setError("");
            const query = `start_date=${startDate}&end_date=${endDate}`;
            const [
                expenseResponse,
                incomeResponse,
                categoryResponse,
                shiftResponse,
                reportResponse,
            ] = await Promise.all([
                axios.get(`/api/expenses?${query}`, { headers }),
                axios.get(`/api/incomes?${query}`, { headers }),
                axios.get("/api/financial-categories", { headers }),
                axios.get("/api/shifts", { headers }),
                axios.get(`/api/reports/profit-loss?${query}`, { headers }),
            ]);
            const freshExpenses = Array.isArray(expenseResponse.data.data) ? expenseResponse.data.data : [];
            const freshIncomes = Array.isArray(incomeResponse.data.data) ? incomeResponse.data.data : [];
            const freshCategories = Array.isArray(categoryResponse.data.data) ? categoryResponse.data.data : [];
            const freshShifts = Array.isArray(shiftResponse.data.data) ? shiftResponse.data.data : [];
            setExpenses(freshExpenses);
            setIncomes(freshIncomes);
            setFinancialCategories(freshCategories);
            setShifts(freshShifts);
            setProfitLoss(reportResponse.data);
            setPageCache(expenseKey, freshExpenses);
            setPageCache(incomeKey, freshIncomes);
            setPageCache("financial_categories", freshCategories);
            setPageCache("cashier_shifts", freshShifts);
            setPageCache(profitLossKey, reportResponse.data);
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Data keuangan tidak dapat dimuat.",
            );
        } finally {
            setIsInitialLoading(false);
            setIsRefreshing(false);
        }
    };

    useEffect(() => {
        if (token) {
            loadData();
        }
    }, [token, startDate, endDate]);

    const saveExpense = async (event) => {
        event.preventDefault();
        try {
            await axios.post(
                "/api/expenses",
                { ...expense, amount: Number(expense.amount) },
                { headers },
            );
            setExpense({
                category: "",
                description: "",
                amount: "",
                expense_date: today,
            });
            removePageCacheByPrefix("expenses_");
            removePageCacheByPrefix("profit_loss_");
            removePageCache("dashboard");
            removePageCache("owner_dashboard");
            await loadData();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Pengeluaran gagal disimpan.",
            );
        }
    };

    const saveIncome = async (event) => {
        event.preventDefault();
        try {
            await axios.post(
                "/api/incomes",
                { ...income, amount: Number(income.amount) },
                { headers },
            );
            setIncome({
                category: "",
                description: "",
                amount: "",
                income_date: today,
            });
            removePageCacheByPrefix("incomes_");
            removePageCacheByPrefix("profit_loss_");
            removePageCache("dashboard");
            removePageCache("owner_dashboard");
            await loadData();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Pemasukan gagal disimpan.",
            );
        }
    };

    const saveFinancialCategory = async (event) => {
        event.preventDefault();
        try {
            await axios.post(
                "/api/financial-categories",
                { name: newCategory, type: categoryType },
                { headers },
            );
            setNewCategory("");
            removePageCache("financial_categories");
            await loadData();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Kategori gagal disimpan.",
            );
        }
    };

    const openShift = async () => {
        try {
            await axios.post(
                "/api/shifts/open",
                { opening_cash: Number(openingCash) },
                { headers },
            );
            removePageCache("cashier_shifts");
            await loadData();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message || "Shift gagal dibuka.",
            );
        }
    };

    const closeShift = async (shiftId) => {
        try {
            await axios.post(
                `/api/shifts/${shiftId}/close`,
                { actual_cash: Number(actualCash), closing_note: closingNote },
                { headers },
            );
            setActualCash("");
            setClosingNote("");
            removePageCache("cashier_shifts");
            await loadData();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message || "Shift gagal ditutup.",
            );
        }
    };

    const exportProfitLoss = async (format) => {
        try {
            setError("");
            const query = `start_date=${startDate}&end_date=${endDate}`;
            const response = await axios.get(
                `/api/reports/profit-loss/export/${format}?${query}`,
                {
                    headers,
                    responseType: "blob",
                },
            );
            const extension = format === "excel" ? "xlsx" : "pdf";
            const url = URL.createObjectURL(response.data);
            const link = document.createElement("a");
            link.href = url;
            link.download = `laporan-laba-rugi-${startDate}-sampai-${endDate}.${extension}`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Laporan gagal diekspor.",
            );
        }
    };

    const payOrder = async () => {
        try {
            const response = await axios.post(
                `/api/orders/${createdOrder.id}/pay`,
                {
                    payment_method: paymentMethod,
                    paid_amount:
                        paymentMethod === "cash" ? Number(paidAmount) : null,
                },
                { headers: { Authorization: `Bearer ${token}` } },
            );
            setCreatedOrder(response.data.data);
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message || "Pembayaran gagal.",
            );
        }
    };

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    if (isInitialLoading && !profitLoss && expenses.length === 0 && incomes.length === 0) {
        return <FinanceSkeleton />;
    }

    return (
        <div className="min-h-screen bg-slate-100 p-8">
            <div className="mx-auto max-w-6xl space-y-6">
                {isRefreshing ? <div className="text-right text-xs text-slate-400">Memperbarui...</div> : null}
                <div className="flex items-center justify-between rounded-2xl bg-white p-6 shadow">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-rose-600">
                            POS Mie Ayam
                        </p>
                        <h1 className="mt-2 text-3xl font-bold text-slate-800">
                            Keuangan & Shift
                        </h1>
                    </div>
                    <Link
                        to="/"
                        className="rounded-xl bg-slate-800 px-4 py-2 font-medium text-white"
                    >
                        Dashboard
                    </Link>
                </div>
                {error ? (
                    <p className="rounded-xl bg-red-50 p-3 text-red-600">
                        {error}
                    </p>
                ) : null}
                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard
                        label="Penjualan Bersih"
                        value={formatCurrency(
                            (profitLoss?.sales || 0) -
                                (profitLoss?.refunds || 0),
                        )}
                    />
                    <StatCard
                        label="Pemasukan Lain"
                        value={formatCurrency(profitLoss?.other_income)}
                    />
                    <StatCard
                        label="Refund"
                        value={formatCurrency(profitLoss?.refunds)}
                    />
                    <StatCard
                        label="Pengeluaran"
                        value={formatCurrency(profitLoss?.expenses)}
                    />
                    <StatCard
                        label="Laba Bersih"
                        value={formatCurrency(profitLoss?.net_profit)}
                    />
                </div>
                <div className="flex flex-wrap gap-3 rounded-2xl bg-white p-4 shadow">
                    <input
                        type="date"
                        value={startDate}
                        onChange={(event) => setStartDate(event.target.value)}
                        className="rounded-xl border p-2"
                    />
                    <input
                        type="date"
                        value={endDate}
                        onChange={(event) => setEndDate(event.target.value)}
                        className="rounded-xl border p-2"
                    />
                    <button
                        type="button"
                        onClick={() => exportProfitLoss("pdf")}
                        className="rounded-xl bg-rose-600 px-4 py-2 font-medium text-white"
                    >
                        Export PDF
                    </button>
                    <button
                        type="button"
                        onClick={() => exportProfitLoss("excel")}
                        className="rounded-xl bg-emerald-600 px-4 py-2 font-medium text-white"
                    >
                        Export Excel
                    </button>
                    <span className="self-center text-sm text-slate-500">
                        Filter laba-rugi dan pengeluaran
                    </span>
                </div>
                <ProfitLoss report={profitLoss} />
                <form
                    onSubmit={saveFinancialCategory}
                    className="flex flex-wrap gap-2 rounded-2xl bg-white p-4 shadow"
                >
                    <input
                        required
                        value={newCategory}
                        onChange={(event) => setNewCategory(event.target.value)}
                        placeholder="Kategori baru"
                        className="rounded-xl border p-2"
                    />
                    <select
                        value={categoryType}
                        onChange={(event) =>
                            setCategoryType(event.target.value)
                        }
                        className="rounded-xl border p-2"
                    >
                        <option value="expense">Pengeluaran</option>
                        <option value="income">Pemasukan</option>
                    </select>
                    <button className="rounded-xl bg-slate-800 px-4 py-2 text-white">
                        Tambah Kategori
                    </button>
                </form>
                <div className="grid gap-6 md:grid-cols-2">
                    <form
                        onSubmit={saveExpense}
                        className="space-y-3 rounded-2xl bg-white p-6 shadow"
                    >
                        <h2 className="text-lg font-bold">Catat Pengeluaran</h2>
                        <select
                            required
                            value={expense.category}
                            onChange={(event) =>
                                setExpense({
                                    ...expense,
                                    category: event.target.value,
                                })
                            }
                            className="w-full rounded-xl border p-2"
                        >
                            <option value="">Pilih kategori</option>
                            {financialCategories
                                .filter(
                                    (category) => category.type === "expense",
                                )
                                .map((category) => (
                                    <option
                                        key={category.id}
                                        value={category.name}
                                    >
                                        {category.name}
                                    </option>
                                ))}
                        </select>
                        <input
                            required
                            placeholder="Keterangan"
                            value={expense.description}
                            onChange={(event) =>
                                setExpense({
                                    ...expense,
                                    description: event.target.value,
                                })
                            }
                            className="w-full rounded-xl border p-2"
                        />
                        <input
                            required
                            type="number"
                            min="1"
                            placeholder="Nominal"
                            value={expense.amount}
                            onChange={(event) =>
                                setExpense({
                                    ...expense,
                                    amount: event.target.value,
                                })
                            }
                            className="w-full rounded-xl border p-2"
                        />
                        <input
                            required
                            type="date"
                            value={expense.expense_date}
                            onChange={(event) =>
                                setExpense({
                                    ...expense,
                                    expense_date: event.target.value,
                                })
                            }
                            className="w-full rounded-xl border p-2"
                        />
                        <button className="rounded-xl bg-rose-600 px-4 py-2 font-medium text-white">
                            Simpan
                        </button>
                    </form>
                    <form
                        onSubmit={saveIncome}
                        className="space-y-3 rounded-2xl bg-white p-6 shadow"
                    >
                        <h2 className="text-lg font-bold">
                            Catat Pemasukan Lain
                        </h2>
                        <select
                            required
                            value={income.category}
                            onChange={(event) =>
                                setIncome({
                                    ...income,
                                    category: event.target.value,
                                })
                            }
                            className="w-full rounded-xl border p-2"
                        >
                            <option value="">Pilih kategori</option>
                            {financialCategories
                                .filter(
                                    (category) => category.type === "income",
                                )
                                .map((category) => (
                                    <option
                                        key={category.id}
                                        value={category.name}
                                    >
                                        {category.name}
                                    </option>
                                ))}
                        </select>
                        <input
                            required
                            placeholder="Keterangan"
                            value={income.description}
                            onChange={(event) =>
                                setIncome({
                                    ...income,
                                    description: event.target.value,
                                })
                            }
                            className="w-full rounded-xl border p-2"
                        />
                        <input
                            required
                            type="number"
                            min="1"
                            placeholder="Nominal"
                            value={income.amount}
                            onChange={(event) =>
                                setIncome({
                                    ...income,
                                    amount: event.target.value,
                                })
                            }
                            className="w-full rounded-xl border p-2"
                        />
                        <input
                            required
                            type="date"
                            value={income.income_date}
                            onChange={(event) =>
                                setIncome({
                                    ...income,
                                    income_date: event.target.value,
                                })
                            }
                            className="w-full rounded-xl border p-2"
                        />
                        <button className="rounded-xl bg-emerald-600 px-4 py-2 font-medium text-white">
                            Simpan
                        </button>
                    </form>
                    <div className="space-y-3 rounded-2xl bg-white p-6 shadow">
                        <h2 className="text-lg font-bold">Shift Kasir</h2>
                        <input
                            type="number"
                            min="0"
                            value={openingCash}
                            onChange={(event) =>
                                setOpeningCash(event.target.value)
                            }
                            className="w-full rounded-xl border p-2"
                        />
                        <button
                            type="button"
                            onClick={openShift}
                            className="rounded-xl bg-emerald-600 px-4 py-2 font-medium text-white"
                        >
                            Buka Shift
                        </button>
                        {shifts.find((shift) => !shift.closed_at) ? (
                            <>
                                <input
                                    type="number"
                                    min="0"
                                    placeholder="Kas aktual"
                                    value={actualCash}
                                    onChange={(event) =>
                                        setActualCash(event.target.value)
                                    }
                                    className="w-full rounded-xl border p-2"
                                />
                                <input
                                    placeholder="Catatan penutupan"
                                    value={closingNote}
                                    onChange={(event) =>
                                        setClosingNote(event.target.value)
                                    }
                                    className="w-full rounded-xl border p-2"
                                />
                                <button
                                    type="button"
                                    disabled={!actualCash}
                                    onClick={() =>
                                        closeShift(
                                            shifts.find(
                                                (shift) => !shift.closed_at,
                                            ).id,
                                        )
                                    }
                                    className="rounded-xl bg-slate-800 px-4 py-2 font-medium text-white disabled:opacity-50"
                                >
                                    Tutup Shift
                                </button>
                            </>
                        ) : null}
                        {shifts.slice(0, 5).map((shift) => (
                            <p
                                key={shift.id}
                                className="rounded-xl bg-slate-50 p-3 text-sm"
                            >
                                {shift.user?.name || "Kasir"} —{" "}
                                {shift.closed_at
                                    ? `Tutup: ${formatCurrency(shift.actual_cash)}`
                                    : "Masih terbuka"}
                            </p>
                        ))}
                    </div>
                </div>
                <div className="rounded-2xl bg-white p-6 shadow">
                    <h2 className="mb-3 text-lg font-bold">
                        Riwayat Pengeluaran
                    </h2>
                    {expenses.map((item) => (
                        <p key={item.id} className="border-b py-2 text-sm">
                            {item.category}: {item.description} —{" "}
                            {formatCurrency(item.amount)}{" "}
                            {item.cancelled_at ? "(dibatalkan)" : ""}
                        </p>
                    ))}
                </div>
            </div>
        </div>
    );
}

function FinanceSkeleton() {
    return (
        <main className="min-h-screen bg-slate-100 p-8">
            <div className="mx-auto max-w-6xl animate-pulse space-y-6">
                <div className="h-28 rounded-2xl bg-white" />
                <div className="grid gap-6 md:grid-cols-3">
                    {[1, 2, 3].map((item) => <div key={item} className="h-32 rounded-2xl bg-white" />)}
                </div>
                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="h-96 rounded-2xl bg-white" />
                    <div className="h-96 rounded-2xl bg-white" />
                </div>
            </div>
        </main>
    );
}

export default FinancePage;
