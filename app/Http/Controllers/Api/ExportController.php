<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\ExcelService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        private PdfService $pdfService,
        private ExcelService $excelService
    ) {}

    /**
     * Export document as PDF.
     */
    public function exportDocumentPdf(int $id, Request $request)
    {
        $document = Document::with(['tenant', 'customer', 'lines'])->findOrFail($id);

        $this->authorize('view', $document);

        $templateId = $request->get('template_id');

        if ($request->get('action') === 'download') {
            return $this->pdfService->downloadDocumentPdf($document, $templateId);
        }

        // Stream inline by default
        return $this->pdfService->streamDocumentPdf($document, $templateId);
    }

    /**
     * Export multiple documents as single PDF.
     */
    public function exportBulkPdf(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);

        $pdfContent = $this->pdfService->generateBulkPdf($request->document_ids);

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="documents_' . date('Ymd') . '.pdf"');
    }

    /**
     * Export sales report as Excel.
     */
    public function exportSalesExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return $this->excelService->exportSalesReport(
            $request->start_date,
            $request->end_date
        );
    }

    /**
     * Export stock report as Excel.
     */
    public function exportStockExcel()
    {
        return $this->excelService->exportStockReport();
    }

    /**
     * Export CRM contacts as Excel.
     */
    public function exportContactsExcel(Request $request)
    {
        $filters = $request->only(['type', 'assigned_to', 'source']);

        return $this->excelService->exportContacts($filters);
    }

    /**
     * Export opportunities as Excel.
     */
    public function exportOpportunitiesExcel(Request $request)
    {
        $filters = $request->only(['status', 'pipeline_id', 'assigned_to']);

        return $this->excelService->exportOpportunities($filters);
    }

    /**
     * Export employees as Excel.
     */
    public function exportEmployeesExcel()
    {
        return $this->excelService->exportEmployees();
    }

    /**
     * Export audit logs as Excel.
     */
    public function exportAuditLogsExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        return $this->excelService->exportAuditLogs(
            $request->start_date,
            $request->end_date
        );
    }
}
