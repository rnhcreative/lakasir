<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Services\Tenants\ProfitLossService;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ProfitLossExport
    implements FromArray,
    ShouldAutoSize,
    WithHeadings,
    WithCustomStartCell,
    WithEvents
{
    use Exportable;

    protected array $results;

    public function __construct(
        public ProfitLossService $profitLossService,
        public array $data
    )
    {
        $this->results = $this->profitLossService->generate($this->data);
    }

    public function headings(): array
    {
        return [
            __('Description'),
            __('Amount') . ' (Rp)',
        ];
    }

    public function array(): array
    {
        $results = $this->results;
        $reports = $results['reports'];

        $data = [];

        $data[] = [
            __('Pendapatan Penjualan Tunai'),
            str_replace('.', '', $reports['total_non_credit_selling']),
        ];

        $data[] = [
            __('Pendapatan Penjualan Kredit (Piutang)'),
            str_replace('.', '', $reports['total_credit_selling']),
        ];

        $data[] = [
            __('Total Penjualan Kotor'),
            str_replace('.', '', $reports['total_gross_selling']),
        ];

        $data[] = [
            __('(-) Retur Penjualan'),
            str_replace('.', '', $reports['total_retur_selling']),
        ];

        $data[] = [
            __('Total Penjualan Bersih'),
            str_replace('.', '', $reports['total_net_selling']),
        ];

        $data[] = [
            __('(-) Harga Pokok Penjualan'),
            str_replace('.', '', $reports['hpp']),
        ];

        $data[] = [
            __('Laba Kotor'),
            str_replace('.', '', $reports['total_gross_profit']),
        ];

        $data[] = [
            __('(-) Biaya Operasional (Pengeluaran)'),
            str_replace('.', '', $reports['total_expense']),
        ];

        $data[] = [
            __('Laba Bersih'),
            str_replace('.', '', $reports['total_net_profit']),
        ];

        return $data;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function registerEvents(): array
    {
        $reports = $this->results['reports'];
        $header = $this->results['header'];

        return [
            AfterSheet::class => function(AfterSheet $event) use ($header, $reports) {
                $sheet = $event->sheet->getDelegate();

                /** Header Row */
                $sheet->setCellValue('A1', 'Laporan Laba Rugi');
                $sheet->mergeCells('A1:B1');

                $sheet->setCellValue('A2', $header['shop_name']);
                $sheet->mergeCells('A2:B2');

                $sheet->setCellValue('A4', __('Period') . ': ' . $header['start_date'] . ' - ' . $header['end_date']);
                $sheet->mergeCells('A4:B4');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A4')->applyFromArray([
                    'font' => [
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);

                /** EOF Header Row */
            },
        ];
    }
}
