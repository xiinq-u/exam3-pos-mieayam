import { useEffect, useRef, useState } from "react";
import axios from "axios";
import { Link, Navigate, useNavigate } from "react-router-dom";
import { getStoredToken } from "../services/auth";
import { getPageCache, setPageCache } from "../services/pageCache";
import formatCurrency from "../utils/formatCurrency";

function ReportPage() {
    const navigate = useNavigate();
    const token = getStoredToken();
    const initialStartDate = new Date(Date.now() - 6 * 24 * 60 * 60 * 1000).toISOString().split("T")[0];
    const initialEndDate = new Date().toISOString().split("T")[0];
    const initialSalesCache = getPageCache(`sales_report_${initialStartDate}_${initialEndDate}_daily`);
    const initialProductCache = getPageCache(`product_sales_report_${initialStartDate}_${initialEndDate}`);
    const initialOrderTypeCache = getPageCache(`order_type_report_${initialStartDate}_${initialEndDate}`);
    const [salesData, setSalesData] = useState(Array.isArray(initialSalesCache?.data) ? initialSalesCache.data : []);
    const [productData, setProductData] = useState(Array.isArray(initialProductCache?.data) ? initialProductCache.data : []);
    const [orderTypeData, setOrderTypeData] = useState(Array.isArray(initialOrderTypeCache?.data) ? initialOrderTypeCache.data : []);
    const [isInitialLoading, setIsInitialLoading] = useState(!initialSalesCache?.data && !initialProductCache?.data && !initialOrderTypeCache?.data);
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [error, setError] = useState("");
    const [startDate, setStartDate] = useState(
        initialStartDate,
    );
    const [endDate, setEndDate] = useState(
        initialEndDate,
    );
    const [period, setPeriod] = useState("daily");

    const fetchReports = async () => {
        const salesKey = `sales_report_${startDate}_${endDate}_${period}`;
        const productKey = `product_sales_report_${startDate}_${endDate}`;
        const orderTypeKey = `order_type_report_${startDate}_${endDate}`;
        const cachedSales = getPageCache(salesKey);
        const cachedProducts = getPageCache(productKey);
        const cachedOrderTypes = getPageCache(orderTypeKey);

        if (cachedSales?.data || cachedProducts?.data || cachedOrderTypes?.data) {
            if (cachedSales?.data) setSalesData(Array.isArray(cachedSales.data) ? cachedSales.data : []);
            if (cachedProducts?.data) setProductData(Array.isArray(cachedProducts.data) ? cachedProducts.data : []);
            if (cachedOrderTypes?.data) setOrderTypeData(Array.isArray(cachedOrderTypes.data) ? cachedOrderTypes.data : []);
            setIsInitialLoading(false);
            setIsRefreshing(true);
        } else if (salesData.length || productData.length || orderTypeData.length) {
            setIsRefreshing(true);
        } else {
            setIsInitialLoading(true);
        }

        try {
            setError("");
            const params = { start_date: startDate, end_date: endDate, period };
            const queryString = new URLSearchParams(params).toString();

            const [sales, products, orderTypes] = await Promise.all([
                axios.get(`/api/reports/sales-summary?${queryString}`, {
                    headers: { Authorization: `Bearer ${token}` },
                }),
                axios.get(
                    `/api/reports/product-sales?start_date=${startDate}&end_date=${endDate}`,
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    },
                ),
                axios.get(
                    `/api/reports/order-type-sales?start_date=${startDate}&end_date=${endDate}`,
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    },
                ),
            ]);

            const freshSales = Array.isArray(sales.data.data) ? sales.data.data : [];
            const freshProducts = Array.isArray(products.data.data) ? products.data.data : [];
            const freshOrderTypes = Array.isArray(orderTypes.data.data) ? orderTypes.data.data : [];
            setSalesData(freshSales);
            setProductData(freshProducts);
            setOrderTypeData(freshOrderTypes);
            setPageCache(salesKey, freshSales);
            setPageCache(productKey, freshProducts);
            setPageCache(orderTypeKey, freshOrderTypes);
        } catch (requestError) {
            console.error("Failed to load reports:", requestError);
            setError("Tidak dapat memuat laporan penjualan.");
        } finally {
            setIsInitialLoading(false);
            setIsRefreshing(false);
        }
    };

    useEffect(() => {
        if (!token) {
            navigate("/login");
            return;
        }

        fetchReports();
    }, [token, navigate, startDate, endDate, period]);

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    const totalRevenue = salesData.reduce(
        (sum, item) => sum + (Number(item.total_revenue) || 0),
        0,
    );
    const totalOrders = salesData.reduce(
        (sum, item) => sum + (Number(item.orders_count) || 0),
        0,
    );

    const periodOptions = [
        { value: "daily", label: "Harian" },
        { value: "weekly", label: "Mingguan" },
        { value: "monthly", label: "Bulanan" },
    ];

    return (
        <div className="relative min-h-screen bg-stone-50 p-4 md:p-8 select-none font-sans">
            {/* Background Pola Titik Kaldu */}
            <div className="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(#e4d5b7_1.5px,transparent_1.5px)] [background-size:24px_24px]"></div>

            <div className="mx-auto max-w-6xl space-y-6 relative z-10">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl bg-white p-6 shadow-sm border border-stone-100">
                    <div>
                        <p className="text-xs font-mono font-bold tracking-widest text-amber-700 uppercase">
                            POS Mie Ayam Puput
                        </p>
                        <h1 className="mt-1 text-2xl md:text-3xl font-black tracking-tight text-stone-800">
                            Laporan Penjualan
                        </h1>
                    </div>

                    <Link
                        to="/"
                        className="inline-flex items-center justify-center rounded-xl bg-stone-800 px-4 py-2 font-mono text-xs font-bold text-amber-400 hover:bg-stone-900 border border-stone-700 shadow-sm transition"
                    >
                        ← Kembali ke Dashboard
                    </Link>
                </div>

                {/* Filter Periode */}
                <div className="rounded-2xl border border-stone-200 bg-[#FFFDF9] p-6 shadow-sm">
                    <h2 className="mb-4 text-xs font-mono font-bold tracking-wider text-stone-600 uppercase">
                        Pengaturan Filter Periode
                    </h2>
                    <div className="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                        {/* Dari Tanggal - Style Kertas Struk */}
                        <div>
                            <label className="mb-1 block text-xs font-mono text-stone-500">
                                Dari Tanggal
                            </label>
                            <input
                                type="date"
                                value={startDate}
                                onChange={(event) => setStartDate(event.target.value)}
                                className="w-full rounded-xl border border-dashed border-stone-300 bg-[#FFFDF9] px-3 py-2 text-xs font-mono text-stone-800 shadow-sm focus:border-amber-600 focus:bg-white focus:outline-none"
                            />
                        </div>

                        {/* Sampai Tanggal - Style Kertas Struk */}
                        <div>
                            <label className="mb-1 block text-xs font-mono text-stone-500">
                                Sampai Tanggal
                            </label>
                            <input
                                type="date"
                                value={endDate}
                                onChange={(event) => setEndDate(event.target.value)}
                                className="w-full rounded-xl border border-dashed border-stone-300 bg-[#FFFDF9] px-3 py-2 text-xs font-mono text-stone-800 shadow-sm focus:border-amber-600 focus:bg-white focus:outline-none"
                            />
                        </div>

                        {/* Satuan Periode - Custom Dropdown Style Kertas Struk */}
                        <div>
                            <label className="mb-1 block text-xs font-mono text-stone-500">
                                Satuan Periode
                            </label>
                            <PaperDropdown
                                value={period}
                                onChange={setPeriod}
                                options={periodOptions}
                            />
                        </div>

                        {/* Tombol Terapkan (Warna Awal) */}
                        <div className="flex items-end">
                            <button
                                type="button"
                                onClick={fetchReports}
                                disabled={isRefreshing}
                                className="w-full rounded-xl bg-amber-600 px-4 py-2 text-xs font-mono font-bold text-white hover:bg-amber-700 disabled:opacity-60 transition shadow-sm"
                            >
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>

                {error && (
                    <div className="rounded-xl border border-red-200 bg-red-50 p-4 font-mono text-xs text-red-600">
                        {error}
                    </div>
                )}
                {isRefreshing ? <div className="text-right text-[10px] font-mono text-stone-400">Memperbarui...</div> : null}

                {/* 2 Kartu Metrik Ringkasan */}
                <div className="grid gap-6 md:grid-cols-2">
                    <div className="bg-stone-800 p-2.5 pb-4 rounded-2xl shadow-xl border-2 border-stone-700 relative">
                        <div className="absolute -top-3 left-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
                        <div className="absolute -top-3 right-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
                        <div className="bg-[#FFFDF9] rounded-xl p-4 border border-amber-100 flex flex-col justify-between h-full">
                            <div className="flex items-center justify-between">
                                <h3 className="text-xs font-mono font-bold text-stone-400 uppercase tracking-wider">
                                    Total Pendapatan Terpilih
                                </h3>
                                <span className="p-1.5 bg-emerald-50 rounded-lg text-emerald-600 font-mono text-[10px] font-bold">
                                    REVENUE
                                </span>
                            </div>
                            <div className="mt-4">
                                <p className="text-2xl font-black text-stone-800 tracking-tight">
                                    {formatCurrency(totalRevenue)}
                                </p>
                                <span className="text-[10px] font-mono text-stone-400 mt-1 inline-block">
                                    Rentang: {startDate} s.d. {endDate}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="bg-stone-800 p-2.5 pb-4 rounded-2xl shadow-xl border-2 border-stone-700 relative">
                        <div className="absolute -top-3 left-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
                        <div className="absolute -top-3 right-1/4 w-1 h-3 bg-amber-800 rounded-full"></div>
                        <div className="bg-[#FFFDF9] rounded-xl p-4 border border-amber-100 flex flex-col justify-between h-full">
                            <div className="flex items-center justify-between">
                                <h3 className="text-xs font-mono font-bold text-stone-400 uppercase tracking-wider">
                                    Total Pesanan Selesai
                                </h3>
                                <span className="p-1.5 bg-amber-50 rounded-lg text-amber-700 font-mono text-[10px] font-bold">
                                    TRANSAKSI
                                </span>
                            </div>
                            <div className="mt-4">
                                <p className="text-2xl font-black text-stone-800 tracking-tight">
                                    {totalOrders.toLocaleString("id-ID")}{" "}
                                    <span className="text-xs font-normal text-stone-400 font-mono">Nota</span>
                                </p>
                                <span className="text-[10px] font-mono text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded mt-1 inline-block font-bold">
                                    Mode: {period.toUpperCase()}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Tabel Ringkasan Penjualan Per Periode */}
                <div className="rounded-2xl border border-stone-100 bg-white p-6 shadow-sm">
                    <div className="mb-4 flex items-center justify-between border-b border-dashed border-stone-200 pb-3">
                        <h2 className="text-xs font-mono font-bold tracking-wider text-stone-800 uppercase">
                            Ringkasan Penjualan Per Periode
                        </h2>
                        <span className="text-[10px] font-mono text-stone-400">
                            {salesData.length} Data Periode
                        </span>
                    </div>

                    {isInitialLoading ? (
                        <div className="animate-pulse space-y-3">
                            {[1, 2, 3, 4].map((item) => <div key={item} className="h-10 rounded bg-stone-100" />)}
                        </div>
                    ) : salesData.length === 0 ? (
                        <p className="font-mono text-xs text-stone-400">
                            Tidak ada data transaksi untuk rentang ini.
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full font-mono text-xs">
                                <thead className="border-b border-stone-200 bg-stone-50">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-bold text-stone-700 uppercase">
                                            Periode
                                        </th>
                                        <th className="px-4 py-2.5 text-right font-bold text-stone-700 uppercase">
                                            Jumlah Nota
                                        </th>
                                        <th className="px-4 py-2.5 text-right font-bold text-stone-700 uppercase">
                                            Total Omzet
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-stone-100">
                                    {salesData.map((row, idx) => (
                                        <tr key={idx} className="hover:bg-amber-50/40">
                                            <td className="px-4 py-2 text-stone-800">
                                                {row.period}
                                            </td>
                                            <td className="px-4 py-2 text-right text-stone-600">
                                                {row.orders_count} Nota
                                            </td>
                                            <td className="px-4 py-2 text-right font-bold text-stone-800">
                                                {formatCurrency(row.total_revenue)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>

                {/* Tabel Penjualan Per Produk */}
                <div className="rounded-2xl border border-stone-100 bg-white p-6 shadow-sm">
                    <div className="mb-4 flex items-center justify-between border-b border-dashed border-stone-200 pb-3">
                        <h2 className="text-xs font-mono font-bold tracking-wider text-stone-800 uppercase">
                            Penjualan Per Menu &amp; Porsi
                        </h2>
                        <span className="text-[10px] font-mono text-stone-400">
                            {productData.length} Menu Terjual
                        </span>
                    </div>

                    {productData.length === 0 ? (
                        <p className="font-mono text-xs text-stone-400">
                            Tidak ada penjualan produk pada periode ini.
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full font-mono text-xs">
                                <thead className="border-b border-stone-200 bg-stone-50">
                                    <tr>
                                        <th className="px-4 py-2.5 text-left font-bold text-stone-700 uppercase">
                                            Menu Makanan / Minuman
                                        </th>
                                        <th className="px-4 py-2.5 text-right font-bold text-stone-700 uppercase">
                                            Porsi Terjual
                                        </th>
                                        <th className="px-4 py-2.5 text-right font-bold text-stone-700 uppercase">
                                            Subtotal Omzet
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-stone-100">
                                    {productData.map((product) => (
                                        <tr key={product.product_id} className="hover:bg-amber-50/40">
                                            <td className="px-4 py-2 font-bold text-stone-800">
                                                {product.name}
                                            </td>
                                            <td className="px-4 py-2 text-right text-stone-600">
                                                {product.quantity_sold} Porsi
                                            </td>
                                            <td className="px-4 py-2 text-right font-bold text-amber-700">
                                                {formatCurrency(product.total_revenue)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>

                {/* Penjualan Per Tipe Order */}
                <div className="rounded-2xl border border-stone-200 bg-[#FFFDF9] p-6 shadow-sm">
                    <div className="mb-4 border-b border-dashed border-stone-200 pb-3">
                        <h2 className="text-xs font-mono font-bold tracking-wider text-stone-800 uppercase">
                            Penjualan Berdasarkan Tipe Order
                        </h2>
                    </div>

                    {orderTypeData.length === 0 ? (
                        <p className="font-mono text-xs text-stone-400">
                            Tidak ada data jenis pemesanan untuk periode ini.
                        </p>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2">
                            {orderTypeData.map((type) => (
                                <div
                                    key={type.type}
                                    className="rounded-xl border border-stone-200 bg-white p-4 shadow-sm"
                                >
                                    <div className="flex items-center justify-between">
                                        <p className="text-xs font-mono font-bold uppercase text-stone-500">
                                            {type.type}
                                        </p>
                                        <span className="text-[10px] font-mono text-stone-400 bg-stone-100 px-2 py-0.5 rounded">
                                            {type.count} Transaksi
                                        </span>
                                    </div>
                                    <p className="mt-3 text-2xl font-black text-stone-800">
                                        {formatCurrency(type.revenue)}
                                    </p>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

// Dropdown Bergaya Kertas Struk
function PaperDropdown({ value, onChange, options }) {
    const [open, setOpen] = useState(false);
    const dropdownRef = useRef(null);

    const selectedOption = options.find((opt) => opt.value === value) || options[0];

    useEffect(() => {
        function handleClickOutside(event) {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setOpen(false);
            }
        }
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    return (
        <div className="relative w-full" ref={dropdownRef}>
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className="flex w-full items-center justify-between rounded-xl border border-dashed border-stone-300 bg-[#FFFDF9] px-3 py-2 text-xs font-mono text-stone-800 shadow-sm transition hover:border-amber-600 focus:border-amber-600 focus:outline-none"
            >
                <span className="font-semibold">{selectedOption?.label}</span>
                <span className={`text-[9px] text-stone-400 transition-transform duration-200 ${open ? "rotate-180" : ""}`}>
                    ▼
                </span>
            </button>

            {open && (
                <div className="absolute left-0 top-full z-50 mt-1.5 w-full rounded-xl border border-dashed border-stone-300 bg-[#FFFDF9] py-1 shadow-md ring-1 ring-black/5">
                    {options.map((opt) => {
                        const isSelected = opt.value === value;
                        return (
                            <button
                                key={opt.value}
                                type="button"
                                onClick={() => {
                                    onChange(opt.value);
                                    setOpen(false);
                                }}
                                className={`flex w-full items-center justify-between px-3 py-1.5 text-left font-mono text-xs transition ${
                                    isSelected
                                        ? "bg-amber-100/60 font-bold text-amber-900"
                                        : "text-stone-700 hover:bg-stone-100/70"
                                }`}
                            >
                                <span>{opt.label}</span>
                                {isSelected && (
                                    <span className="text-[9px] text-amber-700">●</span>
                                )}
                            </button>
                        );
                    })}
                </div>
            )}
        </div>
    );
}

export default ReportPage;
