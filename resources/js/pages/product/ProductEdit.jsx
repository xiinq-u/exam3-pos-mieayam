import axios from "axios";
import { useEffect, useState } from "react";
import { Link, Navigate, useNavigate, useParams } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import { preloadProductImages } from "../../services/imagePreloader";
import {
    getPageCache,
    invalidateProductPageCaches,
    setPageCache,
} from "../../services/pageCache";
import { refreshProductCaches } from "../../services/prefetch";
import ProductImage from "../../components/ProductImage";

function ProductEdit() {
    const { productId } = useParams();
    const token = getStoredToken();
    const navigate = useNavigate();
    const headers = { Authorization: `Bearer ${token}` };
    const cachedCategories = getPageCache("categories");
    const cachedProduct = getPageCache(`product_detail_${productId}`);
    const cachedProducts = getPageCache("products");
    const initialProduct = cachedProduct?.data ?? (
        Array.isArray(cachedProducts?.data)
            ? cachedProducts.data.find((item) => String(item.id) === String(productId))
            : null
    );
    const [categories, setCategories] = useState(Array.isArray(cachedCategories?.data) ? cachedCategories.data : []);
    const [product, setProduct] = useState(initialProduct || null);
    const [form, setForm] = useState({
        name: initialProduct?.name || "",
        category_id: String(initialProduct?.category_id || ""),
        price: String(initialProduct?.price || ""),
        is_available: initialProduct ? Boolean(initialProduct.is_available) : true,
    });
    const [image, setImage] = useState(null);
    const [previewUrl, setPreviewUrl] = useState("");
    const [errors, setErrors] = useState([]);
    const [loading, setLoading] = useState(!initialProduct);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (!token) {
            return;
        }

        const loadData = async () => {
            try {
                const [productResponse, categoryResponse] = await Promise.all([
                    axios.get(`/api/products/${productId}`, { headers }),
                    axios.get("/api/categories", { headers }),
                ]);
                const loadedProduct = productResponse.data.product;

                setProduct(loadedProduct);
                setPageCache(`product_detail_${productId}`, loadedProduct);
                void preloadProductImages([loadedProduct]);
                const loadedCategories = Array.isArray(categoryResponse.data) ? categoryResponse.data : [];
                setCategories(loadedCategories);
                setPageCache("categories", loadedCategories);
                setForm({
                    name: loadedProduct.name || "",
                    category_id: String(loadedProduct.category_id || ""),
                    price: String(loadedProduct.price || "0"),
                    is_available: Boolean(loadedProduct.is_available),
                });
            } catch (requestError) {
                setErrors([requestError?.response?.data?.message || "Data produk tidak dapat dimuat."]);
            } finally {
                setLoading(false);
            }
        };

        loadData();
    }, [productId, token]);

    useEffect(() => {
        if (!image) {
            setPreviewUrl("");

            return undefined;
        }

        const objectUrl = URL.createObjectURL(image);
        setPreviewUrl(objectUrl);

        return () => URL.revokeObjectURL(objectUrl);
    }, [image]);

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    const updateField = (field, value) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const updateProduct = async (event) => {
        event.preventDefault();
        setSaving(true);
        setErrors([]);

        const payload = new FormData();
        payload.append("_method", "PATCH");
        payload.append("name", form.name);
        payload.append("category_id", form.category_id);
        payload.append("price", form.price);
        payload.append("is_available", form.is_available ? "1" : "0");

        if (image) {
            payload.append("image", image);
        }

        try {
            const response = await axios.post(`/api/products/${productId}`, payload, { headers });
            invalidateProductPageCaches();
            setPageCache(`product_detail_${productId}`, response.data.data);
            void preloadProductImages([response.data.data]);
            void refreshProductCaches(token);
            navigate(`/products/${productId}`, { replace: true });
        } catch (requestError) {
            const validationErrors = requestError?.response?.data?.errors;
            setErrors(
                validationErrors
                    ? Object.values(validationErrors).flat()
                    : [requestError?.response?.data?.message || "Produk gagal diperbarui."],
            );
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return <ProductEditSkeleton />;
    }

    if (!product) {
        return (
            <main className="mx-auto min-h-screen max-w-3xl p-8 text-center">
                <ErrorList errors={errors} />
                <Link to="/products" className="font-bold text-red-600">Kembali ke Produk</Link>
            </main>
        );
    }

    return (
        <main className="mx-auto min-h-screen max-w-3xl px-4 py-8 sm:p-6">
            <div className="mb-8 flex items-end justify-between gap-4">
                <div>
                    <h1 className="text-4xl font-extrabold tracking-tighter text-stone-900">Edit Menu</h1>
                    <p className="mt-2 text-stone-500">Update informasi detail produk ke database.</p>
                </div>
                <div className="rounded-full border border-stone-200 bg-stone-100 px-4 py-2 text-xs font-bold tracking-widest text-stone-600 uppercase">ID: {product.id}</div>
            </div>

            <form onSubmit={updateProduct} className="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <ErrorList errors={errors} />
                <div className="grid grid-cols-1 gap-8 md:grid-cols-12">
                    <div className="space-y-6 md:col-span-7">
                        <Field label="Nama Produk">
                            <input value={form.name} onChange={(event) => updateField("name", event.target.value)} className="w-full rounded-xl border-2 border-stone-100 bg-stone-50 p-4 font-bold outline-none transition-all focus:border-red-500 focus:bg-white" required />
                        </Field>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Field label="Kategori">
                                <select value={form.category_id} onChange={(event) => updateField("category_id", event.target.value)} className="w-full rounded-xl border-2 border-stone-100 bg-stone-50 p-4 font-bold outline-none focus:border-red-500" required>
                                    {categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
                                </select>
                            </Field>
                            <Field label="Harga (Rp)">
                                <input type="number" min="0" value={form.price} onChange={(event) => updateField("price", event.target.value)} className="w-full rounded-xl border-2 border-stone-100 bg-stone-50 p-4 font-bold outline-none focus:border-red-500" required />
                            </Field>
                        </div>
                    </div>

                    <div className="space-y-6 md:col-span-5">
                        <Field label="Foto Produk">
                            <label className="relative flex aspect-square cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-stone-200 bg-stone-100 transition-colors hover:border-red-400">
                                {previewUrl ? (
                                    <img src={previewUrl} loading="eager" decoding="async" fetchPriority="high" className="absolute inset-0 h-full w-full object-cover" alt={product.name} />
                                ) : (
                                    <ProductImage image={product.image} alt={product.name} index={0} className="absolute inset-0 h-full w-full object-cover" />
                                )}
                                <input type="file" onChange={(event) => setImage(event.target.files?.[0] || null)} className="absolute inset-0 cursor-pointer opacity-0" accept="image/*" />
                            </label>
                            <p className="mt-2 text-[10px] leading-relaxed text-stone-400">Disarankan WebP/JPG, rasio 1:1, sekitar 600 x 600 px, maksimal ideal 300 KB.</p>
                        </Field>
                        <label className="flex cursor-pointer items-center justify-between rounded-xl border border-red-100 bg-red-50 p-4">
                            <span className="text-xs font-bold text-red-800">Aktifkan Menu</span>
                            <input type="checkbox" checked={form.is_available} onChange={(event) => updateField("is_available", event.target.checked)} className="toggle border-red-300 bg-red-100 text-red-600 checked:border-red-600 checked:bg-red-600" />
                        </label>
                    </div>
                </div>

                <div className="mt-8 flex gap-4 border-t border-stone-100 pt-6">
                    <Link to="/products" className="rounded-xl px-4 py-3 font-bold text-stone-500 hover:text-stone-900 sm:px-8">Batal</Link>
                    <button type="submit" disabled={saving} className="flex-1 rounded-xl bg-red-600 py-3 font-bold text-white shadow-lg shadow-red-200 hover:bg-red-700 disabled:opacity-60">{saving ? "Memperbarui..." : "Update Data"}</button>
                </div>
            </form>
        </main>
    );
}

function ProductEditSkeleton() {
    return (
        <main className="mx-auto min-h-screen max-w-3xl px-4 py-8 sm:p-6">
            <div className="mb-8 h-20 w-2/3 animate-pulse rounded-2xl bg-stone-200" />
            <div className="grid animate-pulse gap-8 rounded-3xl border border-stone-200 bg-white p-8 md:grid-cols-12">
                <div className="space-y-6 md:col-span-7">
                    {[1, 2, 3].map((item) => <div key={item} className="h-16 rounded-xl bg-stone-100" />)}
                </div>
                <div className="aspect-square rounded-2xl bg-stone-200 md:col-span-5" />
            </div>
        </main>
    );
}

function Field({ label, children }) {
    return <div><label className="mb-2 block text-[10px] font-bold tracking-widest text-stone-400 uppercase">{label}</label>{children}</div>;
}

function ErrorList({ errors }) {
    return errors.length > 0 ? (
        <div className="mb-6 rounded-xl border border-red-100 bg-red-50 p-4 text-sm font-bold text-red-700">
            <ul className="list-inside list-disc">{errors.map((error) => <li key={error}>{error}</li>)}</ul>
        </div>
    ) : null;
}

export default ProductEdit;
