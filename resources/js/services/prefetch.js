import axios from "axios";
import { preloadProductImages } from "./imagePreloader";
import { isPageCacheFresh, setPageCache } from "./pageCache";

const dateValue = (date) => date.toISOString().split("T")[0];

const requestAndCache = async (key, request, transform = (response) => response.data) => {
    if (isPageCacheFresh(key, 60_000)) {
        return;
    }

    const response = await request();
    setPageCache(key, transform(response));
};

const productList = (response) => Array.isArray(response.data)
    ? response.data
    : Array.isArray(response.data?.data)
        ? response.data.data
        : [];

export async function refreshCashierProductCache(token) {
    const response = await axios.get("/api/cashier/products", {
        headers: { Authorization: `Bearer ${token}` },
    });
    const products = productList(response);

    setPageCache("cashier_products", products);
    sessionStorage.setItem(
        "cashier_products",
        JSON.stringify({ data: products, cachedAt: Date.now() }),
    );
    void preloadProductImages(products);

    return products;
}

export async function refreshProductCaches(token) {
    const headers = { Authorization: `Bearer ${token}` };
    const [productsResult, cashierResult] = await Promise.allSettled([
        axios.get("/api/products", { headers }),
        axios.get("/api/cashier/products", { headers }),
    ]);
    const products = productsResult.status === "fulfilled"
        ? productList(productsResult.value)
        : [];
    const cashierProducts = cashierResult.status === "fulfilled"
        ? productList(cashierResult.value)
        : [];

    if (productsResult.status === "fulfilled") {
        setPageCache("products", products);
        products.forEach((product) => setPageCache(`product_detail_${product.id}`, product));
    }

    if (cashierResult.status === "fulfilled") {
        setPageCache("cashier_products", cashierProducts);
        sessionStorage.setItem(
            "cashier_products",
            JSON.stringify({ data: cashierProducts, cachedAt: Date.now() }),
        );
    }

    void preloadProductImages([...products, ...cashierProducts]);
}

export async function prefetchLoginEssentials(token, user) {
    const headers = { Authorization: `Bearer ${token}` };
    const role = user?.role;
    const dashboardPath = role ? `/api/${role}/dashboard` : null;

    if (dashboardPath) {
        await requestAndCache(`${role}_dashboard`, () => axios.get(dashboardPath, { headers }));
    }
}

export async function prefetchForUser(token, user) {
    if (!token || !user?.role) {
        return;
    }

    const headers = { Authorization: `Bearer ${token}` };
    const today = dateValue(new Date());
    const reportStart = dateValue(new Date(Date.now() - 6 * 24 * 60 * 60 * 1000));
    const reportQuery = `start_date=${reportStart}&end_date=${today}&period=daily`;
    const dateQuery = `start_date=${today}&end_date=${today}`;
    const tasks = [
        requestAndCache(`${user.role}_dashboard`, () => axios.get(`/api/${user.role}/dashboard`, { headers })),
    ];

    if (user.role === "cashier") {
        tasks.push(
            requestAndCache("pending_orders", () => axios.get("/api/cashier/orders/pending?page=1", { headers })),
        );
    }

    if (user.role === "owner") {
        tasks.push(
            requestAndCache("products", () => axios.get("/api/products", { headers }), (response) => {
                const products = productList(response);
                products.forEach((product) => setPageCache(`product_detail_${product.id}`, product));
                void preloadProductImages(products);

                return products;
            }),
            requestAndCache("categories", () => axios.get("/api/categories", { headers })),
            requestAndCache("materials", () => axios.get("/api/materials", { headers })),
            requestAndCache(`sales_report_${reportStart}_${today}_daily`, () => axios.get(`/api/reports/sales-summary?${reportQuery}`, { headers }), (response) => response.data.data),
            requestAndCache(`product_sales_report_${reportStart}_${today}`, () => axios.get(`/api/reports/product-sales?start_date=${reportStart}&end_date=${today}`, { headers }), (response) => response.data.data),
            requestAndCache(`order_type_report_${reportStart}_${today}`, () => axios.get(`/api/reports/order-type-sales?start_date=${reportStart}&end_date=${today}`, { headers }), (response) => response.data.data),
            requestAndCache(`expenses_${today}_${today}`, () => axios.get(`/api/expenses?${dateQuery}`, { headers }), (response) => response.data.data),
            requestAndCache(`incomes_${today}_${today}`, () => axios.get(`/api/incomes?${dateQuery}`, { headers }), (response) => response.data.data),
            requestAndCache("financial_categories", () => axios.get("/api/financial-categories", { headers }), (response) => response.data.data),
            requestAndCache("cashier_shifts", () => axios.get("/api/shifts", { headers }), (response) => response.data.data),
            requestAndCache(`profit_loss_${today}_${today}`, () => axios.get(`/api/reports/profit-loss?${dateQuery}`, { headers })),
        );
    }

    if (user.role === "kitchen") {
        tasks.push(
            requestAndCache("kitchen_orders", () => axios.get("/api/kitchen/orders", { headers })),
            requestAndCache("materials", () => axios.get("/api/materials", { headers })),
        );
    }

    await Promise.allSettled(tasks);
}
