import { useEffect, useState } from "react";
import axios from "axios";
import { Outlet, useLocation } from "react-router-dom";
import { getStoredToken, getStoredUser } from "../services/auth";
import {
    getCachedProducts,
    preloadProductImages,
} from "../services/imagePreloader";
import { setPageCache } from "../services/pageCache";
import { prefetchForUser } from "../services/prefetch";
import Footer from "./Footer";
import Header from "./Header";
import Navbar from "./Navbar";

let productPreloadToken = null;

function AppShell() {
    const location = useLocation();
    const token = getStoredToken();
    const user = getStoredUser();
    const [sidebarOpen, setSidebarOpen] = useState(false);

    useEffect(() => {
        setSidebarOpen(false);
    }, [location.pathname]);

    useEffect(() => {
        if (token && user) {
            void prefetchForUser(token, user);
        }
    }, [token, user?.id, user?.role]);

    useEffect(() => {
        if (!token || user?.role !== "cashier" || productPreloadToken === token) {
            return;
        }

        productPreloadToken = token;
        const cachedProducts = getCachedProducts("cashier_products");

        if (cachedProducts.length > 0) {
            void preloadProductImages(cachedProducts);
        }

        void axios.get("/api/cashier/products", {
            headers: { Authorization: `Bearer ${token}` },
        }).then((response) => {
            const products = Array.isArray(response.data)
                ? response.data
                : Array.isArray(response.data?.data)
                    ? response.data.data
                    : [];

            setPageCache("cashier_products", products);
            sessionStorage.setItem(
                "cashier_products",
                JSON.stringify({ data: products, cachedAt: Date.now() }),
            );
            void preloadProductImages(products);
        }).catch((error) => {
            console.warn("Preload gambar produk gagal dijalankan:", error);
        });
    }, [token, user?.role]);

    if (!token) {
        return <Outlet />;
    }

    return (
        <div className="min-h-screen bg-stone-50">
            <Header onOpenMenu={() => setSidebarOpen(true)} />

            {sidebarOpen ? (
                <div className="fixed inset-0 z-50 lg:hidden">
                    <button
                        type="button"
                        onClick={() => setSidebarOpen(false)}
                        className="absolute inset-0 bg-stone-950/40 backdrop-blur-[1px]"
                        aria-label="Tutup menu"
                    />
                    <div className="absolute inset-y-0 left-0 w-[min(21rem,88vw)] overflow-y-auto pb-3">
                        <Navbar onClose={() => setSidebarOpen(false)} />
                    </div>
                </div>
            ) : null}

            <div className="lg:grid lg:grid-cols-[20rem_minmax(0,1fr)]">
                <div className="sticky top-[68px] hidden h-[calc(100vh-68px)] overflow-y-auto pb-3 lg:block">
                    <Navbar onClose={() => setSidebarOpen(false)} />
                </div>

                <div className="min-w-0">
                    <Outlet />
                    <Footer />
                </div>
            </div>
        </div>
    );
}

export default AppShell;
