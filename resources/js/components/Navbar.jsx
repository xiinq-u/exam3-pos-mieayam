import { useMemo, useState } from "react";
import axios from "axios";
import { NavLink, useNavigate } from "react-router-dom";
import {
    clearAuthentication,
    getStoredToken,
    getStoredUser,
} from "../services/auth";

const navigationByRole = {
    owner: [
        { label: "DASHBOARD", to: "/owner" },
        { label: "PRODUK & KATEGORI", to: "/products" },
        { label: "BAHAN BAKU", to: "/materials" },
        { label: "PENDAPATAN", to: "/finance" },
        { label: "LAPORAN", to: "/reports" },
        { label: "KELOLA PEGAWAI", to: "/users" },
        { label: "AUDIT LOG", to: "/audit-logs" },
        { label: "AKUN", to: "/account" },
    ],
    cashier: [
        { label: "DASHBOARD KASIR", to: "/cashier" },
        { label: "TRANSAKSI BARU", to: "/cashier/transaction" },
        { label: "PESANAN", to: "/orders" },
        { label: "SHIFT SAYA", to: "/shifts" },
        { label: "AKUN", to: "/account" },
    ],
    kitchen: [
        { label: "DASHBOARD DAPUR", to: "/kitchen" },
        { label: "ANTREAN DAPUR", to: "/kitchen/orders" },
        { label: "BAHAN BAKU", to: "/materials" },
        { label: "AKUN", to: "/account" },
    ],
};

function Navbar({ onClose }) {
    const navigate = useNavigate();
    const user = getStoredUser();
    const token = getStoredToken();
    const [loggingOut, setLoggingOut] = useState(false);

    const accessibleItems = navigationByRole[user?.role] ?? [];
    const cashierNumber = String(user?.id ?? 1).padStart(3, "0");
    const dateTime = useMemo(
        () =>
            new Intl.DateTimeFormat("id-ID", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
            }).format(new Date()),
        [],
    );

    const logout = async () => {
        setLoggingOut(true);

        try {
            if (token) {
                await axios.post(
                    "/api/logout",
                    {},
                    { headers: { Authorization: `Bearer ${token}` } },
                );
            }
        } catch (error) {
            console.error("Logout failed:", error);
        } finally {
            clearAuthentication();
            onClose();
            navigate("/login", { replace: true });
        }
    };

    const totalMenuItems = accessibleItems.length + 1;

    return (
        <aside className="relative mx-auto min-h-full w-full select-none border border-stone-200/80 bg-[#FFFDF9] px-4 pt-6 pb-8 font-mono text-xs text-stone-700 shadow-xl transition-shadow duration-300 ease-out">
            <div className="mb-4 flex items-center justify-between lg:hidden">
                <span className="text-xs font-bold tracking-wider text-stone-800 uppercase">Menu</span>
                <button
                    type="button"
                    onClick={onClose}
                    className="flex h-7 w-7 items-center justify-center rounded-md text-stone-500 hover:bg-stone-100 hover:text-stone-900"
                    aria-label="Tutup menu"
                >
                    <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" aria-hidden="true">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div className="mb-4 w-full border-b border-stone-300" />
            <div className="mb-6 space-y-1 text-center">
                <h2 className="text-sm font-black tracking-wider text-stone-900 uppercase">Mie Ayam Puput</h2>
                <p className="text-[10px] text-stone-400 uppercase">Jl. Raya Solo - Pemesanan Internal</p>
                <p className="text-[9px] text-stone-400">TELP: 0812-XXXX-XXXX</p>
                <div className="border-b-2 border-dashed border-stone-300/80 pt-3" />
                <div className="flex justify-between px-1 pt-1 text-[10px] text-stone-500">
                    <span>TGL: {dateTime}</span>
                    <span>KASIR: #{cashierNumber}</span>
                </div>
                <div className="border-b-2 border-dashed border-stone-300/80 pt-1" />
            </div>

            <div className="space-y-4">
                <span className="block px-1 text-[9px] tracking-wider text-stone-400 uppercase">Daftar Menu Utama:</span>
                <ul className="space-y-1">
                    {accessibleItems.map((item, index) => (
                        <li key={item.to}>
                            <NavLink to={item.to} end onClick={onClose} className="group block w-full">
                                {({ isActive }) => (
                                    <div className={`flex w-full items-center justify-between rounded-md border-l-4 px-3 py-3 transition duration-150 ${isActive ? "border-red-500 bg-stone-100 font-bold text-stone-900" : "border-transparent bg-transparent text-stone-500 hover:bg-stone-50 hover:text-stone-800"}`}>
                                        <span>{String(index + 1).padStart(2, "0")}. {item.label}</span>
                                        {isActive ? (
                                            <span className="text-[10px] font-black text-red-600">[*AKTIF*]</span>
                                        ) : (
                                            <span className="text-[10px] opacity-0 group-hover:opacity-100">-&gt;</span>
                                        )}
                                    </div>
                                )}
                            </NavLink>
                        </li>
                    ))}
                    <li>
                        <button type="button" onClick={logout} disabled={loggingOut} className="group block w-full text-left disabled:cursor-wait disabled:opacity-60">
                            <div className="flex w-full items-center justify-between rounded-md border-l-4 border-transparent px-3 py-3 text-red-600 transition duration-150 hover:border-red-500 hover:bg-red-50 hover:text-red-700">
                                <span>{String(totalMenuItems).padStart(2, "0")}. {loggingOut ? "KELUAR..." : "LOGOUT"}</span>
                                <span className="text-[10px] opacity-0 group-hover:opacity-100">-&gt;</span>
                            </div>
                        </button>
                    </li>
                </ul>
            </div>

            <div className="mt-6 space-y-1">
                <div className="mb-2 border-b-2 border-dashed border-stone-300/80" />
                <div className="flex justify-between px-1 text-[11px] font-bold text-stone-800">
                    <span>TOTAL MENU ACCESS</span>
                    <span>{totalMenuItems} ITEMS</span>
                </div>
                <div className="border-b border-stone-300 pt-3" />
            </div>

            <div className="mt-6 space-y-1 text-center text-[9px] tracking-widest text-stone-400 uppercase">
                <p>Jaga Kualitas Rasa &amp; Pelayanan</p>
                <p className="font-bold text-stone-500">*** Simpan Struk Ini ***</p>
            </div>
            <div className="receipt-torn-edge pointer-events-none absolute right-0 bottom-0 left-0 h-3 translate-y-full" />
        </aside>
    );
}

export default Navbar;
