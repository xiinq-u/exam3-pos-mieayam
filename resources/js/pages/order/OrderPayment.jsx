import formatCurrency from "../../utils/formatCurrency";

function OrderPayment({
    order,
    paymentMethod,
    paidAmount,
    paying,
    onPaymentMethodChange,
    onPaidAmountChange,
    onPay,
}) {
    if (!order) {
        return null;
    }

    return (
        <section className="space-y-3 rounded-xl border border-sky-100 bg-sky-50 p-4 text-sm shadow-sm">
            <p className="font-bold text-sky-900">
                Antrean #{order.queue_number} — {order.payment_status === "paid" ? "Lunas" : "Menunggu pembayaran"}
            </p>

            {order.payment_status !== "paid" ? (
                <>
                    <select
                        value={paymentMethod}
                        onChange={(event) => onPaymentMethodChange(event.target.value)}
                        className="w-full rounded-xl border border-sky-200 bg-white p-2"
                    >
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                    {paymentMethod === "cash" ? (
                        <input
                            type="number"
                            min={order.total}
                            value={paidAmount}
                            onChange={(event) => onPaidAmountChange(event.target.value)}
                            placeholder="Uang diterima"
                            className="w-full rounded-xl border border-sky-200 bg-white p-2"
                        />
                    ) : null}
                    <button
                        type="button"
                        onClick={onPay}
                        disabled={paying}
                        className="w-full rounded-xl bg-sky-600 px-4 py-2 font-medium text-white disabled:cursor-wait disabled:opacity-60"
                    >
                        {paying ? "Memproses..." : "Bayar"}
                    </button>
                </>
            ) : (
                <>
                    <p>Kembalian: {formatCurrency(order.change_amount)}</p>
                    <button
                        type="button"
                        onClick={() => window.print()}
                        className="w-full rounded-xl border border-sky-600 px-4 py-2 font-medium text-sky-700"
                    >
                        Cetak Ulang Struk
                    </button>
                </>
            )}
        </section>
    );
}

export default OrderPayment;
