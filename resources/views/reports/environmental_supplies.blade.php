<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: bold; }
        .title { text-align: center; font-weight: bold; margin-bottom: 8px; }
        .subtitle { text-align: center; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="title">DIRECCIÓN GENERAL DE ENERGÍA, HIDROCARBUROS Y MINAS</div>
    <div class="title">MEDIO AMBIENTE - INSUMOS DE ALMACÉN</div>
    <div class="subtitle">
        Desde: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        | Hasta: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>MES</th>
                <th>FECHA</th>
                <th>NOMBRE DE UNIDAD SOLICITANTE</th>
                <th>N° SOLICITUD</th>
                <th>N° ESPECIFICO</th>
                <th>PRODUCTO</th>
                <th>CLASIFICACIÓN</th>
                <th>UNIDAD</th>
                <th>C. SOLICITADA</th>
                <th>C. ENTREGADA</th>
                <th>COSTO U.</th>
                <th>TOTAL</th>
                <th>FONDOS</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->month_name }}</td>
                    <td>{{ $row->delivery_date_formatted }}</td>
                    <td>{{ $row->organizational_unit_name }}</td>
                    <td>{{ $row->request_number }}</td>
                    <td>{{ $row->account_code }}</td>
                    <td>{{ $row->product_detail }}</td>
                    <td>{{ $row->account_name }}</td>
                    <td>{{ $row->measure_name }}</td>
                    <td>{{ number_format((float) $row->requested_quantity, 0, '.', '') }}</td>
                    <td>{{ number_format((float) $row->delivered_quantity, 0, '.', '') }}</td>
                    <td>{{ number_format((float) $row->unit_price, 2, '.', '') }}</td>
                    <td>{{ number_format((float) $row->total, 2, '.', '') }}</td>
                    <td>{{ $row->funding_source_name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center;">
                        No hay entregas de productos con reporte a medio ambiente en el rango seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
