import { useEffect, useState } from "react";
import axios from "axios";
import { Link, Navigate, useNavigate } from "react-router-dom";
import { getStoredToken } from "../services/auth";
import { removePageCache } from "../services/pageCache";

function UserManagementPage() {
    const navigate = useNavigate();
    const token = getStoredToken();
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [formData, setFormData] = useState({
        name: "",
        email: "",
        password: "",
        role: "cashier",
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        if (!token) {
            navigate("/login");
            return;
        }

        const fetchUsers = async () => {
            try {
                setLoading(true);
                const response = await axios.get("/api/users", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                setUsers(response.data.data || []);
            } catch (requestError) {
                console.error("Failed to load users:", requestError);
                setError("Tidak dapat memuat data pegawai.");
            } finally {
                setLoading(false);
            }
        };

        fetchUsers();
    }, [token, navigate]);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError("");
        setIsSubmitting(true);

        try {
            await axios.post("/api/users", formData, {
                headers: { Authorization: `Bearer ${token}` },
            });

            setFormData({ name: "", email: "", password: "", role: "cashier" });
            removePageCache("owner_dashboard");

            const response = await axios.get("/api/users", {
                headers: { Authorization: `Bearer ${token}` },
            });
            setUsers(response.data.data || []);
        } catch (requestError) {
            setError(
                requestError?.response?.data?.message ||
                    "Gagal membuat pegawai.",
            );
        } finally {
            setIsSubmitting(false);
        }
    };

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    return (
        <div className="min-h-screen bg-slate-100 p-8">
            <div className="mx-auto max-w-6xl space-y-6">
                <div className="flex items-center justify-between rounded-2xl bg-white p-6 shadow">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-sky-600">
                            POS Mie Ayam
                        </p>
                        <h1 className="mt-2 text-3xl font-bold text-slate-800">
                            Kelola Pegawai
                        </h1>
                    </div>

                    <Link
                        to="/owner"
                        className="rounded-xl bg-slate-800 px-4 py-2 font-medium text-white hover:bg-slate-900"
                    >
                        Dashboard
                    </Link>
                </div>

                <div className="rounded-2xl bg-white p-6 shadow">
                    <h2 className="text-xl font-bold text-slate-800">
                        Tambah Pegawai Baru
                    </h2>

                    {error ? (
                        <div className="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
                            {error}
                        </div>
                    ) : null}

                    <form
                        className="mt-6 grid gap-4 md:grid-cols-2"
                        onSubmit={handleSubmit}
                    >
                        <div>
                            <label className="mb-1 block text-sm font-medium text-slate-700">
                                Nama
                            </label>
                            <input
                                type="text"
                                required
                                value={formData.name}
                                onChange={(event) =>
                                    setFormData({
                                        ...formData,
                                        name: event.target.value,
                                    })
                                }
                                className="w-full rounded-xl border border-slate-300 px-3 py-2"
                                placeholder="Nama pegawai"
                            />
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium text-slate-700">
                                Email
                            </label>
                            <input
                                type="email"
                                required
                                value={formData.email}
                                onChange={(event) =>
                                    setFormData({
                                        ...formData,
                                        email: event.target.value,
                                    })
                                }
                                className="w-full rounded-xl border border-slate-300 px-3 py-2"
                                placeholder="email@example.com"
                            />
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium text-slate-700">
                                Password
                            </label>
                            <input
                                type="password"
                                required
                                value={formData.password}
                                onChange={(event) =>
                                    setFormData({
                                        ...formData,
                                        password: event.target.value,
                                    })
                                }
                                className="w-full rounded-xl border border-slate-300 px-3 py-2"
                                placeholder="••••••••"
                            />
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium text-slate-700">
                                Role
                            </label>
                            <select
                                required
                                value={formData.role}
                                onChange={(event) =>
                                    setFormData({
                                        ...formData,
                                        role: event.target.value,
                                    })
                                }
                                className="w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="cashier">Kasir</option>
                                <option value="kitchen">Dapur</option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            disabled={isSubmitting}
                            className="mt-6 rounded-xl bg-sky-600 px-4 py-2 font-medium text-white hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-70 md:col-span-2"
                        >
                            {isSubmitting ? "Membuat..." : "Buat Pegawai"}
                        </button>
                    </form>
                </div>

                <div className="rounded-2xl bg-white p-6 shadow">
                    <h2 className="text-xl font-bold text-slate-800">
                        Daftar Pegawai
                    </h2>

                    {loading ? (
                        <p className="mt-4 text-slate-500">
                            Memuat data pegawai...
                        </p>
                    ) : users.length === 0 ? (
                        <p className="mt-4 text-sm text-slate-500">
                            Belum ada pegawai yang terdaftar.
                        </p>
                    ) : (
                        <div className="mt-4 grid gap-3">
                            {users.map((user) => (
                                <div
                                    key={user.id}
                                    className="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4"
                                >
                                    <div>
                                        <p className="font-semibold text-slate-800">
                                            {user.name}
                                        </p>
                                        <p className="text-sm text-slate-500">
                                            {user.email}
                                        </p>
                                    </div>

                                    <span className="rounded-full bg-sky-100 px-3 py-1 text-sm font-medium text-sky-700">
                                        {user.role === "cashier"
                                            ? "Kasir"
                                            : user.role === "kitchen"
                                              ? "Dapur"
                                              : user.role}
                                    </span>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default UserManagementPage;
