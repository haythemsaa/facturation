@extends('emails.layout')

@section('title', 'Nouvelle Facture')

@section('header')
    Nouvelle Facture {{ $invoice->number }}
@endsection

@section('content')
    <p>Bonjour {{ $customer->name }},</p>

    <p>Nous vous remercions pour votre confiance. Veuillez trouver ci-joint votre facture.</p>

    <div class="info-box">
        <h3 style="margin-top: 0;">Détails de la facture</h3>
        <table style="width: 100%;">
            <tr>
                <td><strong>N° Facture:</strong></td>
                <td>{{ $invoice->number }}</td>
            </tr>
            <tr>
                <td><strong>Date:</strong></td>
                <td>{{ $invoice->date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Échéance:</strong></td>
                <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Montant Total TTC:</strong></td>
                <td><strong style="font-size: 18px; color: #667eea;">{{ number_format($invoice->total_ttc, 3, ',', ' ') }} TND</strong></td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="text-center">Quantité</th>
                <th class="text-right">Prix U.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
            <tr>
                <td>{{ $line->product_name }}</td>
                <td class="text-center">{{ $line->quantity }}</td>
                <td class="text-right">{{ number_format($line->unit_price, 3, ',', ' ') }} TND</td>
                <td class="text-right">{{ number_format($line->quantity * $line->unit_price, 3, ',', ' ') }} TND</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Total HT:</strong></td>
                <td class="text-right">{{ number_format($invoice->total_ht, 3, ',', ' ') }} TND</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>TVA:</strong></td>
                <td class="text-right">{{ number_format($invoice->total_tva, 3, ',', ' ') }} TND</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>Timbre Fiscal:</strong></td>
                <td class="text-right">{{ number_format($invoice->timbre_fiscal, 3, ',', ' ') }} TND</td>
            </tr>
            <tr style="font-size: 16px;">
                <td colspan="3" class="text-right"><strong>Total TTC:</strong></td>
                <td class="text-right"><strong>{{ number_format($invoice->total_ttc, 3, ',', ' ') }} TND</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="text-center">
        <a href="{{ $downloadUrl }}" class="button">Télécharger la Facture PDF</a>
    </div>

    <p>Pour toute question concernant cette facture, n'hésitez pas à nous contacter.</p>

    <p>Cordialement,<br>{{ $tenant->company_name ?? $tenant->name }}</p>
@endsection
