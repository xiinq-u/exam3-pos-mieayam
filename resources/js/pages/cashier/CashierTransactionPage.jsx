import { useEffect, useRef, useState } from "react";
import axios from "axios";
import { Link, Navigate, useNavigate } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import {
    getCachedProducts,
    preloadProductImages,
} from "../../services/imagePreloader";
import {
    getPageCache,
    invalidateOrderPageCaches,
    removePageCache,
    setPageCache,
} from "../../services/pageCache";
import formatCurrency from "../../utils/formatCurrency";
import ProductImage from "../../components/ProductImage";
import Cart from "../order/cart";
import OrderPayment from "../order/OrderPayment";

function CashierTransactionPage() {
    const navigate = useNavigate();
    const token = getStoredToken();
    const headers = { Authorization: `Bearer ${token}` };
    const toastTimeout = useRef(null);
    const cachedProducts = getPageCache("cashier_products");
    const cachedProductItems = getCachedProducts("cashier_products");
    const cachedCart = getPageCache("cashier_cart");
    const [products, setProducts] = useState(
        cachedProductItems,
    );
    const [productQuantities, setProductQuantities] = useState({});
    const [cart, setCart] = useState(
        Array.isArray(cachedCart?.data) ? cachedCart.data : [],
    );
    const [cartOpen, setCartOpen] = useState(false);
    const [toast, setToast] = useState("");
    const [customerName, setCustomerName] = useState("Budi");
    const [orderType, setOrderType] = useState("dine_in");
    const [orderNote, setOrderNote] = useState("");
    const [createdOrder, setCreatedOrder] = useState(null);
    const [paymentMethod, setPaymentMethod] = useState("cash");
    const [paidAmount, setPaidAmount] = useState("");
    const [loading, setLoading] = useState(!cachedProducts?.data);
    const [submitting, setSubmitting] = useState(false);
    const [paying, setPaying] = useState(false);
    const [error, setError] = useState("");

    useEffect(() => {
        if (!token) {
            navigate("/login");
            return;
        }

        const loadData = async () => {
            try {
                const [productsResponse, cartResponse] = await Promise.all([
                    axios.get("/api/cashier/products", { headers }),
                    axios.get("/api/cashier/cart", { headers }),
                ]);
                const freshProducts = Array.isArray(productsResponse.data)
                    ? productsResponse.data
                    : [];
                setProducts(freshProducts);
                setPageCache("cashier_products", freshProducts);
                void preloadProductImages(freshProducts);

                if (!cachedCart?.data) {
                    setCart(
                        Array.isArray(cartResponse.data.items)
                            ? cartResponse.data.items
                            : [],
                    );
                }
            } catch (requestError) {
                setError(
                    requestError?.response?.data?.message ||
                        "Data kasir tidak dapat dimuat.",
                );
            } finally {
                setLoading(false);
            }
        };

        loadData();
    }, [token, navigate]);

    useEffect(() => {
        if (cart.length > 0) {
            setPageCache("cashier_cart", cart);
        } else {
            removePageCache("cashier_cart");
        }
    }, [cart]);

    useEffect(
        () => () => {
            window.clearTimeout(toastTimeout.current);
        },
        [],
    );

    const showToast = (message) => {
        window.clearTimeout(toastTimeout.current);
        setToast(message);
        toastTimeout.current = window.setTimeout(() => setToast(""), 1800);
    };

    const addToCart = (productId) => {
        const product = products.find((item) => item.id === productId);
        const quantity = Math.max(
            1,
            Number(productQuantities[productId] || 1),
        );

        if (!product) {
            return;
        }

        setCreatedOrder(null);
        setError("");
        setCart((current) => {
            const existing = current.find(
                (item) => item.product_id === productId,
            );

            if (existing) {
                return current.map((item) =>
                    item.product_id === productId
                        ? {
                              ...item,
                              quantity: item.quantity + quantity,
                              subtotal:
                                  (item.quantity + quantity) * item.price,
                          }
                        : item,
                );
            }

            return [
                ...current,
                {
                    product_id: product.id,
                    product_name: product.name,
                    price: Number(product.price),
                    quantity,
                    subtotal: Number(product.price) * quantity,
                },
            ];
        });
        setProductQuantities((current) => ({ ...current, [productId]: 1 }));
        showToast("Pesanan ditambahkan ke tagihan");
    };

    const removeFromCart = (productId) => {
        setCart((current) =>
            current.filter((item) => item.product_id !== productId),
        );
        showToast("Pesanan dihapus dari tagihan");
    };

    const total = cart.reduce(
        (sum, item) => sum + Number(item.subtotal || 0),
        0,
    );
    const cartCount = cart.reduce(
        (sum, item) => sum + Number(item.quantity || 0),
        0,
    );

    const checkout = async (event) => {
        event.preventDefault();
        setSubmitting(true);
        setError("");

        try {
            const response = await axios.post(
                "/api/cashier/checkout",
                {
                    customer_name: customerName,
                    order_type: orderType,
                    order_note: orderNote,
                    items: cart.map((item) => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                    })),
                },
                { headers },
            );

            setCustomerName("Budi");
            setOrderType("dine_in");
            setOrderNote("");
            setCreatedOrder(response.data.order);
            setCart([]);
            removePageCache("cashier_cart");
            invalidateOrderPageCaches();
            setCartOpen(true);
            showToast(response.data.message || "Pesanan berhasil dibuat");
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message || "Checkout gagal.",
            );
        } finally {
            setSubmitting(false);
        }
    };

    const payOrder = async () => {
        if (!createdOrder) {
            return;
        }

        setPaying(true);
        setError("");

        try {
            const response = await axios.post(
                `/api/orders/${createdOrder.id}/pay`,
                {
                    payment_method: paymentMethod,
                    paid_amount:
                        paymentMethod === "qris"
                            ? Number(createdOrder.total)
                            : Number(paidAmount),
                },
                { headers },
            );
            setCreatedOrder(response.data.data);
            setPaidAmount("");
            invalidateOrderPageCaches();
            showToast("Pembayaran berhasil");
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Pembayaran gagal diproses.",
            );
        } finally {
            setPaying(false);
        }
    };

    const today = new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(new Date());

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    return (
        <div className="mx-auto min-h-screen max-w-[1500px] bg-[#FAFAFA] px-4 py-6 sm:p-8">
            <div
                className={`fixed top-20 left-1/2 z-[999] max-w-[calc(100vw-2rem)] -translate-x-1/2 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-center text-xs font-black tracking-widest text-emerald-700 uppercase shadow-2xl shadow-emerald-900/10 transition-all duration-200 ${
                    toast
                        ? "translate-y-0 opacity-100"
                        : "pointer-events-none translate-y-2 opacity-0"
                }`}
                role="status"
            >
                {toast}
            </div>

            {cartOpen ? (
                <button
                    type="button"
                    onClick={() => setCartOpen(false)}
                    className="fixed inset-0 z-[70] bg-stone-950/40 backdrop-blur-[2px] 2xl:hidden"
                    aria-label="Tutup struk"
                />
            ) : null}

            <div className="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 className="text-4xl font-extrabold tracking-tighter text-stone-900">
                        Kasir
                    </h1>
                    <p className="mt-1 text-sm font-medium text-stone-400">
                        Mie Ayam Puput - {today}
                    </p>
                </div>
                <div className="grid grid-cols-2 gap-2 sm:flex">
                    <Link to="/cashier" className="rounded-xl border border-stone-200 bg-white px-5 py-2.5 text-center font-bold text-stone-600 transition-all hover:border-stone-400">Dashboard Kasir</Link>
                    <Link to="/orders" className="rounded-xl bg-red-600 px-5 py-2.5 text-center font-bold text-white shadow-lg shadow-red-200 transition-all hover:bg-red-700">Pesanan</Link>
                </div>
            </div>

            <div className="grid grid-cols-1 items-start gap-8 2xl:grid-cols-[minmax(0,1fr)_420px]">
                <section className="grid grid-cols-1 items-start gap-5 sm:grid-cols-2 sm:gap-6 xl:grid-cols-3">
                    {loading ? (
                        Array.from({ length: 6 }, (_, index) => (
                            <div key={index} className="animate-pulse rounded-[2rem] border border-stone-100 bg-white p-4 sm:p-5">
                                <div className="mb-4 aspect-square rounded-[1.5rem] bg-stone-200" />
                                <div className="mb-2 h-4 w-2/3 rounded bg-stone-200" />
                                <div className="mb-4 h-6 w-1/2 rounded bg-stone-200" />
                                <div className="h-11 rounded-xl bg-stone-200" />
                            </div>
                        ))
                    ) : null}

                    {!loading && error && products.length === 0 ? (
                        <p className="col-span-full rounded-xl bg-red-50 p-4 text-center text-sm text-red-600">
                            {error}
                        </p>
                    ) : null}

                    {products.map((product, index) => {
                        return (
                            <article
                                key={product.id}
                                className="group min-w-0 rounded-[2rem] border border-stone-100 bg-white p-4 shadow-sm transition-all duration-500 hover:shadow-xl hover:shadow-stone-100 sm:p-5"
                            >
                                <div className="relative mb-4 aspect-[4/3] w-full overflow-hidden rounded-[1.5rem] border border-stone-100 bg-[#FFFDF9] sm:aspect-square">
                                    <ProductImage
                                        image={product.image}
                                        alt={product.name}
                                        index={index}
                                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    />
                                </div>

                                <h2 className="mb-1 truncate text-sm font-bold text-stone-900">
                                    {product.name}
                                </h2>
                                <p className="mb-4 text-lg font-black text-red-600">
                                    {formatCurrency(product.price)}
                                </p>

                                <div className="grid grid-cols-[5rem_minmax(0,1fr)] gap-2">
                                    <input
                                        type="number"
                                        min="1"
                                        value={productQuantities[product.id] || 1}
                                        onChange={(event) =>
                                            setProductQuantities((current) => ({
                                                ...current,
                                                [product.id]: Math.max(
                                                    1,
                                                    Number(event.target.value),
                                                ),
                                            }))
                                        }
                                        className="w-full rounded-xl border border-stone-100 bg-[#FFFDF9] text-center text-sm font-bold outline-none transition-all focus:ring-2 focus:ring-red-100"
                                        aria-label={`Jumlah ${product.name}`}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => addToCart(product.id)}
                                        className="min-h-11 rounded-xl bg-stone-900 text-[10px] font-black tracking-widest text-white uppercase transition-all hover:bg-red-600"
                                    >
                                        Tambah
                                    </button>
                                </div>
                            </article>
                        );
                    })}
                </section>

                <aside
                    className={`fixed inset-y-0 right-0 z-[80] w-full max-w-[420px] overflow-y-auto bg-white shadow-2xl transition-transform duration-300 ease-out sm:w-[420px] 2xl:sticky 2xl:top-24 2xl:z-auto 2xl:h-fit 2xl:max-h-[calc(100vh-7rem)] 2xl:w-auto 2xl:max-w-none 2xl:translate-x-0 2xl:overflow-visible 2xl:bg-transparent 2xl:shadow-none ${
                        cartOpen ? "translate-x-0" : "translate-x-full"
                    }`}
                >
                    <div className="sticky top-0 z-20 flex items-center justify-between border-b border-stone-200 bg-white px-5 py-4 2xl:hidden">
                        <div>
                            <p className="text-[10px] font-black tracking-widest text-stone-400 uppercase">
                                Struk Pesanan
                            </p>
                            <p className="text-sm font-black text-stone-900">
                                Mie Ayam Puput
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={() => setCartOpen(false)}
                            className="rounded-xl border border-stone-200 px-4 py-2 text-xs font-black tracking-widest text-stone-600 uppercase active:scale-[0.98]"
                        >
                            Tutup
                        </button>
                    </div>
                    <div className="space-y-5 pb-5 2xl:pb-0">
                        <Cart
                            items={cart}
                            total={total}
                            customerName={customerName}
                            orderType={orderType}
                            orderNote={orderNote}
                            error={error}
                            submitting={submitting}
                            onCustomerNameChange={setCustomerName}
                            onOrderTypeChange={setOrderType}
                            onOrderNoteChange={setOrderNote}
                            onRemoveItem={removeFromCart}
                            onCheckout={checkout}
                        />
                        <OrderPayment
                            order={createdOrder}
                            paymentMethod={paymentMethod}
                            paidAmount={paidAmount}
                            paying={paying}
                            onPaymentMethodChange={setPaymentMethod}
                            onPaidAmountChange={setPaidAmount}
                            onPay={payOrder}
                        />
                    </div>
                </aside>
            </div>

            <button
                type="button"
                onClick={() => setCartOpen(true)}
                className="fixed right-5 bottom-5 z-[60] flex items-center gap-3 rounded-2xl bg-stone-900 px-5 py-4 text-xs font-black tracking-widest text-white uppercase shadow-2xl shadow-stone-900/25 active:scale-[0.98] 2xl:hidden"
            >
                Struk
                <span className="grid min-h-6 min-w-6 place-items-center rounded-full bg-red-600 px-2 text-[10px] text-white">
                    {cartCount}
                </span>
            </button>
        </div>
    );
}

export default CashierTransactionPage;
