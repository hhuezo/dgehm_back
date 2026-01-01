<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Liquidación de Inventario</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
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

        .account-header {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .subtotal {
            font-weight: bold;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <h3>REPORTE DE LIQUIDACIÓN DE INVENTARIO</h3>
    <p>
        <strong>Desde:</strong> {{ $startDate }}
        &nbsp;&nbsp;
        <strong>Hasta:</strong> {{ $endDate }}
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 7%">Código</th>
                <th style="width: 51%">Concepto</th>
                <th style="width: 8%">Cantidad</th>
                <th style="width: 8%">Unidad</th>
                <th style="width: 10%">Precio Unitario</th>
                <th style="width: 16%">Valor</th>
            </tr>
        </thead>

        <tbody>
        @foreach ($accounts as $account)

            {{-- ENCABEZADO DE CUENTA --}}
            <tr class="account-header">
                <td class="text-center">
                    {{ $account->account_code }}
                </td>
                <td colspan="5">
                    {{ $account->account_name }}
                </td>
            </tr>

            @php
                $productsByAccount = $products->where(
                    'accounting_account_id',
                    $account->accounting_account_id
                );
            @endphp

            {{-- PRODUCTOS --}}
            @foreach ($productsByAccount as $product)
                <tr>
                    <td></td>
                    <td>{{ $product->product_name }}</td>
                    <td class="text-right">
                        {{ number_format($product->quantity, 0) }}
                    </td>
                    <td class="text-center">
                        {{ $product->measure_name }}
                    </td>
                    <td class="text-right">
                        ${{ number_format($product->unit_price, 2) }}
                    </td>
                    <td class="text-right">
                        ${{ number_format($product->product_total, 2) }}
                    </td>
                </tr>
            @endforeach

            {{-- SUBTOTAL POR CUENTA --}}
            <tr class="subtotal">
                <td colspan="5" class="text-right">
                    SUB TOTAL POR ESPECÍFICO:
                </td>
                <td class="text-right">
                    ${{ number_format($account->subtotal, 2) }}
                </td>
            </tr>

        @endforeach
        </tbody>
    </table>

</body>
</html>
