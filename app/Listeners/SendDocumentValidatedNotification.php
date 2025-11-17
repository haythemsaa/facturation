<?php

namespace App\Listeners;

use App\Events\DocumentValidated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendDocumentValidatedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(DocumentValidated $event): void
    {
        $document = $event->document;

        // Log l'événement
        Log::info("Document {$document->type} {$document->number} validé", [
            'document_id' => $document->id,
            'tenant_id' => $document->tenant_id,
            'total' => $document->total_ttc,
        ]);

        // TODO: Envoyer email au client
        // TODO: Envoyer notification push à l'utilisateur
        // TODO: Créer une notification in-app
    }
}
