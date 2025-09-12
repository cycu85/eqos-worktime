<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rozliczenie delegacji - {{ $delegation->employee_full_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            line-height: 1.1;
            margin: 0;
            padding: 10px;
        }
        
        .main-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        
        .main-header td {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: middle;
        }
        
        .header-left {
            background-color: #fff;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }
        
        .header-right {
            background-color: #c0c0c0;
            text-align: center;
            font-weight: bold;
            color: red;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        
        .main-table td, .main-table th {
            border: 1px solid #000;
            padding: 2px;
            vertical-align: top;
            font-size: 8px;
        }
        
        .gray-bg {
            background-color: #e0e0e0;
        }
        
        .red-text {
            color: red;
            font-weight: bold;
        }
        
        .center {
            text-align: center;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .small-text {
            font-size: 7px;
        }
        
        .expenses-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
        }
        
        .expenses-table th, .expenses-table td {
            border: 1px solid #000;
            padding: 1px;
            font-size: 7px;
            text-align: center;
            vertical-align: middle;
        }
        
        .expenses-table th {
            background-color: #f0f0f0;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
        }
        
        .summary-table td {
            border: 1px solid #000;
            padding: 2px;
            font-size: 8px;
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }
        
        .signature-table td {
            border: 1px solid #000;
            padding: 15px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
        }
        
        .checkbox-section {
            margin: 3px 0;
        }
        
        .checkbox {
            margin-right: 3px;
        }
        
        .no-wrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <!-- Main Header -->
    <table class="main-header">
        <tr>
            <td class="header-left" style="width: 60%;">
                ROZLICZENIE DELEGACJI
            </td>
            <td class="header-right" style="width: 40%;">
                {{ ($delegation->first_name && $delegation->last_name) ? $delegation->first_name . ' ' . $delegation->last_name : '{Imię i Nazwisko}' }}
            </td>
        </tr>
    </table>

    <!-- Basic Info Table -->
    <table class="main-table">
        <tr>
            <td class="gray-bg">Polecenie wyjazdu z dnia</td>
            <td class="red-text">{{ $delegation->order_date ? \Carbon\Carbon::parse($delegation->order_date)->format('d.m.Y') : '{Data polecenia wyjazdu}' }}</td>
            <td colspan="2"></td>
            <td class="gray-bg">Nr delegacji</td>
            <td class="red-text">{{ $delegation->id }}</td>
        </tr>
        <tr>
            <td>Data wyjazdu - przekroczenia granicy</td>
            <td class="red-text">{{ $delegation->departure_date ? \Carbon\Carbon::parse($delegation->departure_date)->format('d.m.Y') : '{Data wyjazdu}' }}</td>
            <td>Data przyjazdu - przekroczenia granicy</td>
            <td class="red-text">{{ $delegation->arrival_date ? \Carbon\Carbon::parse($delegation->arrival_date)->format('d.m.Y') : '{Data przyjazdu}' }}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td>Godzina wyjazdu - przekroczenia granicy</td>
            <td class="red-text">{{ $delegation->departure_time ?: '{Godzina wyjazdu}' }}</td>
            <td>Godzina przyjazdu - przekroczenia granicy</td>
            <td class="red-text">{{ $delegation->arrival_time ?: '{Godzina przyjazdu}' }}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td>Czas delegacji:</td>
            <td colspan="5" class="red-text">{{ $delegation->getDurationText() ?: '{Czas delegacji "doby godziny:minuty"}' }}</td>
        </tr>
        <tr>
            <td>Cel podróży:</td>
            <td class="red-text">{{ $delegation->travel_purpose ?: '{Cel podróży}' }}</td>
            <td>Środki lokomocji:</td>
            <td class="red-text">{{ $delegation->vehicle_registration ?: '{Pojazd}' }}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td>Projekt</td>
            <td class="red-text">{{ $delegation->project ?: '{Projekt}' }}</td>
            <td>Do miejscowość:</td>
            <td class="red-text">{{ $delegation->destination_city ?: '{Miejscowość}' }}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="2">Kraj</td>
            <td colspan="4" class="red-text">{{ $delegation->country ?: '{Kraj}' }}</td>
        </tr>
    </table>

    <div class="small-text bold">UWAGA: zawsze należy wybrać kraj docelowy</div>

    <!-- Currency rates -->
    <table class="main-table">
        <tr>
            <td>Kwota diety</td>
            <td class="red-text">{{ $delegation->diet_amount_currency ? number_format($delegation->diet_amount_currency, 2) : '{Kwota diety waluta}' }} EUR</td>
            <td class="red-text">{{ $delegation->delegation_rate ? number_format($delegation->delegation_rate, 2) : '{Kwota diety PLN}' }} PLN</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td>Kwota limitu za nocleg</td>
            <td>EUR</td>
            <td>PLN</td>
            <td colspan="3"></td>
        </tr>
    </table>

    <!-- NBP Table -->
    <table class="main-table">
        <tr>
            <td style="width: 40%">Tabela kursów NBP z dnia</td>
            <td style="width: 60%" class="red-text">{{ $delegation->nbp_table_date ? \Carbon\Carbon::parse($delegation->nbp_table_date)->format('d.m.Y') : '{Kurs NBP.Data}' }}</td>
        </tr>
        <tr>
            <td>Tabela kursów NBP numer:</td>
            <td class="red-text">{{ $delegation->nbp_table_number ?: '{kurs NBP. Numer tabeli}' }}</td>
        </tr>
        <tr>
            <td>Kurs</td>
            <td class="red-text">{{ $delegation->exchange_rate ? number_format($delegation->exchange_rate, 4) : '{Kurs NBP.Kurs}' }}</td>
        </tr>
    </table>

    <!-- Meals Options -->
    <table class="main-table">
        <tr>
            <td colspan="6" class="bold">Opcje wyżywienia (na koszt pracodawcy):</td>
        </tr>
        <tr>
            <td class="gray-bg">Ilość noclegów</td>
            <td class="red-text">{{ $delegation->nights_count ?: '{Ilość noclegów}' }}</td>
            <td>Śniadanie wliczone w koszt noclegu</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td>☐ Wyżywienie za granicą na koszt pracodawcy - OPCJA 1</td>
            <td colspan="2">☐ Różne w okresie trwania delegacji - OPCJA 2</td>
            <td colspan="3"></td>
        </tr>
    </table>

    <!-- Meals counts -->
    <table class="main-table">
        <tr>
            <td>śniadanie (ilość)</td>
            <td class="red-text">{{ $delegation->breakfasts ?? 0 }}</td>
            <td>śniadanie (ilość)</td>
            <td>obiad (ilość)</td>
            <td class="red-text">{{ $delegation->lunches ?? 0 }}</td>
            <td>obiad (ilość)</td>
        </tr>
        <tr>
            <td>kolacja (ilość)</td>
            <td class="red-text">{{ $delegation->dinners ?? 0 }}</td>
            <td>kolacja (ilość)</td>
            <td colspan="3"></td>
        </tr>
    </table>

    <!-- Diet Summary -->
    <table class="main-table">
        <tr>
            <td style="width: 40%">Suma diet należnych:</td>
            <td style="width: 30%" class="red-text">{{ $delegation->getTotalDietEUR() ? number_format($delegation->getTotalDietEUR(), 2) : '{Suma diet waluta}' }} EUR</td>
            <td style="width: 30%" class="red-text">{{ $delegation->getTotalDietPLN() ? number_format($delegation->getTotalDietPLN(), 2) : '{Suma diet należnych}' }} PLN</td>
        </tr>
    </table>

    <div class="small-text">Wydatki opłacone kartą lub przelewem proszę wpisać informacyjnie - wydatki tego samego rodzaju opłacone w różny sposób wpisać w oddzielnych pozycjach. Proszę wpisać także</div>

    <!-- Expenses Table -->
    <table class="expenses-table">
        <tr>
            <th>Wydatkowano wg załączników:</th>
            <th>waluta (wartość)</th>
            <th>ilość noclegów</th>
            <th>forma płatności</th>
            <th>symbol waluty</th>
            <th>kurs waluty</th>
            <th>Nr faktury</th>
            <th>kwota w PLN</th>
        </tr>
        @for($i = 1; $i <= 6; $i++)
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>dla PLN 1,0</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        @endfor
    </table>

    <!-- Final Summary -->
    <table class="summary-table">
        <tr>
            <td class="bold">Suma wydatków poniesionych (PLN)</td>
            <td class="bold red-text">{{ number_format($delegation->total_expenses ?: 0, 2) }}</td>
            <td class="bold">Do wypłaty dla pracownika(PLN)</td>
            <td class="bold red-text">{{ number_format($delegation->getTotalPaymentAmount(), 2) }}</td>
        </tr>
        <tr>
            <td>Suma wydatków nie opłaconych gotówką (PLN)</td>
            <td>0,00</td>
            <td>Przekroczenie limitu za nocleg (PLN)</td>
            <td>0,00</td>
        </tr>
    </table>

    <div class="small-text">Oświadczam, że zgodnie z treścią artykułu 233 Kodeksu Karnego ponoszę odpowiedzialność za podanie danych niezgodnych z prawdą.</div>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td>DATA I PODPIS OSOBY DELEGOWANEJ</td>
            <td>DATA I PODPIS KIEROWNIKA PROJEKTU/DYREKTORA/<br>ZLECAJĄCEGO WYJAZD</td>
        </tr>
    </table>

    <div class="center bold" style="margin: 5px 0;">SPRAWDZONO POD WZGLĘDEM FORMALNO-RACHUNKOWYM</div>
    
    <table class="signature-table">
        <tr>
            <td>DATA I PODPIS</td>
        </tr>
    </table>
</body>
</html>