<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromArray, WithColumnFormatting, WithHeadings, WithStyles
{
    /**
     * @param  array{start_date: string, end_date: string, sales: float, refunds: float, other_income: float, expenses: float, cost_of_goods_sold: float, gross_profit: float, net_profit: float}  $report
     */
    public function __construct(private readonly array $report) {}

    public function headings(): array
    {
        return ['Keterangan', 'Nilai (Rp)'];
    }

    public function array(): array
    {
        return [
            ['Penjualan', $this->report['sales']],
            ['Refund', -$this->report['refunds']],
            ['Pemasukan lain', $this->report['other_income']],
            ['Harga pokok penjualan', -$this->report['cost_of_goods_sold']],
            ['Laba kotor', $this->report['gross_profit']],
            ['Pengeluaran', -$this->report['expenses']],
            ['Laba bersih', $this->report['net_profit']],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
        ];
    }
}
