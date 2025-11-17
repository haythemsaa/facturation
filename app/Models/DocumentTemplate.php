<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'header_html',
        'footer_html',
        'body_html',
        'styles',
        'settings',
        'is_default',
        'is_active',
        'preview_image',
    ];

    protected $casts = [
        'styles' => 'array',
        'settings' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Render template with document data.
     */
    public function render(Document $document): string
    {
        $html = $this->body_html ?? $this->getDefaultTemplate();

        // Variables disponibles dans le template
        $variables = [
            '{{document.number}}' => $document->number,
            '{{document.date}}' => $document->date?->format('d/m/Y'),
            '{{document.due_date}}' => $document->due_date?->format('d/m/Y'),
            '{{document.subtotal}}' => number_format($document->subtotal, 3, ',', ' '),
            '{{document.total_ht}}' => number_format($document->total_ht, 3, ',', ' '),
            '{{document.total_tva}}' => number_format($document->total_tva, 3, ',', ' '),
            '{{document.timbre_fiscal}}' => number_format($document->timbre_fiscal, 3, ',', ' '),
            '{{document.total_ttc}}' => number_format($document->total_ttc, 3, ',', ' '),
            '{{tenant.name}}' => $document->tenant->company_name ?? $document->tenant->name,
            '{{tenant.address}}' => $document->tenant->address,
            '{{tenant.city}}' => $document->tenant->city,
            '{{tenant.tax_id}}' => $document->tenant->tax_id,
            '{{tenant.phone}}' => $document->tenant->phone,
            '{{tenant.email}}' => $document->tenant->email,
            '{{customer.name}}' => $document->customer?->name ?? '',
            '{{customer.address}}' => $document->customer?->address ?? '',
            '{{customer.city}}' => $document->customer?->city ?? '',
            '{{customer.tax_id}}' => $document->customer?->tax_id ?? '',
        ];

        // Replace variables
        $html = str_replace(array_keys($variables), array_values($variables), $html);

        // Add header/footer
        $fullHtml = $this->wrapWithLayout($html, $document);

        return $fullHtml;
    }

    /**
     * Wrap content with header/footer layout.
     */
    protected function wrapWithLayout(string $content, Document $document): string
    {
        $header = $this->header_html ?? $this->getDefaultHeader($document);
        $footer = $this->footer_html ?? $this->getDefaultFooter($document);
        $styles = $this->getStyles();

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>{$document->number}</title>
            {$styles}
        </head>
        <body>
            <div class='page'>
                {$header}
                <div class='content'>
                    {$content}
                </div>
                {$footer}
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get CSS styles.
     */
    protected function getStyles(): string
    {
        $customStyles = is_array($this->styles) ? implode("\n", $this->styles) : ($this->styles ?? '');

        return "<style>
            body { font-family: 'Arial', sans-serif; font-size: 12px; margin: 0; padding: 20px; }
            .page { max-width: 800px; margin: 0 auto; }
            .header { margin-bottom: 30px; }
            .content { margin: 20px 0; }
            .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f5f5f5; font-weight: bold; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .totals { margin-top: 20px; }
            .totals table { width: 300px; margin-left: auto; }
            {$customStyles}
        </style>";
    }

    /**
     * Get default header HTML.
     */
    protected function getDefaultHeader(Document $document): string
    {
        $tenant = $document->tenant;
        $type = $this->getDocumentTypeLabel($document->type);

        return "
        <div class='header'>
            <h1>{$type} {$document->number}</h1>
            <div style='display: flex; justify-content: space-between;'>
                <div>
                    <strong>{$tenant->company_name ?? $tenant->name}</strong><br>
                    {$tenant->address}<br>
                    {$tenant->postal_code} {$tenant->city}<br>
                    MF: {$tenant->tax_id}<br>
                    Tél: {$tenant->phone}<br>
                    Email: {$tenant->email}
                </div>
                <div style='text-align: right;'>
                    <strong>Client:</strong><br>
                    {$document->customer?->name}<br>
                    {$document->customer?->address}<br>
                    {$document->customer?->city}<br>
                    MF: {$document->customer?->tax_id}
                </div>
            </div>
            <div style='margin-top: 20px;'>
                <strong>Date:</strong> {$document->date?->format('d/m/Y')}<br>
                " . ($document->due_date ? "<strong>Échéance:</strong> {$document->due_date->format('d/m/Y')}" : "") . "
            </div>
        </div>";
    }

    /**
     * Get default footer HTML.
     */
    protected function getDefaultFooter(Document $document): string
    {
        $tenant = $document->tenant;

        return "
        <div class='footer'>
            <div class='text-center'>
                {$tenant->company_name} - {$tenant->address}, {$tenant->city}<br>
                Tél: {$tenant->phone} - Email: {$tenant->email} - MF: {$tenant->tax_id}
            </div>
        </div>";
    }

    /**
     * Get default body template.
     */
    protected function getDefaultTemplate(): string
    {
        return "
        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class='text-center'>Quantité</th>
                    <th class='text-right'>Prix U. HT</th>
                    <th class='text-right'>TVA %</th>
                    <th class='text-right'>Total HT</th>
                </tr>
            </thead>
            <tbody>
                {{lines}}
            </tbody>
        </table>

        <div class='totals'>
            <table>
                <tr>
                    <td><strong>Total HT:</strong></td>
                    <td class='text-right'>{{document.total_ht}} TND</td>
                </tr>
                <tr>
                    <td><strong>TVA:</strong></td>
                    <td class='text-right'>{{document.total_tva}} TND</td>
                </tr>
                <tr>
                    <td><strong>Timbre Fiscal:</strong></td>
                    <td class='text-right'>{{document.timbre_fiscal}} TND</td>
                </tr>
                <tr style='font-size: 14px; font-weight: bold;'>
                    <td><strong>Total TTC:</strong></td>
                    <td class='text-right'>{{document.total_ttc}} TND</td>
                </tr>
            </table>
        </div>";
    }

    /**
     * Get document type label in French.
     */
    protected function getDocumentTypeLabel(string $type): string
    {
        return match($type) {
            'invoice' => 'FACTURE',
            'quote' => 'DEVIS',
            'delivery_note' => 'BON DE LIVRAISON',
            'credit_note' => 'AVOIR',
            'purchase_order' => 'BON DE COMMANDE',
            default => strtoupper($type),
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
