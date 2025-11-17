<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTemplate;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Generate PDF for document using template.
     */
    public function generateDocumentPdf(Document $document, ?int $templateId = null): string
    {
        // Load document with relations
        $document->load(['tenant', 'customer', 'lines.product']);

        // Get template
        $template = $templateId
            ? DocumentTemplate::find($templateId)
            : DocumentTemplate::where('tenant_id', $document->tenant_id)
                ->where('type', $document->type)
                ->default()
                ->first();

        // Fallback to creating basic template
        if (!$template) {
            $template = $this->createDefaultTemplate($document);
        }

        // Render HTML with document data
        $html = $this->renderDocumentHtml($document, $template);

        // Generate PDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Download PDF for document.
     */
    public function downloadDocumentPdf(Document $document, ?int $templateId = null): \Illuminate\Http\Response
    {
        $pdfContent = $this->generateDocumentPdf($document, $templateId);

        $filename = $this->generateFilename($document);

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Stream PDF inline in browser.
     */
    public function streamDocumentPdf(Document $document, ?int $templateId = null): \Illuminate\Http\Response
    {
        $pdfContent = $this->generateDocumentPdf($document, $templateId);

        $filename = $this->generateFilename($document);

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=\"{$filename}\"");
    }

    /**
     * Save PDF to storage.
     */
    public function saveDocumentPdf(Document $document, ?int $templateId = null): string
    {
        $pdfContent = $this->generateDocumentPdf($document, $templateId);

        $filename = $this->generateFilename($document);
        $path = "documents/{$document->tenant_id}/{$filename}";

        \Storage::put($path, $pdfContent);

        return $path;
    }

    /**
     * Render HTML for document using template.
     */
    protected function renderDocumentHtml(Document $document, DocumentTemplate $template): string
    {
        $html = $template->body_html ?? $template->getDefaultTemplate();

        // Render document lines
        $linesHtml = $this->renderDocumentLines($document);
        $html = str_replace('{{lines}}', $linesHtml, $html);

        // Use template's render method for variable replacement
        return $template->render($document);
    }

    /**
     * Render document lines as HTML table rows.
     */
    protected function renderDocumentLines(Document $document): string
    {
        $html = '';

        foreach ($document->lines as $line) {
            $totalHT = $line->quantity * $line->unit_price;

            $html .= "
            <tr>
                <td>{$line->product_name}</td>
                <td class='text-center'>{$line->quantity}</td>
                <td class='text-right'>" . number_format($line->unit_price, 3, ',', ' ') . " TND</td>
                <td class='text-right'>{$line->tva_rate}%</td>
                <td class='text-right'>" . number_format($totalHT, 3, ',', ' ') . " TND</td>
            </tr>";
        }

        return $html;
    }

    /**
     * Generate filename for PDF.
     */
    protected function generateFilename(Document $document): string
    {
        $type = strtoupper($document->type);
        $number = str_replace('/', '-', $document->number);
        $date = $document->date?->format('Ymd');

        return "{$type}_{$number}_{$date}.pdf";
    }

    /**
     * Create default template for document type.
     */
    protected function createDefaultTemplate(Document $document): DocumentTemplate
    {
        return new DocumentTemplate([
            'tenant_id' => $document->tenant_id,
            'name' => 'Template par défaut',
            'type' => $document->type,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Generate PDF for multiple documents (bulk export).
     */
    public function generateBulkPdf(array $documentIds): string
    {
        $documents = Document::with(['tenant', 'customer', 'lines'])->findMany($documentIds);

        $html = '';
        foreach ($documents as $document) {
            $template = DocumentTemplate::where('tenant_id', $document->tenant_id)
                ->where('type', $document->type)
                ->default()
                ->first() ?? $this->createDefaultTemplate($document);

            $html .= $template->render($document);
            $html .= '<div style="page-break-after: always;"></div>';
        }

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
