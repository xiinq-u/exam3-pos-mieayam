const preloadedImages = new Set();
const failedImages = new Set();
const loadingImages = new Map();
let cacheGeneration = 0;

export function getProductImageUrl(image) {
    if (!image) {
        return null;
    }

    const imagePath = String(image);

    if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) {
        return imagePath;
    }

    const cleanPath = imagePath
        .replace(/^\/?storage\//, "")
        .replace(/^\/+/, "");

    return `/storage/${cleanPath}`;
}

export function preloadImage(url) {
    if (!url || preloadedImages.has(url)) {
        return Promise.resolve(url);
    }

    if (failedImages.has(url)) {
        return Promise.resolve(null);
    }

    if (loadingImages.has(url)) {
        return loadingImages.get(url);
    }

    const requestGeneration = cacheGeneration;
    const request = new Promise((resolve) => {
        const image = new Image();

        image.onload = () => {
            if (requestGeneration === cacheGeneration) {
                preloadedImages.add(url);
            }
            loadingImages.delete(url);
            resolve(url);
        };

        image.onerror = () => {
            if (requestGeneration === cacheGeneration) {
                failedImages.add(url);
            }
            loadingImages.delete(url);
            resolve(null);
        };

        image.src = url;
    });

    loadingImages.set(url, request);

    return request;
}

export function preloadProductImages(products = []) {
    const urls = products
        .map((product) => getProductImageUrl(product?.image))
        .filter(Boolean);

    return Promise.allSettled(urls.map((url) => preloadImage(url)));
}

export function clearImagePreloadCache() {
    cacheGeneration += 1;
    preloadedImages.clear();
    failedImages.clear();
    loadingImages.clear();
}

export function getCachedProducts(cacheKey) {
    const cached = sessionStorage.getItem(cacheKey)
        ?? sessionStorage.getItem(`pos_page_cache:${cacheKey}`);

    if (!cached) {
        return [];
    }

    try {
        const parsed = JSON.parse(cached);

        if (Array.isArray(parsed)) {
            return parsed;
        }

        return Array.isArray(parsed?.data) ? parsed.data : [];
    } catch {
        sessionStorage.removeItem(cacheKey);
        sessionStorage.removeItem(`pos_page_cache:${cacheKey}`);

        return [];
    }
}
