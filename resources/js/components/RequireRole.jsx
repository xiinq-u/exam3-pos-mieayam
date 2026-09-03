import { Navigate } from "react-router-dom";
import {
    clearAuthentication,
    getStoredToken,
    getStoredUser,
} from "../services/auth";
import { getRoleHomePath } from "../services/roleRedirect";

function RequireRole({ roles, children }) {
    const token = getStoredToken();
    const user = getStoredUser();

    if (!token || !user?.role) {
        clearAuthentication();

        return <Navigate to="/login" replace />;
    }

    if (!roles.includes(user.role)) {
        return <Navigate to={getRoleHomePath(user.role)} replace />;
    }

    return children;
}

export default RequireRole;
