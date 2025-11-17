<?php

namespace App\Services;

use App\Events\DocumentValidated;
use App\Models\Document;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function __construct(
        private TaxCalculator $taxCalculator
    ) {}

    /**
     * Validate a document and update stock if needed.
     */
    public function validateDocument(Document $document): Document
    {
        return DB::transaction(function () use ($document) {
            // Mark as validated
            $document->is_validated = true;
            $document->validated_at = now();
            $document->validated_by = auth()->id();
            $document->save();

            // Update stock if it's a stock-affecting document
            if (in_array($document->type, ['invoice', 'delivery_note'])) {
                $this->updateStockFromDocument($document, 'out');
            } elseif ($document->type === 'purchase_order') {
                $this->updateStockFromDocument($document, 'in');
            }

            // Dispatch event
            event(new DocumentValidated($document));

            return $document->fresh();
        });
    }

    /**
     * Convert quote to invoice.
     */
    public function convertQuoteToInvoice(Document $quote): Document
    {
        if ($quote->type !== 'quote') {
            throw new \InvalidArgumentException('Document must be a quote');
        }

        return DB::transaction(function () use ($quote) {
            // Create invoice from quote
            $invoice = $quote->replicate();
            $invoice->type = 'invoice';
            $invoice->number = null; // Will be auto-generated
            $invoice->related_document_id = $quote->id;
            $invoice->is_validated = false;
            $invoice->save();

            // Copy lines
            foreach ($quote->lines as $line) {
                $invoiceLine = $line->replicate();
                $invoiceLine->document_id = $invoice->id;
                $invoiceLine->save();
            }

            // Update quote status
            $quote->status = 'converted';
            $quote->save();

            return $invoice->fresh('lines');
        });
    }

    /**
     * Calculate document totals with Tunisian tax rules.
     */
    public function calculateTotals(Document $document): array
    {
        $lines = $document->lines;
        $subtotal = $lines->sum(fn($line) => $line->quantity * $line->unit_price);

        // Apply discount
        $discountAmount = $document->discount_rate
            ? $subtotal * ($document->discount_rate / 100)
            : ($document->discount_amount ?? 0);

        $totalHT = $subtotal - $discountAmount;

        // Calculate TVA by rate
        $tvaByRate = $lines->groupBy('tva_rate')->map(function ($groupedLines, $rate) use ($document) {
            $lineTotal = $groupedLines->sum(fn($line) => $line->quantity * $line->unit_price);
            $lineDiscount = $document->discount_rate
                ? $lineTotal * ($document->discount_rate / 100)
                : ($lineTotal / $subtotal) * ($document->discount_amount ?? 0);

            return ($lineTotal - $lineDiscount) * ($rate / 100);
        });

        $totalTVA = $tvaByRate->sum();

        // Timbre fiscal (1% with max 1 TND)
        $timbreFiscal = $this->taxCalculator->calculateTimbreFiscal($totalHT);

        $totalTTC = $totalHT + $totalTVA + $timbreFiscal;

        return [
            'subtotal' => round($subtotal, 3),
            'discount_amount' => round($discountAmount, 3),
            'total_ht' => round($totalHT, 3),
            'total_tva' => round($totalTVA, 3),
            'timbre_fiscal' => round($timbreFiscal, 3),
            'total_ttc' => round($totalTTC, 3),
        ];
    }

    /**
     * Update stock quantities from document lines.
     */
    private function updateStockFromDocument(Document $document, string $type): void
    {
        foreach ($document->lines as $line) {
            if (!$line->product || !$line->product->track_stock) {
                continue;
            }

            StockMovement::create([
                'product_id' => $line->product_id,
                'warehouse_id' => $document->warehouse_id,
                'type' => $type === 'out' ? 'sale' : 'in',
                'quantity' => $line->quantity,
                'reference_type' => Document::class,
                'reference_id' => $document->id,
                'note' => "Document {$document->type} {$document->number}",
                'tenant_id' => $document->tenant_id,
            ]);
        }
    }
}
