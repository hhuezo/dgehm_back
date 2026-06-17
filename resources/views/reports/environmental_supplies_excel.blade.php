<table>
    <thead>
        <tr>
            <th colspan="13" style="text-align:center; font-weight:bold;">
                DIRECCIÓN GENERAL DE ENERGÍA, HIDROCARBUROS Y MINAS
            </th>
        </tr>
        <tr>
            <th colspan="13" style="text-align:center; font-weight:bold;">
                MEDIO AMBIENTE - INSUMOS DE ALMACÉN
            </th>
        </tr>
        <tr>
            <th colspan="13" style="text-align:center;">
                Desde: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                &nbsp; | &nbsp;
                Hasta: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th>MES</th>
            <th>FECHA</th>
            <th>NOMBRE DE UNIDAD SOLICITANTE</th>
            <th>NUMERO DE SOLICITUD</th>
            <th>N° DE ESPECIFICO</th>
            <th>NOMBRE-DETALLE DE PRODUCTO</th>
            <th>CLASIFICACIÓN</th>
            <th>UNIDAD DE MEDIDA</th>
            <th>CANTIDAD SOLICITADA</th>
            <th>CANTIDAD ENTREGADA</th>
            <th>COSTO UNITARIO</th>
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
