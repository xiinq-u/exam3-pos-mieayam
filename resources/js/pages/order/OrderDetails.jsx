import axios from "axios";
import { useEffect, useState } from "react";
import { Link, Navigate, useParams } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import { invalidateOrderPageCaches } from "../../services/pageCache";

const formatNumber = (value) =>
    Number(value || 0).toLocaleString("id-ID", { maximumFractionDigits: 0 });

const formatDate = (value) =>
    new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(value));

function OrderDetails() {
    const { orderId } = useParams();
    const token = getStoredToken();
    const headers = { Authorization: `Bearer ${token}` };
    const [order, setOrder] = useState(null);
    const [paymentMethod, setPaymentMethod] = useState("cash");
    const [paidAmount, setPaidAmount] = useState("");
    const [loading, setLoading] = useState(true);
    const [paying, setPaying] = useState(false);
    const [paymentSuccess, setPaymentSuccess] = useState(false);
    const [error, setError] = useState("");

    useEffect(() => {
        if (!token) {
            return;
        }

        const loadOrder = async () => {
            setLoading(true);
            setError("");

            try {
                const response = await axios.get(`/api/cashier/orders/${orderId}`, { headers });
                const loadedOrder = response.data.data;

                setOrder(loadedOrder);
                setPaymentMethod(loadedOrder.payment_method || "cash");
                setPaidAmount(String(loadedOrder.total || ""));
            } catch (requestError) {
                setError(requestError?.response?.data?.message || "Detail pesanan tidak dapat dimuat.");
            } finally {
                setLoading(false);
            }
        };

        loadOrder();
    }, [orderId, token]);

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    const total = Number(order?.total || 0);
    const paymentValue = paymentMethod === "qris" ? total : Number(paidAmount || 0);
    const changeAmount = Math.max(0, paymentValue - total);
    const isPaid = order?.payment_status === "paid";

    const payOrder = async (event) => {
        event.preventDefault();
        setPaying(true);
        setError("");

        try {
            const response = await axios.post(
                `/api/orders/${order.id}/pay`,
                {
                    payment_method: paymentMethod,
                    paid_amount: paymentValue,
                },
                { headers },
            );

            setOrder((currentOrder) => ({ ...currentOrder, ...response.data.data }));
            invalidateOrderPageCaches();
            setPaymentSuccess(true);
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Pembayaran gagal diproses.");
        } finally {
            setPaying(false);
        }
    };

    if (loading) {
        return <main className="mx-auto min-h-screen max-w-3xl px-4 py-16 text-center text-sm text-stone-400">Memuat detail pesanan...</main>;
    }

    if (!order) {
        return (
            <main className="mx-auto min-h-screen max-w-3xl px-4 py-16 text-center">
                <p className="mb-5 text-sm text-red-600">{error || "Pesanan tidak ditemukan."}</p>
                <Link to="/orders" className="font-bold text-red-600 hover:text-red-700">Kembali ke Antrean</Link>
            </main>
        );
    }

    return (
        <main className="mx-auto min-h-screen max-w-3xl px-4 py-8 sm:px-6">
            <div className="no-print mb-8">
                <div className="mb-4 flex flex-wrap gap-3">
                    <Link to="/orders" className="flex items-center gap-1 text-sm font-bold text-red-600 hover:text-red-700">
                        &larr; Kembali ke Antrean
                    </Link>
                    <Link to="/cashier" className="flex items-center gap-1 text-sm font-bold text-stone-500 hover:text-stone-900">
                        Kembali ke Kasir
                    </Link>
                </div>
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-extrabold text-stone-900">{order.order_number}</h1>
                        <p className="mt-1 text-sm text-stone-400">{formatDate(order.created_at)}</p>
                    </div>
                    <span className={`w-fit rounded-full px-3 py-1 text-xs font-black tracking-widest uppercase ${isPaid ? "bg-emerald-100 text-emerald-700" : "bg-amber-100 text-amber-700"}`}>
                        {isPaid ? "Lunas" : order.status}
                    </span>
                </div>
            </div>

            {paymentSuccess ? (
                <div className="no-print mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-sm font-black tracking-widest text-emerald-800 uppercase">Pembayaran Berhasil</p>
                            <p className="mt-1 text-xs text-emerald-700">Mau cetak struk sekarang?</p>
                        </div>
                        <div className="grid grid-cols-2 gap-2 sm:flex">
                            <button type="button" onClick={() => window.print()} className="rounded-xl bg-emerald-600 px-5 py-3 text-xs font-black tracking-widest text-white uppercase hover:bg-emerald-700">Cetak</button>
                            <Link to="/cashier" className="rounded-xl border border-emerald-200 bg-white px-5 py-3 text-center text-xs font-black tracking-widest text-emerald-700 uppercase hover:bg-emerald-100">Tidak Dulu</Link>
                        </div>
                    </div>
                </div>
            ) : null}

            {error ? <div className="no-print mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">{error}</div> : null}

            <section className="receipt-print-area relative overflow-hidden rounded-xl border border-stone-200 bg-white p-5 shadow-xl">
                <div className="text-center">
                    <h2 className="receipt-title text-xl font-black tracking-tight text-stone-950 uppercase">Mie Ayam Puput</h2>
                    <p className="receipt-subtitle mt-1 text-[10px] font-bold tracking-widest text-stone-500 uppercase">Struk Pembayaran</p>
                    <p className="receipt-subtitle mt-2 text-[10px] text-stone-500">Jl. Hj Manshur Rawa Mulya</p>
                </div>

                <hr className="receipt-divider my-5 border-stone-200" />

                <div className="space-y-1.5 text-xs">
                    <ReceiptRow label="No. Order:" value={order.order_number} />
                    <ReceiptRow label="Waktu:" value={formatDate(order.created_at)} />
                    <ReceiptRow label="Pembeli:" value={order.customer_name || "-"} />
                    <ReceiptRow label="Kasir:" value={order.user?.name || "Guest"} />
                    <ReceiptRow label="Tipe:" value={String(order.order_type || "-").replaceAll("_", " ")} uppercase />
                    <ReceiptRow label="Metode:" value={order.payment_method || paymentMethod} uppercase />
                </div>

                <hr className="receipt-divider my-5 border-stone-200" />

                <div className="space-y-3">
                    <div className="receipt-row flex justify-between text-[10px] font-black tracking-widest text-stone-500 uppercase">
                        <span>Menu</span>
                        <span>Subtotal</span>
                    </div>
                    {(order.items || []).map((item) => (
                        <div key={item.id} className="receipt-row receipt-item-row flex justify-between gap-4 text-xs">
                            <div className="min-w-0">
                                <p className="item-name font-bold text-stone-900">{item.product_name}</p>
                                <p className="text-xs text-stone-500">{item.quantity} x Rp{formatNumber(item.price)}</p>
                            </div>
                            <span className="shrink-0 font-black text-stone-900">Rp{formatNumber(item.subtotal)}</span>
                        </div>
                    ))}
                </div>

                <hr className="receipt-divider my-5 border-stone-200" />

                <div className="space-y-2">
                    <div className="receipt-row receipt-total-row flex items-center justify-between">
                        <span className="text-xs font-black text-stone-500 uppercase">Total:</span>
                        <span className="text-xl font-black text-stone-950">Rp{formatNumber(order.total)}</span>
                    </div>
                    {isPaid ? (
                        <>
                            <ReceiptRow label="Bayar:" value={`Rp${formatNumber(order.paid_amount)}`} />
                            <ReceiptRow label="Kembali:" value={`Rp${formatNumber(order.change_amount)}`} />
                        </>
                    ) : null}
                </div>

                {!isPaid ? (
                    <form onSubmit={payOrder} className="no-print mt-5 space-y-6 border-t border-stone-200 pt-5">
                        <div className="flex rounded-2xl bg-stone-200 p-1">
                            {["cash", "qris"].map((method) => (
                                <button key={method} type="button" onClick={() => setPaymentMethod(method)} className={`flex-1 rounded-xl py-3 text-xs font-black tracking-widest uppercase transition-all ${paymentMethod === method ? "bg-white text-red-600 shadow-sm" : "text-stone-500"}`}>
                                    {method === "cash" ? "Tunai" : "QRIS"}
                                </button>
                            ))}
                        </div>

                        <div className="space-y-2">
                            <label htmlFor="paid-amount" className="px-2 text-[10px] font-black tracking-widest text-stone-400 uppercase">Uang yang Dibayarkan</label>
                            <input id="paid-amount" type="number" value={paymentMethod === "qris" ? total : paidAmount} onChange={(event) => setPaidAmount(event.target.value)} min={total} disabled={paymentMethod === "qris"} required className="w-full rounded-2xl border-2 border-stone-200 bg-white px-6 py-4 text-xl font-black text-stone-900 outline-none transition-all focus:border-red-500 disabled:bg-stone-100" />
                        </div>

                        <div className="flex items-center justify-between rounded-2xl bg-red-600 px-6 py-4 text-white">
                            <span className="text-[10px] font-black tracking-widest uppercase opacity-70">Kembalian</span>
                            <span className="text-2xl font-black">Rp{formatNumber(changeAmount)}</span>
                        </div>

                        <button type="submit" disabled={paying} className="w-full rounded-2xl bg-stone-900 py-4 text-sm font-black tracking-[0.2em] text-white uppercase transition-all hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-60">
                            {paying ? "Memproses..." : "Bayar Sekarang"}
                        </button>
                    </form>
                ) : !paymentSuccess ? (
                    <button type="button" onClick={() => window.print()} className="no-print mt-5 w-full rounded-2xl bg-stone-900 py-4 text-sm font-black tracking-[0.2em] text-white uppercase transition-all hover:bg-red-600">
                        Cetak Struk
                    </button>
                ) : null}
            </section>
        </main>
    );
}

function ReceiptRow({ label, value, uppercase = false }) {
    return (
        <div className="receipt-row flex justify-between gap-4">
            <span className="text-stone-500">{label}</span>
            <span className={`text-right font-bold text-stone-900 ${uppercase ? "uppercase" : ""}`}>{value}</span>
        </div>
    );
}

export default OrderDetails;
