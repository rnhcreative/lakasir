<?php

namespace App\Exports;

use App\Services\Tenants\ExpenseReportService;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExpenseReportExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    use Exportable;

    protected array $results;

    public function __construct(
        public ExpenseReportService $expenseReportService,
        public array $data
    )
    {
        $this->results = $this->expenseReportService->generate($this->data);
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('Type'),
            __('Amount'),
            __('Note'),
            __('Payment Method'),
        ];
    }

    public function array(): array
    {
        $results = $this->results;

        $data = [];

        foreach ($results['reports'] as $key => $report) {
            $data[] = [
                $report['date'],
                $report['type'],
                (float) str_replace('.', '', $report['amount']),
                $report['note'],
                $report['payment_method'],
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
                $sheet->setCellValue('A1', 'Laporan Pengeluaran');
                $sheet->mergeCells('A1:E1');

                $sheet->setCellValue('A2', $header['shop_name']);
                $sheet->mergeCells('A2:E2');

                $sheet->setCellValue('A4', __('Period') . ': ' . $header['start_date'] . ' - ' . $header['end_date']);
                $sheet->mergeCells('A4:E4');

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
                $sheet->setCellValue('C' . ($lastRow + 1), (float) str_replace('.', '', $footer['total']));
                $sheet->setCellValue('D' . ($lastRow + 1), '');
                $sheet->setCellValue('E' . ($lastRow + 1), '');

                $sheet->mergeCells('A' . ($lastRow + 1) . ':B' . ($lastRow + 1));

                $sheet->getStyle('A' . ($lastRow + 1) . ':E' . ($lastRow + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                /** EOF Footer Row */
            },
        ];
    }
}
