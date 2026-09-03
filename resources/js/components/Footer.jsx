import { useLocation } from "react-router-dom";

function Footer() {
    const location = useLocation();
    const isOrderDetailPage = /^\/orders\/[^/]+$/.test(location.pathname);

    if (isOrderDetailPage) {
        return null;
    }

    return (
        <footer className="relative z-10 mx-auto mb-8 w-full max-w-6xl select-none px-6">
            <div className="relative w-full overflow-hidden rounded-2xl border-2 border-dashed border-stone-300/80 bg-[#FFFDF9] p-5 text-center shadow-lg">
                <div className="absolute top-1/2 left-3 flex -translate-y-1/2 flex-col space-y-1.5 opacity-40">
                    <div className="h-2 w-2 rounded-full bg-stone-300" />
                    <div className="h-2 w-2 rounded-full bg-stone-300" />
                </div>
                <div className="absolute top-1/2 right-3 flex -translate-y-1/2 flex-col space-y-1.5 opacity-40">
                    <div className="h-2 w-2 rounded-full bg-stone-300" />
                    <div className="h-2 w-2 rounded-full bg-stone-300" />
                </div>

                <div className="flex flex-col items-center justify-between gap-4 px-4 sm:flex-row">
                    <div className="text-center sm:text-left">
                        <span className="mb-0.5 block font-mono text-[9px] font-bold tracking-widest text-red-500 uppercase">
                            Sistem Kasir v2.0
                        </span>
                        <p className="text-xs font-bold tracking-tight text-stone-700">
                            © {new Date().getFullYear()} Mie Ayam Puput. All rights reserved.
                        </p>
                    </div>

                    <div className="text-center font-mono text-[10px] tracking-wider text-stone-400 sm:text-right">
                        <span>TERIMA KASIH ATAS KUNJUNGAN ANDA</span>
                        <span className="mt-0.5 block text-[9px] text-stone-300">
                            Sastra Rasa • Racikan Solo Asli
                        </span>
                    </div>
                </div>
            </div>
        </footer>
    );
}

export default Footer;
