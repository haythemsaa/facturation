@extends('emails.layout')

@section('title', 'Rappel de Paiement')

@section('header')
    Rappel de Paiement
@endsection

@section('content')
    <p>Bonjour {{ $customer->name }},</p>

    <p>Nous vous rappelons que la facture ci-dessous est en attente de paiement.</p>

    <div class="info-box" style="border-left-color: #ffc107; background-color: #fff3cd;">
        <h3 style="margin-top: 0; color: #856404;">Facture en retard</h3>
        <table style="width: 100%;">
            <tr>
                <td><strong>N° Facture:</strong></td>
                <td>{{ $invoice->number }}</td>
            </tr>
            <tr>
                <td><strong>Date d'émission:</strong></td>
                <td>{{ $invoice->date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Date d'échéance:</strong></td>
                <td style="color: #dc3545; font-weight: bold;">{{ $invoice->due_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Jours de retard:</strong></td>
                <td style="color: #dc3545; font-weight: bold;">{{ now()->diffInDays($invoice->due_date) }} jours</td>
            </tr>
            <tr>
                <td><strong>Montant dû:</strong></td>
                <td><strong style="font-size: 20px; color: #dc3545;">{{ number_format($invoice->total_ttc, 3, ',', ' ') }} TND</strong></td>
            </tr>
        </table>
    </div>

    <p>Nous vous remercions de bien vouloir procéder au règlement dans les plus brefs délais.</p>

    <div class="text-center">
        <a href="{{ $invoiceUrl }}" class="button" style="background-color: #ffc107; color: #000;">Voir la Facture</a>
    </div>

    <p>Si vous avez déjà effectué le paiement, merci de ne pas tenir compte de ce message.</p>

    <p>Cordialement,<br>{{ $tenant->company_name ?? $tenant->name }}</p>
@endsection
