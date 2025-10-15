<?php

namespace App\Http\Controllers;

use App\Services\Tenants\ReceivableReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceivableReportController
{
    public function __invoke(Request $request, ReceivableReportService $receivableReportService)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'type' => 'required|in:all,debt,payment',
        ]);

        $reportData = $receivableReportService->generate($request->all());
        $reports = $reportData['reports'];
        $footer = $reportData['footer'];
        $header = $reportData['header'];

        $pdf = Pdf::loadView('reports.receivable', compact('reports', 'footer', 'header'))
            ->setPaper('a4', 'landscape');
        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->getCanvas();
        $canvas->page_text(720, 570, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', null, 10, [0, 0, 0]);

        if ($request->ajax()) {
            return $pdf->download();
        }

        return $pdf->stream();
    }
}
