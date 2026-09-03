import { Fragment, useEffect, useState } from "react";
import axios from "axios";
import { useLocation, useNavigate } from "react-router-dom";
import {
    clearAuthentication,
    getStoredToken,
    updateStoredUser,
} from "../services/auth";
import { getRoleHomePath } from "../services/roleRedirect";

let validatedToken = null;

function SessionValidator({ children }) {
    const location = useLocation();
    const navigate = useNavigate();
    const [sessionVersion, setSessionVersion] = useState(0);

    useEffect(() => {
        const token = getStoredToken();

        if (!token || validatedToken === token) {
            return;
        }

        validatedToken = token;

        void axios.get("/api/me", {
            headers: { Authorization: `Bearer ${token}` },
        }).then((response) => {
            if (getStoredToken() !== token) {
                return;
            }

            const user = response.data?.user;

            if (!user?.role || getRoleHomePath(user.role) === "/login") {
                clearAuthentication();
                validatedToken = null;
                navigate("/login", { replace: true });

                return;
            }

            updateStoredUser(user);
            setSessionVersion((currentVersion) => currentVersion + 1);
        }).catch((error) => {
            if (
                getStoredToken() === token
                && [401, 403].includes(error?.response?.status)
            ) {
                clearAuthentication();
                validatedToken = null;
                navigate("/login", { replace: true });
            }
        });
    }, [location.pathname, navigate]);

    return <Fragment key={sessionVersion}>{children}</Fragment>;
}

export default SessionValidator;
