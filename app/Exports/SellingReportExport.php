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
            __('Selling Code'),
            __('Product Name'),
            __('Price'),
            __('Qty'),
            __('Sub Total'),
            __('Discount'),
            __('Total'),
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
                $report['name'],
                (float) str_replace(',', '', $report['selling_price']),
                $report['qty'],
                (float) str_replace(',', '', $report['selling']),
                (float) str_replace(',', '', $report['discount_price']),
                (float) str_replace(',', '', $report['total_after_discount']),
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
                $sheet->setCellValue('E' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_qty']));
                $sheet->setCellValue('F' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_before_discount']));
                $sheet->setCellValue('G' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_all_discount']));
                $sheet->setCellValue('H' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_after_discount']));

                $sheet->mergeCells('A' . ($lastRow + 1) . ':D' . ($lastRow + 1));

                $sheet->getStyle('A' . ($lastRow + 1) . ':H' . ($lastRow + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                /** EOF Footer Row */
            },
        ];
    }
}
