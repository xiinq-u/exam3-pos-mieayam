function Cart({
    items,
    total,
    customerName,
    orderType,
    orderNote,
    error,
    submitting,
    onCustomerNameChange,
    onOrderTypeChange,
    onOrderNoteChange,
    onRemoveItem,
    onCheckout,
}) {
    const now = new Date();
    const date = new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    }).format(now);
    const time = new Intl.DateTimeFormat("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    }).format(now);
    const formatNumber = (value) =>
        Number(value || 0).toLocaleString("id-ID", {
            maximumFractionDigits: 0,
        });

    return (
        <section className="relative border-x border-t border-stone-200 bg-white p-6 font-sans text-stone-800 shadow-xl">
            <div className="cart-torn-edge pointer-events-none absolute -bottom-2 left-0 z-10 h-2.5 w-full" />

            <div className="mb-6 border-b-2 border-stone-900 pb-4 text-center">
                <h2 className="text-2xl font-black tracking-tight text-stone-900 uppercase">
                    Mie Ayam Puput
                </h2>
                <div className="mt-4 flex justify-between text-[10px] font-bold text-stone-500 uppercase">
                    <span>{date}</span>
                    <span>{time}</span>
                </div>
            </div>

            {items.length === 0 ? (
                <div className="py-10 text-center text-xs text-stone-400 italic">
                    - Keranjang Kosong -
                </div>
            ) : (
                <>
                    <div className="mb-6 space-y-4">
                        {items.map((item) => (
                            <div key={item.product_id} className="flex items-center justify-between gap-4 text-sm">
                                <div className="flex min-w-0 flex-col">
                                    <span className="truncate font-bold text-stone-900">
                                        {item.product_name}
                                    </span>
                                    <span className="text-[10px] text-stone-500">
                                        {item.quantity} x {formatNumber(item.price)}
                                    </span>
                                </div>
                                <div className="flex shrink-0 items-center gap-3 font-bold">
                                    <span>{formatNumber(item.subtotal)}</span>
                                    <button
                                        type="button"
                                        onClick={() => onRemoveItem(item.product_id)}
                                        className="text-stone-300 transition-colors hover:text-red-500"
                                        aria-label={`Hapus ${item.product_name}`}
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="border-t-2 border-stone-900 pt-4">
                        <div className="mb-6 flex items-center justify-between">
                            <span className="text-sm font-black uppercase">Total</span>
                            <span className="text-2xl font-black text-stone-900">
                                Rp{formatNumber(total)}
                            </span>
                        </div>

                        <form className="space-y-3" onSubmit={onCheckout}>
                            <input
                                type="text"
                                value={customerName}
                                onChange={(event) => onCustomerNameChange(event.target.value)}
                                className="w-full rounded-lg border-none bg-stone-100 p-3 text-xs font-bold uppercase placeholder:text-stone-400 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                                placeholder="Nama Pembeli"
                                required
                            />
                            <select
                                value={orderType}
                                onChange={(event) => onOrderTypeChange(event.target.value)}
                                className="w-full rounded-lg border-none bg-stone-100 p-3 text-xs font-bold uppercase focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                            >
                                <option value="dine_in">Dine In</option>
                                <option value="take_away">Take Away</option>
                            </select>
                            <input
                                type="text"
                                value={orderNote}
                                onChange={(event) => onOrderNoteChange(event.target.value)}
                                className="w-full rounded-lg border-none bg-stone-100 p-3 text-xs font-bold placeholder:text-stone-400 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                                placeholder="Catatan pesanan (opsional)"
                            />

                            {error ? (
                                <p role="alert" className="text-center text-[10px] font-bold text-red-600">
                                    {error}
                                </p>
                            ) : null}

                            <button
                                type="submit"
                                disabled={submitting}
                                className="w-full rounded-xl bg-stone-900 py-4 text-xs font-black tracking-widest text-white uppercase shadow-lg transition-all hover:bg-red-600 disabled:cursor-wait disabled:opacity-60"
                            >
                                {submitting ? "Memproses Pesanan..." : "Buat Pesanan"}
                            </button>
                        </form>
                    </div>
                </>
            )}
        </section>
    );
}

export default Cart;
