<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Zaakceptowana delegacja</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <p>Dzień dobry,</p>
        
        <p>W załączeniu przesyłamy zaakceptowaną delegację w formacie PDF.</p>
        
        <h3 style="color: #2563eb; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;">Szczegóły delegacji:</h3>
        <ul style="list-style-type: none; padding-left: 0;">
            <li style="margin-bottom: 8px;"><strong>Pracownik:</strong> {{ $delegation->full_name }}</li>
            <li style="margin-bottom: 8px;"><strong>Cel podróży:</strong> {{ $delegation->travel_purpose }}</li>
            <li style="margin-bottom: 8px;"><strong>Miejscowość:</strong> {{ $delegation->destination_city }}, {{ $delegation->country }}</li>
            <li style="margin-bottom: 8px;"><strong>Data wyjazdu:</strong> {{ $delegation->departure_date?->format('d.m.Y') }}{{ $delegation->departure_time ? ' o godz. ' . $delegation->departure_time : '' }}</li>
            <li style="margin-bottom: 8px;"><strong>Data powrotu:</strong> {{ $delegation->arrival_date?->format('d.m.Y') }}{{ $delegation->arrival_time ? ' o godz. ' . $delegation->arrival_time : '' }}</li>
            <li style="margin-bottom: 8px;"><strong>Czas trwania:</strong> {{ $delegation->delegation_duration }}</li>
            <li style="margin-bottom: 8px;"><strong>Status:</strong> Zaakceptowana {{ $delegation->supervisor_approval_date?->format('d.m.Y o H:i') }}</li>
        </ul>

        @if($delegation->project)
        <p style="margin-top: 15px;"><strong>Projekt:</strong> {{ $delegation->project }}</p>
        @endif

        @if($delegation->vehicle_registration)
        <p><strong>Pojazd:</strong> {{ $delegation->vehicle_registration }}</p>
        @endif

        <h3 style="color: #2563eb; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-top: 25px;">Rozliczenie finansowe:</h3>
        <ul style="list-style-type: none; padding-left: 0;">
            @if($delegation->country === 'Polska')
            <li style="margin-bottom: 8px;"><strong>Dieta krajowa:</strong> {{ number_format($delegation->total_diet_pln, 2, ',', ' ') }} PLN</li>
            @else
            <li style="margin-bottom: 8px;"><strong>Dieta zagraniczna:</strong> {{ number_format($delegation->total_diet_currency, 2, ',', ' ') }} EUR ({{ number_format($delegation->total_diet_pln, 2, ',', ' ') }} PLN)</li>
            @endif
            @if($delegation->accommodation_limit > 0)
            <li style="margin-bottom: 8px;"><strong>Limit noclegowy:</strong> {{ number_format($delegation->accommodation_limit, 2, ',', ' ') }} PLN</li>
            @endif
            @if($delegation->total_expenses > 0)
            <li style="margin-bottom: 8px;"><strong>Wydatki własne:</strong> {{ number_format($delegation->total_expenses, 2, ',', ' ') }} PLN</li>
            @endif
            <li style="margin-bottom: 8px; font-size: 18px; color: #059669;"><strong>Kwota do wypłaty: {{ number_format($delegation->amount_to_pay, 2, ',', ' ') }} PLN</strong></li>
        </ul>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

        <p>Pozdrawiamy,<br>
        <strong>System {{ \App\Models\Setting::getAppName() }}</strong></p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        <p style="font-size: 12px; color: #6b7280;">
            Ta wiadomość została wygenerowana automatycznie przez system {{ \App\Models\Setting::getAppName() }}.<br>
            Data wysłania: {{ now()->format('d.m.Y H:i:s') }}
        </p>
    </div>
</body>
</html>