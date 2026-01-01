<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Entrega de Producto</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* =========================
           TABLA DEL REPORTE
        ========================== */
        .report-table th,
        .report-table td {
            border: 1px solid #000;
            padding: 4px;
        }

        .report-table th {
            background-color: #e6f3f7;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .product-name {
            font-size: 10.5px;
        }

        .total-col {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .header-title {
            font-size: 14px;
            font-weight: bold;
        }

        .header-subtitle {
            font-size: 12px;
            margin-top: 3px;
        }

        .header-info {
            font-size: 11px;
            margin-top: 3px;
        }
    </style>
</head>

<body>

    {{-- HEADER INSTITUCIONAL --}}
    <table style="margin-bottom:10px;">
        <tr>
            <td style="width:20%; text-align:left;">
                <img src="{{ public_path('escudo.png') }}" width="55">
            </td>

            <td style="width:60%; text-align:center;">
                <div class="header-title">
                    Dirección General de Energía, Hidrocarburos y Minas
                </div>
                <div class="header-subtitle">
                    INFORME DE ENTREGA DE PRODUCTO
                </div>
                <div class="header-info">
                    Desde: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                    &nbsp;&nbsp; Hasta: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </div>
                <div class="header-info" style="font-weight:bold;">
                    RECURSOS PROPIOS &nbsp; CEL
                </div>
            </td>

            <td style="width:20%; text-align:right;">
                <img src="{{ public_path('logo_azul.png') }}" height="55">
            </td>
        </tr>
    </table>

    <hr style="border:1px solid #000; margin-bottom:10px;">

    {{-- TABLA MATRIZ --}}
    <table class="report-table">
        <thead>
            <tr>
                <th style="width:26%;">Producto</th>
                <th style="width:6%;">Unidad</th>

                @foreach ($offices as $office)
                    <th style="
                        width: 3%;
                        writing-mode: vertical-rl;
                        transform: rotate(180deg);
                        font-size: 10px;
                    ">
                        {{ strtoupper($office->name) }}
                    </th>
                @endforeach

                <th style="width:6%;">TOTAL</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($products as $productRows)
                @php
                    $first    = $productRows->first();
                    $total    = 0;
                    $byOffice = $productRows->keyBy('office_id');
                @endphp

                <tr>
                    <td class="product-name">
                        {{ $first->product_name }}
                    </td>
                    <td class="text-center">
                        {{ $first->measure_name }}
                    </td>

                    @foreach ($offices as $office)
                        @php
                            $qty = $byOffice[$office->id]->quantity ?? 0;
                            $total += $qty;
                        @endphp
                        <td class="text-right">
                            {{ $qty ?: '' }}
                        </td>
                    @endforeach

                    <td class="text-right total-col">
                        {{ $total }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br><br>

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
