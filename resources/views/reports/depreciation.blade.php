<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Depreciación</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        .report-table th, .report-table td { border: 1px solid #000; padding: 4px; }
        .report-table th { background-color: #bfe8f1; text-align: center; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .specific-header { font-weight: bold; background-color: #e8f4f8; }
        .subtotal { font-weight: bold; background-color: #f9f9f9; }
        .total-general { font-weight: bold; background-color: #e9e9e9; }
    </style>
</head>
<body>

    <table style="margin-bottom:10px;">
        <tr>
            <td style="width:20%; text-align:left; vertical-align:top;">
                @if(file_exists(public_path('escudo.png')))
                    <img src="{{ public_path('escudo.png') }}" width="60">
                @endif
            </td>
            <td style="width:60%; text-align:center; vertical-align:middle;">
                <div style="font-weight:bold; font-size:14px;">Dirección General de Energía, Hidrocarburos y Minas</div>
                <div style="margin-top:4px; font-size:13px;">Reporte de Depreciación de Activos Fijos</div>
                <div style="margin-top:4px; font-size:13px;">Al {{ \Carbon\Carbon::parse($reportDate)->format('d/m/Y') }}</div>
                <div style="margin-top:4px; font-size:11px;">Activos con valor de compra ≥ $900.00 · Valor residual 10% · Depreciación lineal 90% sobre vida útil</div>
            </td>
            <td style="width:20%; text-align:right; vertical-align:top;">
                @if(file_exists(public_path('logo_azul.png')))
                    <img src="{{ public_path('logo_azul.png') }}" height="60">
                @endif
            </td>
        </tr>
    </table>
    <hr style="border:1px solid #000; margin-bottom:15px;">

    <table class="report-table">
        <thead>
            <tr>
                <th style="width:8%">Código</th>
                <th style="width:18%">Descripción</th>
                <th style="width:8%">F. Adquisición</th>
                <th style="width:5%">Vida útil (años)</th>
                <th style="width:10%">Valor compra</th>
                <th style="width:8%">Valor residual 10%</th>
                <th style="width:8%">Deprec. anual</th>
                <th style="width:8%">Deprec. mensual</th>
                <th style="width:10%">Deprec. acumulada</th>
                <th style="width:10%">Valor en libros</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rowsBySpecific as $group)
                <tr class="specific-header">
                    <td colspan="10">
                        Específico: {{ $group['specific']->code }} — {{ $group['specific']->name }}
                    </td>
                </tr>
                @foreach ($group['rows'] as $row)
                    <tr>
                        <td class="text-center">{{ $row['asset']->code }}</td>
                        <td>{{ $row['asset']->description }}</td>
                        <td class="text-center">{{ $row['asset']->acquisition_date->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $row['useful_life_years'] }}</td>
                        <td class="text-right">${{ number_format($row['purchase_value'], 2) }}</td>
                        <td class="text-right">${{ number_format($row['residual_value'], 2) }}</td>
                        <td class="text-right">${{ number_format($row['annual_depreciation'], 2) }}</td>
                        <td class="text-right">${{ number_format($row['monthly_depreciation'], 2) }}</td>
                        <td class="text-right">${{ number_format($row['accumulated_depreciation'], 2) }}</td>
                        <td class="text-right">${{ number_format($row['book_value'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal">
                    <td colspan="4" class="text-right">SUB TOTAL POR ESPECÍFICO:</td>
                    <td class="text-right">${{ number_format($group['subtotal_purchase'], 2) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">${{ number_format($group['subtotal_book_value'], 2) }}</td>
                </tr>
            @endforeach

            <tr class="total-general">
                <td colspan="4" class="text-right">TOTAL GENERAL:</td>
                <td class="text-right">${{ number_format($grandTotalPurchase, 2) }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right">${{ number_format($grandTotalBookValue, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
