<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Services\Tenants\ProductReportService;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;

class ProductReportExport implements
    FromArray, WithHeadings, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    use Exportable;
    protected array $results;

    public function __construct(
        public ProductReportService $productReportService,
        public array $data)
    {
        $this->results = $this->productReportService->generate($this->data);
    }

    public function headings(): array
    {
        return [
            __('Product Name'),
            __('SKU'),
            __('Selling'),
            __('Transaction'),
            __('Item'),
            __('Discount'),
            __('Profit'),
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $results = $this->results;
        $reports = $results['reports'];

        $data = [];
        foreach ($reports as $report) {
            $data[] = [
                $report['product_name'],
                $report['product_sku'],
                (float) str_replace('.', '', $report['total_selling']),
                $report['total_transaction'],
                $report['total_item'],
                (int) str_replace('.', '', $report['total_discount']),
                (float) str_replace('.', '', $report['total_profit']),
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
                $sheet->setCellValue('A1', 'Laporan Produk');
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
                $sheet->setCellValue('C' . ($lastRow + 1), (float) str_replace('.', '', $footer['total_selling']));
                $sheet->setCellValue('D' . ($lastRow + 1), (float) str_replace('.', '', $footer['total_transaction']));
                $sheet->setCellValue('E' . ($lastRow + 1), (float) str_replace('.', '', $footer['total_item']));
                $sheet->setCellValue('F' . ($lastRow + 1), (float) str_replace('.', '', $footer['total_discount']));
                $sheet->setCellValue('G' . ($lastRow + 1), (float) str_replace('.', '', $footer['total_profit']));

                $sheet->mergeCells('A' . ($lastRow + 1) . ':B' . ($lastRow + 1));

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
