<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Services\Tenants\SellingReportService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class SellingReportExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    use Exportable;

    protected array $results;

    public function __construct(
        public SellingReportService $sellingReportService,
        public array $data
    )
    {
        $this->results = $this->sellingReportService->generate($this->data);
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('Selling'),
            __('Transaction'),
            __('Item'),
            __('Discount'),
            __('Profit'),
        ];
    }

    public function array(): array
    {
        $results = $this->results;

        $data = [];

        foreach ($results['reports'] as $key => $report) {
            $data[] = [
                $report['date'],
                (float) str_replace(',', '', $report['total_selling']),
                $report['total_transaction'],
                $report['total_item'],
                (float) str_replace(',', '', $report['total_discount']),
                (float) str_replace(',', '', $report['total_profit']),
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
                $sheet->setCellValue('A1', 'Laporan Penjualan');
                $sheet->mergeCells('A1:F1');

                $sheet->setCellValue('A2', $header['shop_name']);
                $sheet->mergeCells('A2:F2');

                $sheet->setCellValue('A4', __('Period') . ': ' . $header['start_date'] . ' - ' . $header['end_date']);
                $sheet->mergeCells('A4:F4');

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
                $sheet->setCellValue('B' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_selling']));
                $sheet->setCellValue('C' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_transaction']));
                $sheet->setCellValue('D' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_item']));
                $sheet->setCellValue('E' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_discount']));
                $sheet->setCellValue('F' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_profit']));

                $sheet->getStyle('A6:F6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $sheet->getStyle('A' . ($lastRow + 1) . ':F' . ($lastRow + 1))->applyFromArray([
                    // add background color
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFEEEEEE', // soft gray
                        ],
                    ],
                ]);
                /** EOF Footer Row */
            },
        ];
    }
}
