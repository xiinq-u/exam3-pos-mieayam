function DashboardStatCard({ label, value, detail, tone = "stone" }) {
    const tones = {
        stone: "border-stone-200 bg-white text-stone-900",
        red: "border-red-200 bg-red-50 text-red-700",
        amber: "border-amber-200 bg-amber-50 text-amber-700",
        emerald: "border-emerald-200 bg-emerald-50 text-emerald-700",
        sky: "border-sky-200 bg-sky-50 text-sky-700",
    };

    return (
        <article className={`rounded-2xl border p-5 shadow-sm ${tones[tone] ?? tones.stone}`}>
            <p className="text-[10px] font-black tracking-widest uppercase opacity-65">{label}</p>
            <p className="mt-3 text-2xl font-black tracking-tight">{value}</p>
            {detail ? <p className="mt-1 text-xs font-semibold opacity-70">{detail}</p> : null}
        </article>
    );
}

export default DashboardStatCard;
