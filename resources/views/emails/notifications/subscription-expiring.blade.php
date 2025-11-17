@extends('emails.layout')

@section('title', 'Votre abonnement expire bientôt')

@section('header')
    Abonnement - Expiration Prochaine
@endsection

@section('content')
    <p>Bonjour,</p>

    <p>Votre abonnement au plan <strong>{{ $subscription->plan }}</strong> arrive à expiration.</p>

    <div class="info-box" style="border-left-color: #ff9800; background-color: #fff3e0;">
        <h3 style="margin-top: 0; color: #e65100;">Détails de l'abonnement</h3>
        <table style="width: 100%;">
            <tr>
                <td><strong>Plan:</strong></td>
                <td>{{ ucfirst($subscription->plan) }}</td>
            </tr>
            <tr>
                <td><strong>Modules:</strong></td>
                <td>{{ implode(', ', $subscription->modules ?? []) }}</td>
            </tr>
            <tr>
                <td><strong>Date d'expiration:</strong></td>
                <td style="color: #ff5722; font-weight: bold;">{{ $subscription->ends_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Jours restants:</strong></td>
                <td style="color: #ff5722; font-weight: bold;">{{ $daysRemaining }} jours</td>
            </tr>
        </table>
    </div>

    <p>Pour continuer à profiter de nos services sans interruption, merci de renouveler votre abonnement avant la date d'expiration.</p>

    <div class="text-center">
        <a href="{{ $renewUrl }}" class="button" style="background-color: #ff9800;">Renouveler maintenant</a>
    </div>

    <h4>Que se passe-t-il après l'expiration ?</h4>
    <ul>
        <li>Vos données seront conservées en lecture seule pendant 30 jours</li>
        <li>Vous ne pourrez plus créer de nouveaux documents</li>
        <li>L'accès aux modules sera suspendu</li>
    </ul>

    <p>Pour toute question, notre équipe support est à votre disposition.</p>

    <p>Cordialement,<br>L'équipe {{ config('app.name') }}</p>
@endsection
