import { Navigate } from "react-router-dom";
import {
    clearAuthentication,
    getStoredToken,
    getStoredUser,
} from "../services/auth";
import { getRoleHomePath } from "../services/roleRedirect";

function RoleHomeRedirect() {
    const token = getStoredToken();
    const user = getStoredUser();
    const homePath = getRoleHomePath(user?.role);

    if (!token || !user?.role || homePath === "/login") {
        clearAuthentication();

        return <Navigate to="/login" replace />;
    }

    return <Navigate to={homePath} replace />;
}

export default RoleHomeRedirect;
