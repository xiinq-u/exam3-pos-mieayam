function ProfitLoss({ report }) {
    const formatNumber = (value) =>
        Number(value || 0).toLocaleString("id-ID", {
            maximumFractionDigits: 0,
        });

    if (!report) {
        return (
            <section className="rounded-2xl bg-white p-6 shadow">
                <p className="text-sm text-slate-500">
                    Memuat laporan laba-rugi...
                </p>
            </section>
        );
    }

    const rows = [
        { label: "Penjualan", value: report.sales },
        { label: "Refund", value: report.refunds, deduction: true },
        { label: "Pemasukan lain", value: report.other_income },
        {
            label: "Harga pokok penjualan",
            value: report.cost_of_goods_sold,
            deduction: true,
        },
        { label: "Laba kotor", value: report.gross_profit, total: true },
        { label: "Pengeluaran", value: report.expenses, deduction: true },
        { label: "Laba bersih", value: report.net_profit, total: true },
    ];

    return (
        <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow">
            <div className="border-b border-slate-200 px-6 py-5">
                <p className="font-mono text-[10px] font-bold tracking-widest text-rose-600 uppercase">
                    Ringkasan Keuangan
                </p>
                <h2 className="mt-1 text-xl font-bold text-slate-800">
                    Laporan Laba-Rugi
                </h2>
                <p className="mt-1 text-sm text-slate-500">
                    Periode {report.start_date} sampai {report.end_date}
                </p>
            </div>

            <div className="overflow-x-auto px-6 pb-6">
                <table className="mt-6 w-full min-w-md border-collapse text-sm">
                    <thead>
                        <tr className="bg-slate-100">
                            <th className="px-4 py-3 text-left font-semibold text-slate-700">
                                Keterangan
                            </th>
                            <th className="px-4 py-3 text-right font-semibold text-slate-700">
                                Nilai (Rp)
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((row) => (
                            <tr
                                key={row.label}
                                className={`border-b border-slate-200 ${
                                    row.total
                                        ? "border-t-2 border-t-slate-700 font-bold"
                                        : ""
                                }`}
                            >
                                <td className="px-4 py-3 text-slate-700">
                                    {row.label}
                                </td>
                                <td
                                    className={`px-4 py-3 text-right ${
                                        row.total
                                            ? "text-slate-900"
                                            : "text-slate-700"
                                    }`}
                                >
                                    {row.deduction
                                        ? `(${formatNumber(row.value)})`
                                        : formatNumber(row.value)}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </section>
    );
}

export default ProfitLoss;
