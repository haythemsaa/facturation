<?php

namespace App\Observers;

use App\Models\Document;

class DocumentObserver
{
    /**
     * Handle the Document "creating" event.
     * Auto-generate document number if not provided.
     */
    public function creating(Document $document): void
    {
        if (empty($document->number)) {
            $document->number = $this->generateDocumentNumber($document);
        }

        // Auto-calculate totals if not set
        if ($document->subtotal === null) {
            $document->subtotal = 0;
        }
        if ($document->tax_amount === null) {
            $document->tax_amount = 0;
        }
        if ($document->total === null) {
            $document->total = 0;
        }
    }

    /**
     * Handle the Document "updated" event.
     * Update paid status based on amounts.
     */
    public function updated(Document $document): void
    {
        // Auto-update payment status
        if ($document->isDirty('paid_amount')) {
            if ($document->paid_amount >= $document->total) {
                $document->payment_status = 'paid';
            } elseif ($document->paid_amount > 0) {
                $document->payment_status = 'partial';
            } else {
                $document->payment_status = 'pending';
            }

            // Save without triggering another update event
            $document->saveQuietly();
        }
    }

    /**
     * Generate unique document number based on type and sequence.
     */
    private function generateDocumentNumber(Document $document): string
    {
        $prefix = match($document->type) {
            'invoice' => 'INV',
            'quote' => 'QTE',
            'delivery_note' => 'BL',
            'credit_note' => 'AV',
            'purchase_order' => 'BC',
            default => 'DOC'
        };

        $year = now()->format('Y');
        $month = now()->format('m');

        // Get last document number for this type and tenant
        $lastDocument = Document::where('type', $document->type)
            ->where('number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderByRaw('CAST(SUBSTRING(number FROM \'\d+$\') AS INTEGER) DESC')
            ->first();

        if ($lastDocument && preg_match('/-(\d+)$/', $lastDocument->number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }
}
