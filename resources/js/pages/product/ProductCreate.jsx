import axios from "axios";
import { useEffect, useState } from "react";
import { Link, Navigate, useNavigate } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import { preloadProductImages } from "../../services/imagePreloader";
import {
    getPageCache,
    invalidateProductPageCaches,
    setPageCache,
} from "../../services/pageCache";

function ProductCreate() {
    const token = getStoredToken();
    const navigate = useNavigate();
    const cachedCategories = getPageCache("categories");
    const [categories, setCategories] = useState(Array.isArray(cachedCategories?.data) ? cachedCategories.data : []);
    const [form, setForm] = useState({
        name: "",
        category_id: "",
        price: "0",
        is_available: true,
    });
    const [image, setImage] = useState(null);
    const [previewUrl, setPreviewUrl] = useState("");
    const [errors, setErrors] = useState([]);
    const [loading, setLoading] = useState(!cachedCategories?.data);
    const [saving, setSaving] = useState(false);
    const headers = { Authorization: `Bearer ${token}` };

    useEffect(() => {
        if (!token) {
            return;
        }

        const loadCategories = async () => {
            try {
                const response = await axios.get("/api/categories", { headers });
                const loadedCategories = response.data || [];

                setCategories(loadedCategories);
                setPageCache("categories", loadedCategories);
                setForm((current) => ({
                    ...current,
                    category_id: current.category_id || String(loadedCategories[0]?.id || ""),
                }));
            } catch (requestError) {
                setErrors([requestError?.response?.data?.message || "Kategori tidak dapat dimuat."]);
            } finally {
                setLoading(false);
            }
        };

        loadCategories();
    }, [token]);

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

    const submitProduct = async (event) => {
        event.preventDefault();
        setSaving(true);
        setErrors([]);

        const payload = new FormData();
        payload.append("name", form.name);
        payload.append("category_id", form.category_id);
        payload.append("price", form.price);
        payload.append("is_available", form.is_available ? "1" : "0");

        if (image) {
            payload.append("image", image);
        }

        try {
            const response = await axios.post("/api/products", payload, { headers });
            invalidateProductPageCaches();
            void preloadProductImages([response.data.data]);
            navigate("/products", { replace: true });
        } catch (requestError) {
            const validationErrors = requestError?.response?.data?.errors;

            setErrors(
                validationErrors
                    ? Object.values(validationErrors).flat()
                    : [requestError?.response?.data?.message || "Produk gagal disimpan."],
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <main className="mx-auto min-h-screen max-w-3xl px-4 py-8 sm:p-6">
            <div className="mb-8">
                <h1 className="text-4xl font-extrabold tracking-tighter text-stone-900">Tambah Menu</h1>
                <p className="mt-2 text-stone-500">Input data produk baru untuk sistem Mie Ayam Puput.</p>
            </div>

            <form onSubmit={submitProduct} className="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                {errors.length > 0 ? (
                    <div className="mb-6 rounded-xl border border-red-100 bg-red-50 p-4 text-sm font-bold text-red-700">
                        <ul className="list-inside list-disc">
                            {errors.map((error) => <li key={error}>{error}</li>)}
                        </ul>
                    </div>
                ) : null}

                <div className="grid grid-cols-1 gap-8 md:grid-cols-12">
                    <div className="space-y-6 md:col-span-7">
                        <FormField label="Nama Produk">
                            <input type="text" value={form.name} onChange={(event) => updateField("name", event.target.value)} className="w-full rounded-xl border-2 border-stone-100 bg-stone-50 p-4 font-bold outline-none transition-all focus:border-red-500 focus:bg-white" placeholder="Contoh: Mie Ayam Bakso" required />
                        </FormField>

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <FormField label="Kategori">
                                <select value={form.category_id} onChange={(event) => updateField("category_id", event.target.value)} disabled={loading || categories.length === 0} className="w-full rounded-xl border-2 border-stone-100 bg-stone-50 p-4 font-bold outline-none focus:border-red-500" required>
                                    <option value="">Pilih kategori</option>
                                    {categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
                                </select>
                            </FormField>
                            <FormField label="Harga (Rp)">
                                <input type="number" value={form.price} onChange={(event) => updateField("price", event.target.value)} min="0" className="w-full rounded-xl border-2 border-stone-100 bg-stone-50 p-4 font-bold outline-none focus:border-red-500" required />
                            </FormField>
                        </div>
                    </div>

                    <div className="space-y-6 md:col-span-5">
                        <FormField label="Foto Produk">
                            <label className="relative flex aspect-square cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-stone-200 bg-stone-100 transition-colors hover:border-red-400">
                                {previewUrl ? <img src={previewUrl} loading="eager" decoding="async" fetchPriority="high" className="absolute inset-0 h-full w-full object-cover" alt="Pratinjau produk" /> : <span className="p-4 text-center text-xs text-stone-400">Klik untuk upload foto</span>}
                                <input type="file" onChange={(event) => setImage(event.target.files?.[0] || null)} className="absolute inset-0 cursor-pointer opacity-0" accept="image/*" />
                            </label>
                            <p className="mt-2 text-[10px] leading-relaxed text-stone-400">Disarankan WebP/JPG, rasio 1:1, sekitar 600 x 600 px, maksimal ideal 300 KB.</p>
                        </FormField>

                        <label className="flex cursor-pointer items-center justify-between rounded-xl border border-red-100 bg-red-50 p-4">
                            <span className="text-xs font-bold text-red-800">Tersedia untuk dijual</span>
                            <input type="checkbox" checked={form.is_available} onChange={(event) => updateField("is_available", event.target.checked)} className="toggle border-red-300 bg-red-100 text-red-600 checked:border-red-600 checked:bg-red-600" />
                        </label>
                    </div>
                </div>

                <div className="mt-8 flex gap-4 border-t border-stone-100 pt-6">
                    <Link to="/products" className="rounded-xl px-4 py-3 font-bold text-stone-500 transition-colors hover:text-stone-900 sm:px-8">Batal</Link>
                    <button type="submit" disabled={saving || loading || categories.length === 0} className="flex-1 rounded-xl bg-red-600 py-3 font-bold text-white shadow-lg shadow-red-200 transition-all hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60">
                        {saving ? "Menyimpan..." : "Simpan Produk"}
                    </button>
                </div>
            </form>
        </main>
    );
}

function FormField({ label, children }) {
    return (
        <div>
            <label className="mb-2 block text-[10px] font-bold tracking-widest text-stone-400 uppercase">{label}</label>
            {children}
        </div>
    );
}

export default ProductCreate;
