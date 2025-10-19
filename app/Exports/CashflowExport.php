<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use App\Services\Tenants\CashflowService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class CashflowExport
    implements FromArray,
    ShouldAutoSize,
    WithHeadings,
    WithCustomStartCell,
    WithEvents
{
    use Exportable;

    protected array $results;

    public function __construct(
        public CashflowService $cashflowService,
        public array $data
    )
    {
        $this->results = $this->cashflowService->generate($this->data);
    }

    public function headings(): array
    {
        return [

        ];
    }

    public function array(): array
    {
        $results = $this->results;
        $reports = $results['reports'];

        $data = [];

        foreach ($reports as $paymentMethod => $cashflows) {
            $data[] = [
                $paymentMethod,
                '',
                '',
                '',
                '',
            ];

            $data[] = [
                __('Date'),
                __('Description'),
                __('Debit'),
                __('Credit'),
                __('Balance'),
            ];

            foreach ($cashflows['rows'] as $cashflow) {
                $data[] = [
                    $cashflow->date,
                    $cashflow->note,
                    (float) str_replace(',', '', $cashflow->debit),
                    (float) str_replace(',', '', $cashflow->credit),
                    (float) str_replace(',', '', $cashflow->saldo),
                ];
            }

            $data[] = [
                __('Total'),
                '',
                (float) str_replace(',', '', $cashflows['total_debit']),
                (float) str_replace(',', '', $cashflows['total_credit']),
                (float) str_replace(',', '', $cashflows['ending_balance']),
            ];
        }

        return $data;
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function registerEvents(): array
    {
        $reports = $this->results['reports'];
        $header = $this->results['header'];

        return [
            AfterSheet::class => function(AfterSheet $event) use ($header, $reports) {
                $sheet = $event->sheet->getDelegate();

                /** Header Row */
                $sheet->setCellValue('A1', 'Arus Kas');
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

                // Set font bold for table headers
                $startRow = 6;
                $sheet->getStyle('A' . ($startRow + 1) . ':E' . ($startRow + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                $sheet->mergeCells('A' . ($startRow + 1) . ':E' . ($startRow + 1));

                $sheet->getStyle('A' . ($startRow + 2) . ':E' . ($startRow + 2))->applyFromArray([
                    // set background color to gray
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEEEEEE'],
                    ],
                ]);

                foreach ($reports as $paymentMethod => $cashflows) {
                    $startRow += count($cashflows['rows']) + 4;

                    // set font bold for total row
                    $sheet->getStyle('A' . $startRow . ':E' . $startRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                    // merge total row cells
                    $sheet->mergeCells('A' . $startRow . ':E' . $startRow);

                    $startRow += 1;
                    // don't set if row below the last row
                    if ($paymentMethod === array_key_last($reports->toArray())) {
                        break;
                    }


                    $sheet->getStyle('A' . $startRow . ':E' . $startRow)->applyFromArray([
                        // set background color to gray
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFEEEEEE'],
                        ],
                    ]);
                }
            },
        ];
    }
}
