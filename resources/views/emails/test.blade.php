Witaj!

To jest testowa wiadomość email z aplikacji {{ \App\Models\Setting::getAppName() }}.

Jeśli otrzymałeś tę wiadomość, oznacza to, że konfiguracja SMTP działa poprawnie.

Szczegóły testu:
- Data wysłania: {{ now()->format('d.m.Y H:i:s') }}
- Aplikacja: {{ \App\Models\Setting::getAppName() }}
- Środowisko: {{ config('app.env') }}

Dziękujemy za korzystanie z {{ \App\Models\Setting::getAppName() }}!

---
Ta wiadomość została wygenerowana automatycznie przez system {{ \App\Models\Setting::getAppName() }}.