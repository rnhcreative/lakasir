<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Services\Tenants\ReturSellingReportService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ReturSellingReportExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    use Exportable;

    protected array $results;

    public function __construct(
        public ReturSellingReportService $returSellingReportService,
        public array $data
    )
    {
        $this->results = $this->returSellingReportService->generate($this->data);
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('Selling Code'),
            __('Item'),
            __('Item yang ditukar'),
            __('Qty'),
            __('Refund amount'),
            __('Additional amount'),
        ];
    }

    public function array(): array
    {
        $results = $this->results;

        $data = [];

        foreach ($results['reports'] as $key => $report) {
            $data[] = [
                $report['date'],
                $report['code'],
                $report['retur_item_name'],
                $report['new_item_name'],
                $report['qty'],
                (float) str_replace('.', '', $report['refund_amount']),
                (float) str_replace('.', '', $report['additional_amount']),
            ];
        }

        return $data;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function registerEvents(): array
    {
        $header = $this->results['header'];
        $footer = $this->results['footer'];

        return [
            AfterSheet::class => function(AfterSheet $event) use ($header, $footer) {
                $sheet = $event->sheet->getDelegate();

                /** Header Row */
                $sheet->setCellValue('A1', 'Laporan Retur Penjualan');
                $sheet->mergeCells('A1:G1');

                $sheet->setCellValue('A2', $header['shop_name']);
                $sheet->mergeCells('A2:G2');

                $sheet->setCellValue('A4', __('Period') . ': ' . $header['start_date'] . ' - ' . $header['end_date']);
                $sheet->mergeCells('A4:G4');

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

                /** Footer Row */
                $lastRow = $sheet->getHighestDataRow();
                $sheet->setCellValue('A' . ($lastRow + 1), 'Total');
                $sheet->setCellValue('F' . ($lastRow + 1), (float) str_replace('.', '', $footer['total_refund_amount']));
                $sheet->setCellValue('G' . ($lastRow + 1), (float) str_replace('.', '', $footer['total_additional_amount']));

                $sheet->mergeCells('A' . ($lastRow + 1) . ':E' . ($lastRow + 1));

                $sheet->getStyle('A' . ($lastRow + 1) . ':G' . ($lastRow + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                /** EOF Footer Row */
            },
        ];
    }
}
