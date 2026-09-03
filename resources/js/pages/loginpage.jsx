import { useState } from "react";
import axios from "axios";
import { Navigate, useNavigate } from "react-router-dom";
import {
    getStoredToken,
    getStoredUser,
    storeAuthentication,
} from "../services/auth";
import { prefetchLoginEssentials } from "../services/prefetch";
import { getRoleHomePath } from "../services/roleRedirect";

function NoodleBowlIllustration() {
    return (
        <div className="noodle-login-illustration group hidden cursor-pointer flex-col items-center justify-center space-y-8 lg:col-span-5 lg:flex">
            <div className="relative flex h-72 w-72 items-center justify-center">
                <div className="absolute top-4 z-10 flex space-x-3 opacity-0 transition-opacity duration-500 group-hover:opacity-100">
                    <div className="h-12 w-2 animate-bounce rounded-full bg-stone-200/40 blur-md" />
                    <div className="h-16 w-3 animate-pulse rounded-full bg-stone-200/50 blur-md [animation-delay:75ms]" />
                    <div className="h-10 w-2 animate-bounce rounded-full bg-stone-200/40 blur-md [animation-delay:150ms]" />
                </div>

                <div className="absolute top-16 -left-4 z-40 h-1.5 w-56 -rotate-[35deg] rounded-full bg-amber-800 shadow-md transition-transform duration-500 group-hover:translate-x-4 group-hover:-translate-y-2" />
                <div className="absolute top-20 -left-6 z-40 h-1.5 w-56 -rotate-[30deg] rounded-full bg-amber-800 shadow-md transition-transform duration-500 group-hover:translate-x-2 group-hover:-translate-y-1" />

                <div className="absolute bottom-24 z-20 flex h-24 w-52 flex-col items-center justify-end overflow-visible">
                    <div className="absolute -left-1 bottom-6 h-14 w-10 -rotate-12 rounded-t-3xl rounded-br-full border-l border-green-700 bg-green-600 shadow-sm transition-transform duration-300 group-hover:-rotate-20" />
                    <div className="absolute bottom-8 left-4 h-12 w-8 rotate-12 rounded-t-2xl rounded-bl-full bg-green-500 shadow-sm" />

                    <div className="noodle-filling relative h-16 w-48 overflow-hidden rounded-t-[30px] rounded-b-full border-b-4 border-amber-600 bg-amber-500 p-1 shadow-inner">
                        <div className="absolute inset-0 flex flex-wrap justify-center gap-1 pt-1 opacity-80">
                            {Array.from({ length: 4 }, (_, index) => (
                                <div className="contents" key={index}>
                                    <div className="h-6 w-10 rotate-12 rounded-[40%_20%_60%_30%] border-r-4 border-b-4 border-amber-300" />
                                    <div className="h-6 w-10 -rotate-12 rounded-[20%_50%_30%_60%] border-t-4 border-l-4 border-amber-200" />
                                </div>
                            ))}
                        </div>

                        <div className="absolute inset-0 z-10 flex items-center justify-center -space-x-2.5 pt-2">
                            <div className="h-8 w-14 rotate-45 rounded-[50%_50%_30%_40%] border-b-4 border-amber-300/90" />
                            <div className="mt-2 h-8 w-12 -rotate-12 rounded-[30%_60%_40%_50%] border-y-4 border-amber-200" />
                            <div className="h-7 w-14 rotate-12 rounded-[45%_35%_55%_45%] border-b-4 border-amber-300/90" />
                            <div className="h-8 w-11 -rotate-45 rounded-[60%_30%_50%_40%] border-b-4 border-l-4 border-amber-100/90" />
                        </div>

                        <div className="absolute inset-x-3 bottom-1 z-20 flex h-10 justify-around">
                            <div className="h-6 w-16 rotate-6 rounded-[50%_20%_50%_20%] border-b-[3.5px] border-amber-200" />
                            <div className="mt-1 h-5 w-16 -rotate-6 rounded-[20%_50%_20%_50%] border-b-[3.5px] border-amber-100" />
                        </div>
                    </div>

                    <div className="absolute top-5 left-8 z-30 flex h-7 w-24 -rotate-3 items-center justify-around rounded-full border border-amber-950 bg-amber-900 px-2 shadow-md">
                        <div className="h-2.5 w-2.5 rounded-sm border border-amber-950 bg-amber-800" />
                        <div className="h-3 w-3 rounded-sm bg-stone-900 shadow-sm" />
                        <div className="h-2.5 w-2 rounded-sm bg-amber-700" />
                    </div>

                    <div className="absolute top-6 left-28 z-40 flex rotate-12 space-x-1">
                        <div className="h-2 w-2 rounded-full border border-green-600 bg-green-500 ring-1 ring-green-300/30" />
                        <div className="mt-1 h-2 w-2.5 rounded-full border border-green-600 bg-green-400" />
                        <div className="h-1.5 w-1.5 rounded-full border border-green-600 bg-green-500" />
                    </div>

                    <div className="absolute -right-2 bottom-4 z-30 flex -space-x-1">
                        <div className="h-9 w-9 rounded-full border-2 border-stone-300 bg-stone-400 shadow-md transition-transform duration-300 group-hover:scale-110" />
                        <div className="mt-2 h-7 w-7 rounded-full border-2 border-stone-300 bg-stone-400 shadow-md" />
                    </div>
                </div>

                <div className="absolute bottom-4 z-30 flex h-32 w-60 items-center justify-center overflow-hidden rounded-b-[120px] border-x-4 border-b-4 border-stone-200 bg-gradient-to-b from-stone-50 to-stone-100 shadow-2xl">
                    <div className="absolute top-0 h-2 w-full bg-red-600" />
                    <div className="relative flex h-12 w-12 items-center justify-center rounded-full border-2 border-amber-400 bg-red-500 opacity-90 shadow-sm transition-transform duration-300 group-hover:scale-110">
                        <div className="absolute -top-1 h-3 w-4 rounded-t-full bg-red-600" />
                        <span className="font-mono text-[10px] font-black text-white">PUPUT</span>
                    </div>
                    <div className="absolute bottom-0 h-2 w-24 rounded-t-md bg-stone-300" />
                </div>
            </div>

            <div className="text-center">
                <span className="mb-1 block text-[10px] font-bold tracking-[0.3em] text-red-500 uppercase">
                    Citarasa Asli Solo
                </span>
                <h2 className="text-2xl font-black tracking-tight text-stone-800">RESEP TURUN TEMURUN</h2>
                <p className="mt-1 max-w-xs text-xs text-stone-400">
                    Arahkan kursor untuk melihat kehangatan menu racikan dapur kami.
                </p>
            </div>
        </div>
    );
}

