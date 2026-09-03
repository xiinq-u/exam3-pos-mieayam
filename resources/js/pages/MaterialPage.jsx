import { useEffect, useState } from "react";
import axios from "axios";
import { Link } from "react-router-dom";
import { getStoredToken, getStoredUser } from "../services/auth";
import { getPageCache, invalidateMaterialCaches, setPageCache } from "../services/pageCache";
import { getRoleHomePath } from "../services/roleRedirect";
import formatCurrency from "../utils/formatCurrency";

function MaterialPage() {
    const token = getStoredToken();
    const user = getStoredUser();
    const isOwner = user?.role === "owner";
    const isKitchen = user?.role === "kitchen";
    const headers = { Authorization: `Bearer ${token}` };
    const cachedMaterials = getPageCache("materials");
    const [materials, setMaterials] = useState(cachedMaterials?.data ?? []);
    const [inventoryValue, setInventoryValue] = useState(0);
    const [loading, setLoading] = useState(!cachedMaterials?.data);
    const [refreshing, setRefreshing] = useState(Boolean(cachedMaterials?.data));
    const [error, setError] = useState("");
    const [adjustments, setAdjustments] = useState({});
    const [edits, setEdits] = useState({});
    const [selectedMaterial, setSelectedMaterial] = useState(null);
    const [movements, setMovements] = useState([]);

    const fetchMaterials = async () => {
        try {
            const requests = [axios.get("/api/materials", { headers })];

            if (isOwner) {
                requests.push(axios.get("/api/reports/inventory", { headers }));
            }

            const [materialsResponse, inventoryResponse] = await Promise.all(requests);
            const freshMaterials = Array.isArray(materialsResponse.data) ? materialsResponse.data : [];

            setMaterials(freshMaterials);
            setPageCache("materials", freshMaterials);
            setInventoryValue(inventoryResponse?.data?.total_inventory_value ?? 0);
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Data bahan baku tidak dapat dimuat.");
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        if (token) {
            void fetchMaterials();
        }
    }, [token, user?.role]);

    const updateAdjustment = (materialId, field, value) => {
        setAdjustments((current) => ({ ...current, [materialId]: { ...current[materialId], [field]: value } }));
    };

    const updateEdit = (material, field, value) => {
        setEdits((current) => ({
            ...current,
            [material.id]: {
                minimum_stock: current[material.id]?.minimum_stock ?? material.minimum_stock,
                purchase_price: current[material.id]?.purchase_price ?? material.purchase_price,
                [field]: value,
            },
        }));
    };

    const handleAdjust = async (material) => {
        const adjustment = adjustments[material.id] ?? {};
        const type = adjustment.type ?? (isKitchen ? "damaged" : "in");
        const quantity = Number(adjustment.quantity ?? 0);

        if (quantity < 1) {
            setError("Jumlah perubahan stok minimal 1.");
            return;
        }

        try {
            await axios.post(`/api/materials/${material.id}/adjust`, { type, quantity, note: adjustment.note || null }, { headers });
            setAdjustments((current) => ({ ...current, [material.id]: {} }));
            invalidateMaterialCaches();
            await fetchMaterials();
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Stok tidak dapat diperbarui.");
        }
    };

    const saveMaterial = async (material) => {
        const edit = edits[material.id] ?? {};

        try {
            await axios.patch(`/api/materials/${material.id}`, {
                minimum_stock: Number(edit.minimum_stock ?? material.minimum_stock),
                purchase_price: Number(edit.purchase_price ?? material.purchase_price),
            }, { headers });
            invalidateMaterialCaches();
            await fetchMaterials();
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Bahan tidak dapat diperbarui.");
        }
    };

    const deactivateMaterial = async (material) => {
        if (!window.confirm(`Nonaktifkan ${material.name}?`)) {
            return;
        }

        try {
            await axios.delete(`/api/materials/${material.id}`, { headers });
            invalidateMaterialCaches();
            await fetchMaterials();
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Bahan tidak dapat dinonaktifkan.");
        }
    };

    const loadMovements = async (material) => {
        try {
            const response = await axios.get(`/api/materials/${material.id}`, { headers });
            setSelectedMaterial(material);
            setMovements(response.data?.movements ?? []);
        } catch (requestError) {
            setError(requestError?.response?.data?.message || "Riwayat stok tidak dapat dimuat.");
        }
    };

    if (!isOwner && !isKitchen) {
        return null;
    }

    const lowStock = materials.filter((material) => Number(material.stock) <= Number(material.minimum_stock));

    return (
        <main className="min-h-screen bg-stone-50 px-4 py-8 sm:px-8">
            <div className="mx-auto max-w-[1400px] space-y-6">
                <header className="flex flex-wrap items-end justify-between gap-4"><div><p className="text-xs font-black tracking-[0.2em] text-red-600 uppercase">{isOwner ? "Persediaan Usaha" : "Kebutuhan Dapur"}</p><h1 className="text-4xl font-black text-stone-900">Bahan Baku</h1><p className="mt-1 text-sm text-stone-500">{isOwner ? "Kelola stok, batas minimum, dan harga beli." : "Pantau stok dan catat bahan rusak atau kedaluwarsa."}</p></div><div className="text-right"><Link to={getRoleHomePath(user.role)} className="rounded-xl bg-stone-900 px-5 py-3 text-xs font-black text-white uppercase">Dashboard</Link>{refreshing ? <p className="mt-2 text-xs text-stone-400">Memperbarui...</p> : null}</div></header>
                {isOwner ? <div className="rounded-2xl border border-sky-200 bg-sky-50 p-5"><p className="text-[10px] font-black tracking-widest text-sky-600 uppercase">Total Nilai Persediaan</p><p className="mt-2 text-2xl font-black text-sky-900">{formatCurrency(inventoryValue)}</p></div> : null}
                <div className="rounded-2xl border border-amber-200 bg-amber-50 p-5"><h2 className="font-black text-amber-900">Peringatan Stok</h2><div className="mt-3 flex flex-wrap gap-2">{lowStock.length ? lowStock.map((material) => <span key={material.id} className="rounded-full bg-white px-3 py-1 text-xs font-bold text-amber-800">{material.name}: {material.stock} {material.unit}</span>) : <p className="text-sm text-amber-700">Semua bahan dalam kondisi aman.</p>}</div></div>
                {error ? <div className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</div> : null}

                {selectedMaterial ? <div className="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><div className="flex justify-between"><h2 className="font-black text-stone-900">Riwayat {selectedMaterial.name}</h2><button onClick={() => setSelectedMaterial(null)} className="text-sm font-bold text-red-600">Tutup</button></div><div className="mt-4 space-y-2">{movements.length ? movements.map((movement) => <div key={movement.id} className="rounded-xl bg-stone-50 p-3 text-sm"><span className="font-bold uppercase">{movement.loss_reason || movement.type}</span> · {movement.quantity} {selectedMaterial.unit} · {movement.note || "-"}<p className="mt-1 text-xs text-stone-400">{new Date(movement.created_at).toLocaleString("id-ID")}</p></div>) : <p className="text-sm text-stone-400">Belum ada riwayat.</p>}</div></div> : null}

                {loading ? <div className="h-72 animate-pulse rounded-2xl bg-stone-200" /> : <div className="space-y-4">{materials.map((material) => {
                    const status = Number(material.stock) === 0 ? "Habis" : Number(material.stock) <= Number(material.minimum_stock) ? "Menipis" : "Aman";
                    const adjustment = adjustments[material.id] ?? {};
                    const edit = edits[material.id] ?? {};

                    return <article key={material.id} className="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><div className="flex flex-wrap justify-between gap-3"><div><h2 className="text-xl font-black text-stone-900">{material.name}</h2><p className="text-xs text-stone-500">SKU {material.sku} · diperbarui {new Date(material.updated_at).toLocaleString("id-ID")}</p></div><span className={`h-fit rounded-full px-3 py-1 text-[10px] font-black uppercase ${status === "Aman" ? "bg-emerald-100 text-emerald-700" : status === "Habis" ? "bg-red-100 text-red-700" : "bg-amber-100 text-amber-700"}`}>{status}</span></div>
                        <div className={`mt-4 grid gap-3 ${isOwner ? "sm:grid-cols-4" : "sm:grid-cols-3"}`}><InfoBox label="Stok" value={`${material.stock} ${material.unit}`} /><InfoBox label="Minimum" value={`${material.minimum_stock} ${material.unit}`} /><InfoBox label="Kebutuhan" value={status === "Aman" ? "Cukup" : `Beli minimal ${Math.max(0, Number(material.minimum_stock) - Number(material.stock))} ${material.unit}`} />{isOwner ? <InfoBox label="Harga Beli" value={formatCurrency(material.purchase_price)} /> : null}</div>
                        {isOwner ? <div className="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto_auto]"><input type="number" min="0" value={edit.minimum_stock ?? material.minimum_stock} onChange={(event) => updateEdit(material, "minimum_stock", event.target.value)} aria-label={`Stok minimum ${material.name}`} className="rounded-xl border border-stone-200 px-3 py-2" /><input type="number" min="0" value={edit.purchase_price ?? material.purchase_price} onChange={(event) => updateEdit(material, "purchase_price", event.target.value)} aria-label={`Harga beli ${material.name}`} className="rounded-xl border border-stone-200 px-3 py-2" /><button onClick={() => saveMaterial(material)} className="rounded-xl bg-stone-900 px-4 py-2 text-xs font-black text-white uppercase">Simpan Data</button><button onClick={() => deactivateMaterial(material)} className="rounded-xl border border-red-200 px-4 py-2 text-xs font-black text-red-600 uppercase">Nonaktifkan</button></div> : null}
                        <div className="mt-4 grid gap-3 sm:grid-cols-[150px_120px_1fr_auto]"><select value={adjustment.type ?? (isKitchen ? "damaged" : "in")} onChange={(event) => updateAdjustment(material.id, "type", event.target.value)} className="rounded-xl border border-stone-200 px-3 py-2">{isOwner ? <><option value="in">Masuk</option><option value="out">Keluar</option><option value="adjustment">Koreksi</option></> : null}<option value="damaged">Rusak</option><option value="expired">Kedaluwarsa</option></select><input type="number" min="1" value={adjustment.quantity ?? ""} onChange={(event) => updateAdjustment(material.id, "quantity", event.target.value)} placeholder="Jumlah" className="rounded-xl border border-stone-200 px-3 py-2" /><input value={adjustment.note ?? ""} onChange={(event) => updateAdjustment(material.id, "note", event.target.value)} placeholder="Catatan" className="rounded-xl border border-stone-200 px-3 py-2" /><button onClick={() => handleAdjust(material)} className="rounded-xl bg-red-600 px-4 py-2 text-xs font-black text-white uppercase">Catat Stok</button></div>
                        <button onClick={() => loadMovements(material)} className="mt-4 text-xs font-bold text-red-600">Lihat riwayat stok →</button>
                    </article>;
                })}</div>}
            </div>
        </main>
    );
}

function InfoBox({ label, value }) {
    return <div className="rounded-xl bg-stone-50 p-3"><p className="text-[9px] font-black tracking-wider text-stone-400 uppercase">{label}</p><p className="mt-1 text-sm font-black text-stone-800">{value}</p></div>;
}

export default MaterialPage;
