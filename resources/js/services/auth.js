import { clearPageCache } from "./pageCache";
import { clearImagePreloadCache } from "./imagePreloader";

export const TOKEN_KEY = "pos_token";
export const USER_KEY = "pos_user";

export function getStoredToken() {
    return localStorage.getItem(TOKEN_KEY);
}

export function getStoredUser() {
    const storedUser = localStorage.getItem(USER_KEY);

    if (!storedUser) {
        return null;
    }

    try {
        return JSON.parse(storedUser);
    } catch {
        localStorage.removeItem(USER_KEY);
        return null;
    }
}

export function storeAuthentication(token, user) {
    clearPageCache();
    clearImagePreloadCache();
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user));
}

export function updateStoredUser(user) {
    localStorage.setItem(USER_KEY, JSON.stringify(user));
}

export function clearAuthentication() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    sessionStorage.clear();
    clearImagePreloadCache();
}