function LoginPage() {
    const navigate = useNavigate();
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");
    const [loading, setLoading] = useState(false);

    const reportDate = new Intl.DateTimeFormat("en-CA", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    })
        .format(new Date())
        .replaceAll("-", "");

    const handleSubmit = async (event) => {
        event.preventDefault();
        setLoading(true);
        setError("");

        try {
            const response = await axios.post(
                "/api/login",
                { email, password },
                { withCredentials: true },
            );

            const token = response.data?.token;
            const user = response.data?.user;

            if (!token || !user?.role) {
                setError("Login berhasil tetapi data akun tidak lengkap.");

                return;
            }

            const homePath = getRoleHomePath(user.role);

            if (homePath === "/login") {
                setError("Role akun tidak dikenali.");

                return;
            }

            storeAuthentication(token, user);
            void prefetchLoginEssentials(token, user);
            navigate(homePath, { replace: true });
        } catch (loginError) {
            setError(
                loginError?.response?.data?.message ||
                    loginError?.response?.data?.errors?.email?.[0] ||
                    "Login gagal.",
            );
        } finally {
            setLoading(false);
        }
    };

    const storedToken = getStoredToken();
    const storedUser = getStoredUser();
    const storedHomePath = getRoleHomePath(storedUser?.role);

    if (storedToken && storedUser?.role && storedHomePath !== "/login") {
        return <Navigate to={storedHomePath} replace />;
    }

    return (
        <main className="relative min-h-screen overflow-hidden bg-[#FFFDF9]">
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(#e4d5b7_1.5px,transparent_1.5px)] bg-[length:32px_32px] opacity-40" />
            <div className="pointer-events-none absolute top-10 left-10 h-72 w-72 rounded-full bg-amber-100/30 blur-2xl" />
            <div className="pointer-events-none absolute right-10 bottom-10 h-96 w-96 rounded-full bg-red-100/20 blur-3xl" />

            <div className="relative z-10 mx-auto grid min-h-screen w-full max-w-6xl select-none grid-cols-1 items-center justify-center gap-12 px-6 py-16 lg:grid-cols-12">
                <NoodleBowlIllustration />

                <div className="col-span-1 flex w-full justify-center lg:col-span-7">
                    <div className="relative w-full max-w-md rounded-[32px] border-4 border-stone-700/50 bg-stone-800 p-3 pb-5 shadow-2xl">
                        <div className="absolute -top-4 left-1/2 z-30 flex h-8 w-32 -translate-x-1/2 items-center justify-center rounded-t-xl rounded-b-md border border-amber-300 bg-gradient-to-b from-amber-400 to-amber-500 shadow-md">
                            <div className="h-4 w-4 rounded-full border border-amber-700 bg-amber-600" />
                        </div>

                        <div className="relative w-full rounded-2xl border border-amber-100/50 bg-[#FFFDF9] p-6 md:p-8">
                            <div className="mb-6 flex items-center justify-between border-b border-stone-200/60 pb-3 font-mono text-[10px] tracking-wider text-stone-400">
                                <span>STRUK: #{reportDate}-AUTH</span>
                                <span className="font-bold text-red-500">KASIR UTAMA</span>
                            </div>

                            <div className="mb-8 text-center">
                                <div className="mb-2 inline-block rounded-md border border-amber-200 bg-amber-100 px-3 py-1 font-mono text-[9px] font-bold tracking-widest text-amber-900 uppercase">
                                    Sistem Akses Meja Masuk
                                </div>
                                <h1 className="text-3xl font-black tracking-tight text-stone-800">
                                    Mie Ayam <span className="text-red-500">Puput</span>
                                </h1>
                            </div>

                            {error ? (
                                <div role="alert" className="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-600">
                                    <div className="mb-1 flex items-center gap-1.5 font-bold tracking-wider uppercase">
                                        <svg fill="none" viewBox="0 0 24 24" strokeWidth="2.5" stroke="currentColor" className="h-4 w-4 text-red-500" aria-hidden="true">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        Validasi Gagal:
                                    </div>
                                    <p className="text-red-500/90">{error}</p>
                                </div>
                            ) : null}

                            <form className="space-y-5" onSubmit={handleSubmit}>
                                <div>
                                    <label htmlFor="email" className="mb-2 block text-xs font-bold tracking-wider text-stone-500 uppercase">
                                        01. Identitas Akun (Email)
                                    </label>
                                    <input
                                        id="email"
                                        type="email"
                                        value={email}
                                        onChange={(event) => setEmail(event.target.value)}
                                        className="w-full rounded-xl border border-stone-200 bg-stone-100/60 px-4 py-3 text-sm font-medium text-stone-800 placeholder-stone-400 transition-all duration-200 focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-400/10 focus:outline-none"
                                        placeholder="petugas@mieayampuput.com"
                                        autoComplete="email"
                                        required
                                        autoFocus
                                    />
                                </div>

                                <div>
                                    <label htmlFor="password" className="mb-2 block text-xs font-bold tracking-wider text-stone-500 uppercase">
                                        02. Kunci Keamanan (Password)
                                    </label>
                                    <input
                                        id="password"
                                        type="password"
                                        value={password}
                                        onChange={(event) => setPassword(event.target.value)}
                                        className="w-full rounded-xl border border-stone-200 bg-stone-100/60 px-4 py-3 text-sm text-stone-800 placeholder-stone-400 transition-all duration-200 focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-400/10 focus:outline-none"
                                        placeholder="••••••••"
                                        autoComplete="current-password"
                                        required
                                    />
                                </div>

                                <label className="flex cursor-pointer select-none items-center gap-2 pt-1 text-xs font-semibold text-stone-500">
                                    <input type="checkbox" defaultChecked className="h-4 w-4 accent-red-600" />
                                    Ingat akun saya di komputer meja ini
                                </label>

                                <div className="pt-2">
                                    <button
                                        type="submit"
                                        disabled={loading}
                                        className="flex w-full items-center justify-center space-x-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 py-3.5 text-xs font-bold tracking-widest text-white shadow-lg shadow-red-500/20 transition-all duration-200 hover:from-red-600 hover:to-red-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <span>{loading ? "MEMPROSES SAJIAN..." : "PROSES SAJIAN & MASUK"}</span>
                                        <svg fill="none" viewBox="0 0 24 24" strokeWidth="2.5" stroke="currentColor" className="h-4 w-4" aria-hidden="true">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="m11.25 4.5 7.5 7.5-7.5 7.5M3 12h15" />
                                        </svg>
                                    </button>
                                </div>
                            </form>

                            <div className="mt-8 border-t-2 border-dashed border-stone-200 pt-4 text-center font-mono text-[10px] tracking-widest text-stone-400 uppercase">
                                Selamat Bertugas &amp; Jaga Kualitas!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    );
}

export default LoginPage;
