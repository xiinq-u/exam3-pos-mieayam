import { useEffect, useState } from "react";
import { NavLink } from "react-router-dom";
import { getStoredUser } from "../services/auth";
import { getRoleHomePath } from "../services/roleRedirect";

const roleHomeLabels = {
    owner: "OWNER",
    cashier: "KASIR",
    kitchen: "DAPUR",
};

function Header({ onOpenMenu }) {
    const user = getStoredUser();
    const homePath = getRoleHomePath(user?.role);
    const homeLabel = roleHomeLabels[user?.role] ?? "Beranda";
    const [isScrolled, setIsScrolled] = useState(false);

    useEffect(() => {
        const updateHeader = () => setIsScrolled(window.scrollY > 12);

        window.addEventListener("scroll", updateHeader, { passive: true });
        updateHeader();

        return () => window.removeEventListener("scroll", updateHeader);
    }, []);

    return (
        <header
            className={`sticky top-0 z-40 flex h-[68px] select-none items-center border-b-2 border-dashed border-amber-200 px-4 transition-all duration-300 ease-out lg:px-8 ${
                isScrolled
                    ? "bg-[#FFFDF9]/95 py-2 shadow-lg shadow-stone-900/5 backdrop-blur-md"
                    : "bg-[#FFFDF9] py-3"
            }`}
        >
            <button
                type="button"
                onClick={onOpenMenu}
                className="mr-1 flex h-9 w-9 flex-none items-center justify-center rounded-lg text-stone-700 hover:bg-stone-100 lg:hidden"
                aria-label="Buka menu"
            >
                <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div className="flex min-w-0 flex-1 items-center space-x-2 sm:space-x-3">
                <div className="relative flex h-9 w-10 origin-bottom-left scale-90 items-end justify-center pb-1 sm:h-11 sm:w-12 sm:scale-100">
                    <div className="absolute top-1 left-2 h-0.5 w-10 rotate-[25deg] bg-amber-800 opacity-80" />
                    <div className="absolute top-2 left-1 h-0.5 w-10 rotate-[15deg] bg-amber-800 opacity-80" />
                    <div className="absolute bottom-4 z-10 flex flex-col items-center">
                        <div className="-mb-0.5 flex space-x-0.5">
                            <div className="h-2.5 w-3 rounded-full border-t-2 border-amber-400" />
                            <div className="h-2.5 w-3 rounded-full border-t-2 border-amber-400" />
                        </div>
                        <div className="absolute -top-1.5 z-20 flex space-x-0.5">
                            <div className="h-2.5 w-2.5 rounded-full border border-stone-500/30 bg-stone-400 shadow-sm" />
                            <div className="mt-0.5 h-2 w-2 rounded-full border border-stone-500/30 bg-stone-400 shadow-sm" />
                        </div>
                    </div>
                    <div className="relative z-30 h-4 w-9 rounded-b-full border-x border-b border-amber-300 bg-gradient-to-b from-red-500 to-red-600 shadow-md sm:h-5 sm:w-10">
                        <div className="absolute top-1 left-1/2 h-0.5 w-6 -translate-x-1/2 rounded-full bg-amber-300/60" />
                    </div>
                </div>

                <div className="flex min-w-0 flex-col text-left leading-none">
                    <span className="truncate text-sm font-black tracking-tight whitespace-nowrap text-stone-800 uppercase sm:text-base">
                        Mie Ayam <span className="text-red-500">Puput</span>
                    </span>
                    <span className="mt-0.5 font-mono text-[8px] tracking-widest text-stone-400 uppercase sm:text-[9px]">
                        Sistem Kasir
                    </span>
                </div>
            </div>

            <nav className="flex flex-none items-center">
                <div className="hidden items-center gap-3 sm:flex">
                    <div className="mr-1 flex flex-col text-right leading-none">
                        <span className="max-w-40 truncate text-xs font-bold text-stone-700">
                            {user?.name ?? "Petugas"}
                        </span>
                        <span className="mt-0.5 font-mono text-[9px] tracking-wider text-amber-600 uppercase">
                            {homeLabel}
                        </span>
                    </div>
                    <NavLink
                        to={homePath}
                        className="flex items-center space-x-1.5 rounded-xl bg-gradient-to-r from-red-500 to-red-600 px-4 py-2 text-xs font-bold tracking-wider text-white shadow-md shadow-red-500/10 transition-all duration-200 hover:from-red-600 hover:to-red-700 active:scale-[0.98]"
                    >
                        <DashboardIcon className="h-3.5 w-3.5" />
                        <span>{homeLabel}</span>
                    </NavLink>
                </div>

                <NavLink
                    to={homePath}
                    aria-label={homeLabel}
                    className="flex items-center justify-center rounded-xl bg-gradient-to-r from-red-500 to-red-600 p-2 text-white shadow-md transition-all duration-200 active:scale-[0.95] sm:hidden"
                >
                    <DashboardIcon className="h-4 w-4" />
                </NavLink>
            </nav>
        </header>
    );
}

function DashboardIcon({ className }) {
    return (
        <svg className={className} fill="none" viewBox="0 0 24 24" strokeWidth="2.5" stroke="currentColor" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
        </svg>
    );
}

export default Header;
