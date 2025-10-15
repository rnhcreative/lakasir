<?php

namespace App\Exports;

use App\Services\Tenants\ReceivableReportService;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ReceivableReportExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    use Exportable;

    protected array $results;

    public function __construct(
        public ReceivableReportService $receivableReportService,
        public array $data
    )
    {
        $this->results = $this->receivableReportService->generate($this->data);
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('Name'),
            __('Contact'),
            __('Type'),
            __('Amount'),
            __('Selling Code'),
            __('Payment Method'),
        ];
    }

    public function array(): array
    {
        $results = $this->results;

        $data = [];

        foreach ($results['reports'] as $row) {
            $data[] = [
                $row['date'],
                $row['member_name'],
                $row['member_email'],
                $row['type'] == 'debt' ? 'Utang' : 'Pembayaran',
                (float) str_replace(',', '', $row['amount']),
                $row['selling_code'],
                $row['payment_method'],
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
                $sheet->setCellValue('A1', 'Laporan Piutang');
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
                $sheet->setCellValue('A' . ($lastRow + 1), 'Total Piutang');
                $sheet->setCellValue('G' . ($lastRow + 1), (float) str_replace(',', '', $footer['total_debt']));
                $sheet->mergeCells('A' . ($lastRow + 1) . ':F' . ($lastRow + 1));
                $sheet->getStyle('A' . ($lastRow + 1) . ':G' . ($lastRow + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $sheet->setCellValue('A' . ($lastRow + 2), 'Total Pembayaran');
                $sheet->setCellValue('G' . ($lastRow + 2), (float) str_replace(',', '', $footer['total_payment']));
                $sheet->mergeCells('A' . ($lastRow + 2) . ':F' . ($lastRow + 2));
                $sheet->getStyle('A' . ($lastRow + 2) . ':G' . ($lastRow + 2))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $sheet->setCellValue('A' . ($lastRow + 3), 'Total Sisa Utang');
                $sheet->setCellValue('G' . ($lastRow + 3), (float) str_replace(',', '', $footer['total_rest_debt']));
                $sheet->mergeCells('A' . ($lastRow + 3) . ':F' . ($lastRow + 3));
                $sheet->getStyle('A' . ($lastRow + 3) . ':G' . ($lastRow + 3))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                /** EOF Footer Row */
            },
        ];
    }
}
