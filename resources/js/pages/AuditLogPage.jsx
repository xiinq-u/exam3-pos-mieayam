import { useEffect, useState } from "react";
import axios from "axios";
import { Link, Navigate } from "react-router-dom";
import { getStoredToken } from "../services/auth";

function AuditLogPage() {
    const token = getStoredToken();
    const [logs, setLogs] = useState([]);
    const [error, setError] = useState("");

    useEffect(() => {
        if (!token) {
            return;
        }
        axios
            .get("/api/audit-logs", {
                headers: { Authorization: `Bearer ${token}` },
            })
            .then((response) => setLogs(response.data.data || []))
            .catch((requestError) =>
                setError(
                    requestError?.response?.data?.message ||
                        "Audit log tidak dapat dimuat.",
                ),
            );
    }, [token]);

    if (!token) {
        return <Navigate to="/login" replace />;
    }

    return (
        <div className="min-h-screen bg-slate-100 p-8">
            <div className="mx-auto max-w-5xl space-y-6">
                <div className="flex items-center justify-between rounded-2xl bg-white p-6 shadow">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-600">
                            POS Mie Ayam
                        </p>
                        <h1 className="mt-2 text-3xl font-bold text-slate-800">
                            Audit Log
                        </h1>
                    </div>
                    <Link
                        to="/"
                        className="rounded-xl bg-slate-800 px-4 py-2 text-white"
                    >
                        Dashboard
                    </Link>
                </div>
                {error ? (
                    <p className="rounded-xl bg-red-50 p-3 text-red-600">
                        {error}
                    </p>
                ) : null}
                <div className="overflow-x-auto rounded-2xl bg-white shadow">
                    <table className="w-full text-sm">
                        <thead className="bg-slate-50 text-left">
                            <tr>
                                <th className="p-4">Waktu</th>
                                <th className="p-4">Pengguna</th>
                                <th className="p-4">Aksi</th>
                                <th className="p-4">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            {logs.map((log) => (
                                <tr key={log.id} className="border-t">
                                    <td className="p-4">
                                        {new Date(
                                            log.created_at,
                                        ).toLocaleString("id-ID")}
                                    </td>
                                    <td className="p-4">
                                        {log.user?.name || "Sistem"}
                                    </td>
                                    <td className="p-4 font-medium">
                                        {log.action}
                                    </td>
                                    <td className="p-4">
                                        {log.properties
                                            ? JSON.stringify(log.properties)
                                            : "-"}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}

export default AuditLogPage;
