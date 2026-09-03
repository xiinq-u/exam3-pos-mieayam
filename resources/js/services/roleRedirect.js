export const ROLE_HOME_PATHS = {
    owner: "/owner",
    cashier: "/cashier",
    kitchen: "/kitchen",
};

export function getRoleHomePath(role) {
    return ROLE_HOME_PATHS[role] ?? "/login";
}
