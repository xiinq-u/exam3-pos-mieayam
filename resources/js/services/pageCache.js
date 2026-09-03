const PAGE_CACHE_PREFIX = "pos_page_cache:";

const storageKey = (key) => `${PAGE_CACHE_PREFIX}${key}`;

export function getPageCache(key) {
    try {
        const value = sessionStorage.getItem(storageKey(key));

        if (!value) {
            return null;
        }

        const parsed = JSON.parse(value);

        return parsed && Object.hasOwn(parsed, "data") && parsed.cachedAt
            ? parsed
            : null;
    } catch {
        removePageCache(key);

        return null;
    }
}

export function setPageCache(key, data) {
    try {
        sessionStorage.setItem(
            storageKey(key),
            JSON.stringify({ data, cachedAt: Date.now() }),
        );
    } catch (error) {
        console.warn(`Cache halaman ${key} tidak dapat disimpan:`, error);
    }
}

export function removePageCache(key) {
    sessionStorage.removeItem(storageKey(key));
}

export function removePageCacheByPrefix(keyPrefix) {
    Object.keys(sessionStorage)
        .filter((key) => key.startsWith(storageKey(keyPrefix)))
        .forEach((key) => sessionStorage.removeItem(key));
}

export function clearPageCache() {
    Object.keys(sessionStorage)
        .filter((key) => key.startsWith(PAGE_CACHE_PREFIX))
        .forEach((key) => sessionStorage.removeItem(key));
    sessionStorage.removeItem("cashier_products");
}

export function isPageCacheFresh(key, maxAge = 60_000) {
    const cached = getPageCache(key);

    return Boolean(cached && Date.now() - cached.cachedAt <= maxAge);
}

export function invalidateOrderPageCaches() {
    removePageCacheByPrefix("pending_orders");
    removePageCacheByPrefix("managed_orders");
    removePageCache("dashboard");
    removePageCache("owner_dashboard");
    removePageCache("cashier_dashboard");
    removePageCache("kitchen_dashboard");
    removePageCacheByPrefix("sales_report_");
    removePageCacheByPrefix("product_sales_report_");
    removePageCacheByPrefix("order_type_report_");
    removePageCacheByPrefix("profit_loss_");
}

export function invalidateProductPageCaches() {
    removePageCache("products");
    removePageCacheByPrefix("product_detail_");
    removePageCache("cashier_products");
    sessionStorage.removeItem("cashier_products");
    removePageCache("dashboard");
    removePageCache("owner_dashboard");
}

export function invalidateCashierDashboardCaches() {
    removePageCache("cashier_dashboard");
    removePageCache("owner_dashboard");
    removePageCacheByPrefix("pending_orders");
    removePageCacheByPrefix("sales_report_");
    removePageCacheByPrefix("profit_loss_");
}

export function invalidateKitchenDashboardCaches() {
    removePageCache("kitchen_dashboard");
    removePageCache("cashier_dashboard");
    removePageCache("owner_dashboard");
    removePageCache("kitchen_orders");
    removePageCache("materials");
}

export function invalidateMaterialCaches() {
    removePageCache("materials");
    removePageCache("owner_dashboard");
    removePageCache("kitchen_dashboard");
}

export function invalidateFinanceDashboardCaches() {
    removePageCache("owner_dashboard");
    removePageCacheByPrefix("profit_loss_");
    removePageCacheByPrefix("expenses_");
    removePageCacheByPrefix("incomes_");
}
