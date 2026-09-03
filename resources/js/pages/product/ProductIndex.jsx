import { useEffect, useRef, useState } from "react";
import axios from "axios";
import { Link, Navigate } from "react-router-dom";
import { getStoredToken } from "../../services/auth";
import {
    getCachedProducts,
    preloadProductImages,
} from "../../services/imagePreloader";
import {
    getPageCache,
    invalidateProductPageCaches,
    removePageCache,
    setPageCache,
} from "../../services/pageCache";
import formatCurrency from "../../utils/formatCurrency";
import ProductImage from "../../components/ProductImage";
import { refreshCashierProductCache } from "../../services/prefetch";

function ProductIndex() {
    const token = getStoredToken();
    const cachedProducts = getPageCache("products");
    const cachedProductItems = getCachedProducts("products");
    const cachedCategories = getPageCache("categories");
    const cachedMaterials = getPageCache("materials");
    const [products, setProducts] = useState(cachedProductItems);
    const [categories, setCategories] = useState(Array.isArray(cachedCategories?.data) ? cachedCategories.data : []);
    const [materials, setMaterials] = useState(Array.isArray(cachedMaterials?.data) ? cachedMaterials.data : []);
    const [categoryName, setCategoryName] = useState("");
    const [recipeProductId, setRecipeProductId] = useState("");
    const [recipeMaterials, setRecipeMaterials] = useState([
        { material_id: "", quantity_per_unit: "" },
    ]);
    const [error, setError] = useState("");
    const [isInitialLoading, setIsInitialLoading] = useState(!cachedProducts?.data);
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [activeTab, setActiveTab] = useState("menu"); // "menu" | "category" | "recipe"

    const headers = { Authorization: `Bearer ${token}` };

    const loadData = async () => {
        if (products.length > 0 || getPageCache("products")?.data) {
            setIsInitialLoading(false);
            setIsRefreshing(true);
        } else {
            setIsInitialLoading(true);
        }

        try {
            const [productResponse, categoryResponse, materialResponse] =
                await Promise.all([
                    axios.get("/api/products", { headers }),
                    axios.get("/api/categories", { headers }),
                    axios.get("/api/materials", { headers }),
                ]);
            const freshProducts = Array.isArray(productResponse.data) ? productResponse.data : [];
            const freshCategories = Array.isArray(categoryResponse.data) ? categoryResponse.data : [];
            const freshMaterials = Array.isArray(materialResponse.data) ? materialResponse.data : [];
            setProducts(freshProducts);
            setCategories(freshCategories);
            setMaterials(freshMaterials);
            setPageCache("products", freshProducts);
            freshProducts.forEach((product) => setPageCache(`product_detail_${product.id}`, product));
            void preloadProductImages(freshProducts);
            void refreshCashierProductCache(token);
            setPageCache("categories", freshCategories);
            setPageCache("materials", freshMaterials);
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Data produk tidak dapat dimuat.",
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
    }, [token]);

    const addCategory = async (event) => {
        event.preventDefault();
        try {
            await axios.post(
                "/api/categories",
                { name: categoryName },
                { headers },
            );
            setCategoryName("");
            removePageCache("categories");
            await loadData();
            setActiveTab("menu");
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Kategori gagal disimpan.",
            );
        }
    };

    const toggleProduct = async (item) => {
        try {
            await axios.patch(
                `/api/products/${item.id}`,
                { is_available: !item.is_available },
                { headers },
            );
            invalidateProductPageCaches();
            await loadData();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Gagal mengubah status ketersediaan produk.",
            );
        }
    };

    const deleteProduct = async (item) => {
        if (!window.confirm(`Yakin ingin menghapus menu "${item.name}"?`)) return;
        try {
            await axios.delete(`/api/products/${item.id}`, { headers });
            invalidateProductPageCaches();
            await loadData();
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Gagal menghapus produk.",
            );
        }
    };

    const saveRecipe = async (event) => {
        event.preventDefault();
        try {
            await axios.put(
                `/api/products/${recipeProductId}/recipe`,
                {
                    materials: recipeMaterials.map((item) => ({
                        material_id: Number(item.material_id),
                        quantity_per_unit: Number(item.quantity_per_unit),
                    })),
                },
                { headers },
            );
            setRecipeMaterials([{ material_id: "", quantity_per_unit: "" }]);
            setRecipeProductId("");
            removePageCache("products");
            removePageCache("materials");
            setActiveTab("menu");
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Resep gagal disimpan.",
            );
        }
    };

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    const productOptions = products.map((item) => ({
        value: String(item.id),
        label: item.name,
    }));

    const materialOptions = materials.map((mat) => ({
        value: String(mat.id),
        label: `${mat.name} (${mat.unit})`,
    }));

    return (
        <div className="relative min-h-screen bg-stone-50 p-4 md:p-8 select-none font-sans">
            {/* Background Pola Titik Kaldu */}
            <div className="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(#e4d5b7_1.5px,transparent_1.5px)] [background-size:24px_24px]"></div>

            <div className="max-w-6xl mx-auto space-y-8 relative z-10 px-1">
                {/* Header Persis Blade */}
                <div className="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-end">
                    <div>
                        <h2 className="text-[10px] font-black text-red-600 tracking-[0.3em] uppercase">
                            Mie Ayam Puput
                        </h2>
                        <h1 className="text-4xl sm:text-5xl font-black text-stone-900 tracking-tighter mt-1">
                            Daftar Menu
                        </h1>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            onClick={() => setActiveTab(activeTab === "category" ? "menu" : "category")}
                            className={`px-4 py-3.5 rounded-2xl font-black uppercase text-xs tracking-wider transition-all border ${
                                activeTab === "category"
                                    ? "bg-amber-600 border-amber-600 text-white"
                                    : "bg-white border-stone-200 text-stone-700 hover:bg-stone-100"
                            }`}
                        >
                            + Kategori
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveTab(activeTab === "recipe" ? "menu" : "recipe")}
                            className={`px-4 py-3.5 rounded-2xl font-black uppercase text-xs tracking-wider transition-all border ${
                                activeTab === "recipe"
                                    ? "bg-amber-600 border-amber-600 text-white"
                                    : "bg-white border-stone-200 text-stone-700 hover:bg-stone-100"
                            }`}
                        >
                            Atur Resep
                        </button>
                        <Link
                            to="/products/create"
                            className="w-full sm:w-auto text-center bg-stone-900 hover:bg-red-600 text-white px-8 py-4 rounded-2xl font-black uppercase text-xs tracking-widest transition-all shadow-xl shadow-stone-200"
                        >
                            + Tambah Menu
                        </Link>
                    </div>
                </div>

                {error && (
                    <div className="rounded-2xl border border-red-200 bg-red-50 p-4 font-mono text-xs text-red-600">
                        {error}
                    </div>
                )}
                {isRefreshing ? <div className="text-right text-[10px] font-mono text-stone-400">Memperbarui...</div> : null}

                {/* Form Buat Kategori Baru */}
                {activeTab === "category" && (
                    <div className="rounded-2xl border border-dashed border-stone-300 bg-[#FFFDF9] p-6 shadow-sm">
                        <form onSubmit={addCategory} className="space-y-3">
                            <h2 className="text-xs font-mono font-bold tracking-wider text-stone-700 uppercase">
                                Tambah Kategori Baru
                            </h2>
                            <div className="flex flex-col sm:flex-row gap-2">
                                <input
                                    required
                                    value={categoryName}
                                    onChange={(event) => setCategoryName(event.target.value)}
                                    placeholder="Contoh: Mie Kuah, Minuman Dingin, Topping"
                                    className="flex-1 rounded-xl border border-dashed border-stone-300 bg-white px-4 py-2 text-xs font-mono text-stone-800 focus:border-amber-600 focus:outline-none"
                                />
                                <button className="rounded-xl bg-stone-900 px-6 py-2 text-xs font-mono font-bold text-white hover:bg-red-600 transition">
                                    Simpan Kategori
                                </button>
                            </div>
                        </form>
                    </div>
                )}

                {/* Form Resep Menu dengan PaperDropdown Bertema */}
                {activeTab === "recipe" && (
                    <div className="rounded-2xl border border-dashed border-stone-300 bg-[#FFFDF9] p-6 shadow-sm">
                        <form onSubmit={saveRecipe} className="space-y-4">
                            <h2 className="text-xs font-mono font-bold tracking-wider text-stone-700 uppercase">
                                Atur Komposisi Bahan Resep Menu
                            </h2>
                            <div>
                                <label className="mb-1 block text-xs font-mono text-stone-500">
                                    Pilih Menu Produk
                                </label>
                                <PaperDropdown
                                    value={recipeProductId}
                                    placeholder="-- Pilih Produk --"
                                    options={productOptions}
                                    onChange={(val) => setRecipeProductId(val)}
                                />
                            </div>

                            <div className="space-y-2">
                                <label className="block text-xs font-mono text-stone-500">
                                    Daftar Bahan Baku
                                </label>
                                {recipeMaterials.map((item, index) => (
                                    <div key={index} className="flex gap-2 items-center">
                                        <div className="flex-1">
                                            <PaperDropdown
                                                value={String(item.material_id)}
                                                placeholder="Pilih Bahan Baku"
                                                options={materialOptions}
                                                onChange={(val) =>
                                                    setRecipeMaterials(
                                                        recipeMaterials.map((row, rowIndex) =>
                                                            rowIndex === index
                                                                ? { ...row, material_id: val }
                                                                : row,
                                                        ),
                                                    )
                                                }
                                            />
                                        </div>
                                        <input
                                            required
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            value={item.quantity_per_unit}
                                            onChange={(event) =>
                                                setRecipeMaterials(
                                                    recipeMaterials.map((row, rowIndex) =>
                                                        rowIndex === index
                                                            ? { ...row, quantity_per_unit: event.target.value }
                                                            : row,
                                                    ),
                                                )
                                            }
                                            placeholder="Takaran"
                                            className="w-32 rounded-xl border border-dashed border-stone-300 bg-[#FFFDF9] px-3 py-2 text-xs font-mono text-stone-800 focus:border-amber-600 focus:outline-none shadow-sm"
                                        />
                                    </div>
                                ))}
                            </div>

                            <div className="flex gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={() =>
                                        setRecipeMaterials([
                                            ...recipeMaterials,
                                            { material_id: "", quantity_per_unit: "" },
                                        ])
                                    }
                                    className="rounded-xl border border-dashed border-stone-300 bg-white px-4 py-2 text-xs font-mono text-stone-700 hover:bg-stone-100"
                                >
                                    + Tambah Baris Bahan
                                </button>
                                <button className="rounded-xl bg-stone-900 px-6 py-2 text-xs font-mono font-bold text-white hover:bg-red-600 transition">
                                    Simpan Resep
                                </button>
                            </div>
                        </form>
                    </div>
                )}

                {/* GRID DAFTAR MENU 1:1 DENGAN TAMPILAN BLADE */}
                {isInitialLoading ? (
                    <div className="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                        {[1, 2, 3, 4, 5, 6].map((item) => (
                            <div key={item} className="animate-pulse rounded-[2rem] border border-stone-100 bg-white p-3">
                                <div className="mb-4 aspect-square rounded-[1.5rem] bg-stone-200" />
                                <div className="mx-2 mb-3 h-4 w-2/3 rounded bg-stone-200" />
                                <div className="mx-2 h-12 rounded-xl bg-stone-200" />
                            </div>
                        ))}
                    </div>
                ) : products.length === 0 ? (
                    <div className="rounded-3xl border border-dashed border-stone-300 bg-[#FFFDF9] p-12 text-center">
                        <p className="font-mono text-sm text-stone-400">
                            Belum ada menu produk yang ditambahkan.
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-6 items-start">
                        {products.map((p, index) => (
                            <div
                                key={p.id}
                                className="bg-white border border-stone-100 p-3 rounded-[2rem] shadow-sm hover:shadow-xl hover:shadow-stone-100 transition-all duration-500 group min-w-0"
                            >
                                {/* Wadah Foto Produk */}
                                <div className="relative w-full aspect-[4/3] sm:aspect-square bg-[#FFFDF9] rounded-[1.5rem] overflow-hidden mb-4 border border-stone-100">
                                    <ProductImage
                                        image={p.image}
                                        alt={p.name}
                                        index={index}
                                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    />

                                    {/* Badge Status Ketersediaan */}
                                    <div className="absolute top-4 right-4">
                                        <button
                                            type="button"
                                            onClick={() => toggleProduct(p)}
                                            title="Klik untuk ubah ketersediaan"
                                            className={`px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest transition-transform active:scale-95 shadow-sm ${
                                                p.is_available
                                                    ? "bg-emerald-500 text-white hover:bg-emerald-600"
                                                    : "bg-red-500 text-white hover:bg-red-600"
                                            }`}
                                        >
                                            {p.is_available ? "Ready" : "Habis"}
                                        </button>
                                    </div>
                                </div>

                                {/* Deskripsi & Aksi Produk */}
                                <div className="px-2 pb-2">
                                    <p className="text-[9px] font-black text-stone-400 uppercase tracking-[0.2em] mb-1">
                                        {p.category?.name ?? "Menu"}
                                    </p>
                                    <h3 className="text-md font-black text-stone-900 mb-4 truncate">
                                        {p.name}
                                    </h3>

                                    <div className="flex justify-between items-center gap-3 bg-[#FFFDF9] p-3 rounded-xl border border-stone-100">
                                        <span className="font-black text-stone-900 text-sm truncate">
                                            {formatCurrency(p.price)}
                                        </span>

                                        <div className="flex gap-1 shrink-0">
                                            {/* Tombol Edit */}
                                            <Link
                                                to={`/products/${p.id}/edit`}
                                                className="p-2 text-stone-400 hover:text-stone-900 transition-colors rounded-lg hover:bg-stone-100"
                                                title="Edit Menu"
                                            >
                                                <svg
                                                    className="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth="2.5"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                    />
                                                </svg>
                                            </Link>

                                            {/* Tombol Hapus */}
                                            <button
                                                type="button"
                                                onClick={() => deleteProduct(p)}
                                                className="p-2 text-stone-400 hover:text-red-600 transition-colors rounded-lg hover:bg-red-50"
                                                title="Hapus Menu"
                                            >
                                                <svg
                                                    className="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth="2.5"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}

// Subkomponen Dropdown Kertas Struk
function PaperDropdown({ value, onChange, options, placeholder = "Pilih..." }) {
    const [open, setOpen] = useState(false);
    const dropdownRef = useRef(null);

    const selectedOption = options.find((opt) => String(opt.value) === String(value));

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
                <span className={`truncate font-semibold ${!selectedOption ? "text-stone-400" : ""}`}>
                    {selectedOption ? selectedOption.label : placeholder}
                </span>
                <span className={`text-[9px] text-stone-400 transition-transform duration-200 ${open ? "rotate-180" : ""}`}>
                    ▼
                </span>
            </button>

            {open && (
                <div className="absolute left-0 top-full z-50 mt-1.5 max-h-52 w-full overflow-y-auto rounded-xl border border-dashed border-stone-300 bg-[#FFFDF9] py-1 shadow-lg ring-1 ring-black/5">
                    {options.length === 0 ? (
                        <div className="px-3 py-2 text-center text-xs font-mono text-stone-400">
                            Tidak ada pilihan
                        </div>
                    ) : (
                        options.map((opt) => {
                            const isSelected = String(opt.value) === String(value);
                            return (
                                <button
                                    key={opt.value}
                                    type="button"
                                    onClick={() => {
                                        onChange(opt.value);
                                        setOpen(false);
                                    }}
                                    className={`flex w-full items-center justify-between px-3 py-2 text-left font-mono text-xs transition ${
                                        isSelected
                                            ? "bg-amber-100/60 font-bold text-amber-900"
                                            : "text-stone-700 hover:bg-stone-100/70"
                                    }`}
                                >
                                    <span className="truncate">{opt.label}</span>
                                    {isSelected && (
                                        <span className="text-[9px] text-amber-700">●</span>
                                    )}
                                </button>
                            );
                        })
                    )}
                </div>
            )}
        </div>
    );
}

export default ProductIndex;
