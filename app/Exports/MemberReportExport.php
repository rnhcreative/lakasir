<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Services\Tenants\MemberReportService;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class MemberReportExport
    implements FromArray,
    ShouldAutoSize,
    WithHeadings,
    WithCustomStartCell,
    WithEvents
{
    use Exportable;

    protected array $results;

    public function __construct(
        public MemberReportService $memberReportService,
        public array $data
    )
    {
        $this->results = $this->memberReportService->generate($this->data);
    }

    public function headings(): array
    {
        return [
            __('Name'),
            __('Contact'),
            'Total ' . __('Transaction'),
            'Total ' . __('Purchasing'),
        ];
    }

    public function array(): array
    {
        $results = $this->results;
        $reports = $results['reports'];

        $data = [];

        foreach ($reports as $report) {
            $data[] = [
                $report['name'],
                $report['email'],
                (int) str_replace(',', '', $report['total_transaction']),
                (float) str_replace(',', '', $report['total_selling']),
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
                $sheet->setCellValue('A1', 'Laporan Member');
                $sheet->mergeCells('A1:D1');

                $sheet->setCellValue('A2', $header['shop_name']);
                $sheet->mergeCells('A2:D2');

                $sheet->setCellValue('A4', __('Period') . ': ' . $header['start_date'] . ' - ' . $header['end_date']);
                $sheet->mergeCells('A4:D4');

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
                $sheet->setCellValue('C' . ($lastRow + 1), (float) str_replace(',', '', $footer['grand_total_transaction']));
                $sheet->setCellValue('D' . ($lastRow + 1), (float) str_replace(',', '', $footer['grand_total_selling']));

                $sheet->mergeCells('A' . ($lastRow + 1) . ':B' . ($lastRow + 1));

                $sheet->getStyle('A' . ($lastRow + 1) . ':D' . ($lastRow + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                /** EOF Footer Row */
            },
        ];
    }
}
