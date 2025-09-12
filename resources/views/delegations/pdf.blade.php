<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rozliczenie delegacji - {{ $delegation->employee_full_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.2;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .section {
            margin-bottom: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        table, th, td {
            border: 1px solid #333;
        }
        
        th, td {
            padding: 4px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .gray-bg {
            background-color: #e0e0e0;
        }
        
        .no-border {
            border: none;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .small {
            font-size: 9px;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-table td {
            border: 1px solid #333;
            padding: 3px;
        }
        
        .signature-section {
            margin-top: 30px;
        }
        
        .signature-box {
            border: 1px solid #333;
            height: 60px;
            margin: 10px 0;
        }
        
        .checkbox {
            margin-right: 5px;
        }
        
        .expenses-table {
            width: 100%;
            margin: 10px 0;
        }
        
        .expenses-table th {
            font-size: 9px;
            padding: 2px;
        }
        
        .expenses-table td {
            font-size: 9px;
            padding: 2px;
            height: 15px;
        }
        
        .summary-section {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        ROZLICZENIE DELEGACJI {{ $delegation->employee_full_name }}<br>
        <span class="small">Nr delegacji {{ $delegation->id }}</span>
    </div>
    
    <!-- Main information table -->
    <table class="info-table">
        <tr>
            <td class="gray-bg bold">Polecenie wyjazdu z dnia</td>
            <td>{{ $delegation->order_date ? \Carbon\Carbon::parse($delegation->order_date)->format('d.m.Y') : '' }}</td>
            <td colspan="2" class="small">* wypełniamy tylko szare pola</td>
        </tr>
        <tr>
            <td>Data wyjazdu - przekroczenia granicy</td>
            <td>{{ $delegation->departure_date ? \Carbon\Carbon::parse($delegation->departure_date)->format('d.m.Y') : '' }}</td>
            <td>Data przyjazdu - przekroczenia granicy</td>
            <td>{{ $delegation->arrival_date ? \Carbon\Carbon::parse($delegation->arrival_date)->format('d.m.Y') : '' }}</td>
        </tr>
        <tr>
            <td>Godzina wyjazdu - przekroczenia granicy</td>
            <td>{{ $delegation->departure_time ?: '' }}</td>
            <td>Godzina przyjazdu - przekroczenia granicy</td>
            <td>{{ $delegation->arrival_time ?: '' }}</td>
        </tr>
        <tr>
            <td>Czas delegacji:</td>
            <td colspan="3">{{ $delegation->getDurationText() }}</td>
        </tr>
        <tr>
            <td>Cel podróży:</td>
            <td>{{ $delegation->travel_purpose }}</td>
            <td>Środki lokomocji:</td>
            <td>{{ $delegation->vehicle_registration ?: '' }}</td>
        </tr>
        <tr>
            <td>Projekt</td>
            <td>{{ $delegation->project ?: '' }}</td>
            <td>Do miejscowość:</td>
            <td>{{ $delegation->destination_city }}, {{ $delegation->country }}</td>
        </tr>
    </table>
    
    <div class="small">UWAGA: zawsze należy wybrać kraj docelowy</div>
    
    <!-- Currency and rates section -->
    <table class="info-table">
        <tr>
            <td class="gray-bg">Kwota diety</td>
            <td>{{ number_format($delegation->delegation_rate_eur, 2) }} EUR</td>
            <td>{{ number_format($delegation->delegation_rate, 2) }} PLN</td>
        </tr>
        <tr>
            <td class="gray-bg">Kwota limitu za nocleg</td>
            <td>EUR</td>
            <td>PLN</td>
        </tr>
    </table>
    
    <!-- NBP exchange rate info -->
    <table class="info-table">
        <tr>
            <td>Tabela kursów NBP z dnia</td>
            <td>{{ $delegation->exchange_rate_date ? \Carbon\Carbon::parse($delegation->exchange_rate_date)->format('d.m.Y') : '' }}</td>
        </tr>
        <tr>
            <td>Tabela kursów NBP numer:</td>
            <td>{{ $delegation->exchange_rate_table ?: '' }}</td>
        </tr>
        <tr>
            <td>Kurs</td>
            <td>{{ $delegation->exchange_rate ? number_format($delegation->exchange_rate, 4) : '' }}</td>
        </tr>
    </table>
    
    <!-- Accommodation and meals -->
    <table class="info-table">
        <tr>
            <td class="gray-bg">Ilość noclegów</td>
            <td>{{ $delegation->nights_count }}</td>
        </tr>
    </table>
    
    <!-- Meal options -->
    <div class="section">
        <div class="bold">Opcje wyżywienia (na koszt pracodawcy):</div>
        <div>
            <input type="checkbox" class="checkbox"> Wyżywienie za granicą na koszt pracodawcy - OPCJA 1 
            <input type="checkbox" class="checkbox"> Różne w okresie trwania delegacji - OPCJA 2
        </div>
        <div>
            <input type="checkbox" class="checkbox"> Śniadanie wliczone w koszt noclegu
        </div>
        
        <table class="info-table" style="margin-top: 10px;">
            <tr>
                <td>śniadanie (ilość)</td>
                <td>{{ $delegation->breakfasts }}</td>
                <td>śniadanie (ilość)</td>
                <td></td>
            </tr>
            <tr>
                <td>obiad (ilość)</td>
                <td>{{ $delegation->lunches }}</td>
                <td>obiad (ilość)</td>
                <td></td>
            </tr>
            <tr>
                <td>kolacja (ilość)</td>
                <td>{{ $delegation->dinners }}</td>
                <td>kolacja (ilość)</td>
                <td></td>
            </tr>
        </table>
    </div>
    
    <!-- Diet summary -->
    <table class="info-table">
        <tr>
            <td class="gray-bg">Suma diet należnych:</td>
            <td>{{ number_format($delegation->calculateTotalDietEUR(), 2) }} SEK</td>
            <td>{{ number_format($delegation->calculateTotalDiet(), 2) }} PLN</td>
        </tr>
    </table>
    
    <!-- Expenses table -->
    <div class="section">
        <div class="small">Wydatki opłacone kartą lub przelewem proszę wpisać informacyjnie - wydatki tego samego rodzaju opłacone w różny sposób wpisać w oddzielnych pozycjach. Proszę wpisać także</div>
        
        <table class="expenses-table">
            <tr>
                <th>Wydatkowano wg załączników:</th>
                <th>waluta (wartość)</th>
                <th>Ilość noclegów</th>
                <th>forma płatności</th>
                <th>symbol waluty</th>
                <th>kurs waluty</th>
                <th>Nr faktury</th>
                <th>kwota w PLN</th>
            </tr>
            @for($i = 1; $i <= 8; $i++)
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
    </div>
    
    <!-- Summary section -->
    <div class="summary-section">
        <table class="info-table">
            <tr>
                <td class="gray-bg">Suma wydatków poniesionych (PLN)</td>
                <td>{{ number_format($delegation->total_expenses, 2) }}</td>
            </tr>
            <tr>
                <td class="gray-bg">Do wypłaty dla pracownika(PLN)</td>
                <td>{{ number_format($delegation->calculateTotalPayment(), 2) }}</td>
            </tr>
            <tr>
                <td>Suma wydatków nie opłaconych gotówką (PLN)</td>
                <td>0,00</td>
            </tr>
            <tr>
                <td>Przekroczenie limitu za nocleg (PLN)</td>
                <td>0,00</td>
            </tr>
        </table>
    </div>
    
    <!-- Declaration -->
    <div class="section small">
        Oświadczam, że zgodnie z treścią artykułu 233 Kodeksu Karnego ponoszę odpowiedzialność za podanie danych niezgodnych z prawdą.
    </div>
    
    <!-- Signature sections -->
    <div class="signature-section">
        <table class="info-table">
            <tr>
                <td class="text-center bold">DATA I PODPIS OSOBY DELEGOWANEJ</td>
                <td class="text-center bold">DATA I PODPIS KIEROWNIKA PROJEKTU/DYREKTORA/<br>ZLECAJĄCEGO WYJAZD</td>
            </tr>
            <tr>
                <td class="signature-box"></td>
                <td class="signature-box"></td>
            </tr>
        </table>
        
        <div class="text-center bold" style="margin-top: 20px;">
            SPRAWDZONO POD WZGLĘDEM FORMALNO-RACHUNKOWYM
        </div>
        
        <div class="signature-box" style="margin-top: 10px;"></div>
        <div class="text-center">DATA I PODPIS</div>
    </div>
    
    <!-- Vehicle information -->
    <div class="section" style="margin-top: 30px;">
        <div class="bold">Informacja o pojazdach</div>
        <table class="info-table">
            <tr>
                <td class="bold">Kierowca:</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">Data</td>
                <td class="bold">Godzina</td>
                <td class="bold">Data</td>
                <td class="bold">Godzina</td>
                <td class="bold">Data</td>
                <td class="bold">Godzina</td>
            </tr>
            @for($i = 1; $i <= 3; $i++)
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @endfor
        </table>
    </div>
</body>
</html>