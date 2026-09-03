import axios from "axios";
import { useEffect, useState } from "react";
import { Link, Navigate, useParams } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import { preloadProductImages } from "../../services/imagePreloader";
import { getPageCache, setPageCache } from "../../services/pageCache";
import formatCurrency from "../../utils/formatCurrency";
import ProductImage from "../../components/ProductImage";

function ProductShow() {
    const { productId } = useParams();
    const token = getStoredToken();
    const cachedProduct = getPageCache(`product_detail_${productId}`);
    const cachedProducts = getPageCache("products");
    const initialProduct = cachedProduct?.data ?? (
        Array.isArray(cachedProducts?.data)
            ? cachedProducts.data.find((item) => String(item.id) === String(productId))
            : null
    );
    const [product, setProduct] = useState(initialProduct || null);
    const [loading, setLoading] = useState(!initialProduct);
    const [error, setError] = useState("");

    useEffect(() => {
        if (!token) {
            return;
        }

        axios.get(`/api/products/${productId}`, {
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => {
                setProduct(response.data.product);
                setPageCache(`product_detail_${productId}`, response.data.product);
                void preloadProductImages([response.data.product]);
            })
            .catch((requestError) => setError(requestError?.response?.data?.message || "Produk tidak dapat dimuat."))
            .finally(() => setLoading(false));
    }, [productId, token]);

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    if (loading) {
        return <ProductShowSkeleton />;
    }

    if (!product) {
        return (
            <main className="mx-auto min-h-screen max-w-3xl p-8 text-center">
                <p className="mb-4 text-red-600">{error || "Produk tidak ditemukan."}</p>
                <Link to="/products" className="font-bold text-red-600">Kembali</Link>
            </main>
        );
    }

    return (
        <main className="mx-auto min-h-screen max-w-3xl px-4 py-8 sm:px-6">
            <article className="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div className="h-64 bg-stone-100 sm:h-80">
                    <ProductImage image={product.image} alt={product.name} index={0} />
                </div>

                <div className="p-6 sm:p-8">
                    <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p className="mb-2 text-[10px] font-black tracking-widest text-red-500 uppercase">Detail Menu</p>
                            <h1 className="text-3xl font-black tracking-tight text-stone-900">{product.name}</h1>
                        </div>
                        <span className={`w-fit rounded-full px-3 py-1 text-xs font-black uppercase ${product.is_available ? "bg-emerald-100 text-emerald-700" : "bg-stone-200 text-stone-600"}`}>
                            {product.is_available ? "Tersedia" : "Tidak Tersedia"}
                        </span>
                    </div>

                    <dl className="grid gap-4 rounded-2xl bg-stone-50 p-5 sm:grid-cols-2">
                        <div>
                            <dt className="text-[10px] font-black tracking-widest text-stone-400 uppercase">Kategori</dt>
                            <dd className="mt-1 font-bold text-stone-800">{product.category?.name || "-"}</dd>
                        </div>
                        <div>
                            <dt className="text-[10px] font-black tracking-widest text-stone-400 uppercase">Harga</dt>
                            <dd className="mt-1 text-xl font-black text-red-600">{formatCurrency(product.price)}</dd>
                        </div>
                    </dl>

                    <div className="mt-6 flex gap-3">
                        <Link to={`/products/${product.id}/edit`} className="rounded-xl bg-red-600 px-6 py-3 font-bold text-white hover:bg-red-700">Edit</Link>
                        <Link to="/products" className="rounded-xl px-6 py-3 font-bold text-stone-500 hover:bg-stone-100 hover:text-stone-900">Kembali</Link>
                    </div>
                </div>
            </article>
        </main>
    );
}

function ProductShowSkeleton() {
    return (
        <main className="mx-auto min-h-screen max-w-3xl px-4 py-8 sm:px-6">
            <div className="animate-pulse overflow-hidden rounded-3xl border border-stone-200 bg-white">
                <div className="h-80 bg-stone-200" />
                <div className="space-y-5 p-8">
                    <div className="h-8 w-1/2 rounded bg-stone-200" />
                    <div className="h-24 rounded-2xl bg-stone-100" />
                </div>
            </div>
        </main>
    );
}

export default ProductShow;
