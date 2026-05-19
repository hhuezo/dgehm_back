<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Existencias</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* =========================
           SOLO TABLA DEL REPORTE
        ========================== */
        .report-table th,
        .report-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .report-table th {
            background-color: #bfe8f1;
            text-align: center;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .subtotal {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .total-general {
            font-weight: bold;
            background-color: #e9e9e9;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <table style="margin-bottom:10px;">
        <tr>
            <td style="width:20%; text-align:left; vertical-align:top;">
                <img src="{{ public_path('escudo.png') }}" width="60">
            </td>

            <td style="width:60%; text-align:center; vertical-align:middle;">
                <div style="font-weight:bold; font-size:14px;">
                    Dirección General de Energía, Hidrocarburos y Minas
                </div>
                <div style="margin-top:4px; font-size:13px;">
                    Informe inicial de Producto
                </div>
                <div style="margin-top:4px; font-size:13px;">
                    AL {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                </div>
                <div style="margin-top:6px; font-weight:bold; font-size:13px;">
                    RECURSOS PROPIOS &nbsp;&nbsp; CEL
                </div>
            </td>

            <td style="width:20%; text-align:right; vertical-align:top;">
                <img src="{{ public_path('logo_azul.png') }}" height="60">
            </td>
        </tr>
    </table>

    <hr style="border:1px solid #000; margin-bottom:15px;">

    {{-- TABLA DEL REPORTE --}}
    <table class="report-table">
        <thead>
            <tr>
                <th style="width:8%">Código</th>
                <th style="width:22%">Cuenta</th>
                <th style="width:30%">Producto</th>
                <th style="width:8%">Cantidad</th>
                <th style="width:8%">Unidad</th>
                <th style="width:12%">Precio Unitario</th>
                <th style="width:12%">Valor</th>
            </tr>
        </thead>

        <tbody>
            @php
                $groupedByAccount = $stock->groupBy('accounting_account_id');
                $grandTotal = 0;
            @endphp

            @foreach ($groupedByAccount as $accountStock)
                @php
                    $accountTotal = 0;
                @endphp

                @foreach ($accountStock as $item)
                    @php
                        $rowValue = (float) $item->stock_quantity * (float) $item->unit_price;
                        $accountTotal += $rowValue;
                    @endphp

                    {{-- Sin rowspan: cada fila lleva código y cuenta para que el PDF no se desconfigure al pasar de página --}}
                    <tr>
                        <td class="text-center">{{ $item->account_code }}</td>
                        <td>{{ $item->account_name }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td class="text-right">{{ number_format($item->stock_quantity, 0) }}</td>
                        <td class="text-center">{{ $item->measure_name }}</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">${{ number_format($rowValue, 2) }}</td>
                    </tr>
                @endforeach

                @php
                    $grandTotal += $accountTotal;
                @endphp

                {{-- SUBTOTAL POR CUENTA --}}
                <tr class="subtotal">
                    <td colspan="6" class="text-right">
                        SUB TOTAL POR ESPECÍFICO:
                    </td>
                    <td class="text-right">
                        ${{ number_format($accountTotal, 2) }}
                    </td>
                </tr>
            @endforeach

            {{-- TOTAL GENERAL --}}
            <tr class="total-general">
                <td colspan="6" class="text-right">
                    TOTAL GENERAL:
                </td>
                <td class="text-right">
                    ${{ number_format($grandTotal, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <br><br><br>

    {{-- FIRMA --}}
    <table style="text-align:center;">
        <tr>
            <td>
                __________________________________________<br>
                <strong>Licda. Dora Jeannette Timas Trujillo</strong><br>
                Gerente Administrativa<br>
                Dirección General de Energía, Hidrocarburos y Minas
            </td>
        </tr>
    </table>

</body>
</html>
