import { useState } from "react";
import axios from "axios";
import { Link, Navigate, useNavigate } from "react-router-dom";
import { getStoredToken, TOKEN_KEY, USER_KEY } from "../services/auth";

function AccountPage() {
    const navigate = useNavigate();
    const token = getStoredToken();
    const [currentPassword, setCurrentPassword] = useState("");
    const [password, setPassword] = useState("");
    const [passwordConfirmation, setPasswordConfirmation] = useState("");
    const [message, setMessage] = useState("");
    const [error, setError] = useState("");

    const submit = async (event) => {
        event.preventDefault();
        try {
            setError("");
            const response = await axios.put(
                "/api/account/password",
                {
                    current_password: currentPassword,
                    password,
                    password_confirmation: passwordConfirmation,
                },
                { headers: { Authorization: `Bearer ${token}` } },
            );
            setMessage(response.data.message);
            setCurrentPassword("");
            setPassword("");
            setPasswordConfirmation("");
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    requestError?.response?.data?.errors?.password?.[0] ||
                    "Password gagal diubah.",
            );
        }
    };

    const logoutAll = async () => {
        try {
            await axios.post(
                "/api/logout-all",
                {},
                { headers: { Authorization: `Bearer ${token}` } },
            );
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);
            navigate("/login");
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Logout semua perangkat gagal.",
            );
        }
    };

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-100 p-6">
            <form
                onSubmit={submit}
                className="w-full max-w-md space-y-4 rounded-2xl bg-white p-8 shadow"
            >
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-slate-800">
                        Ganti Password
                    </h1>
                    <Link to="/" className="text-sm text-slate-600">
                        Dashboard
                    </Link>
                </div>
                <input
                    required
                    type="password"
                    value={currentPassword}
                    onChange={(event) => setCurrentPassword(event.target.value)}
                    placeholder="Password saat ini"
                    className="w-full rounded-xl border p-3"
                />
                <input
                    required
                    type="password"
                    value={password}
                    onChange={(event) => setPassword(event.target.value)}
                    placeholder="Password baru"
                    className="w-full rounded-xl border p-3"
                />
                <input
                    required
                    type="password"
                    value={passwordConfirmation}
                    onChange={(event) =>
                        setPasswordConfirmation(event.target.value)
                    }
                    placeholder="Konfirmasi password baru"
                    className="w-full rounded-xl border p-3"
                />
                {error ? <p className="text-sm text-red-600">{error}</p> : null}
                {message ? (
                    <p className="text-sm text-emerald-600">{message}</p>
                ) : null}
                <button className="w-full rounded-xl bg-slate-800 px-4 py-3 font-medium text-white">
                    Simpan Password
                </button>
                <button
                    type="button"
                    onClick={logoutAll}
                    className="w-full rounded-xl border border-red-300 px-4 py-3 font-medium text-red-600"
                >
                    Logout Semua Perangkat
                </button>
            </form>
        </div>
    );
}

export default AccountPage;
