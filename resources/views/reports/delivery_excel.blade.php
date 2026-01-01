<table>
    <thead>
        <tr>
            <th colspan="{{ 3 + $offices->count() }}" style="text-align:center; font-weight:bold;">
                DIRECCIÓN GENERAL DE ENERGÍA, HIDROCARBUROS Y MINAS
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + $offices->count() }}" style="text-align:center;">
                INFORME DE ENTREGA DE PRODUCTO
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + $offices->count() }}" style="text-align:center;">
                Desde: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                &nbsp; | &nbsp;
                Hasta: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + $offices->count() }}" style="text-align:center; font-weight:bold;">
                RECURSOS PROPIOS &nbsp; CEL
            </th>
        </tr>
        <tr></tr>

        <tr>
            <th>Producto</th>
            <th>Unidad</th>

            @foreach ($offices as $office)
                <th>
                    {{ strtoupper($office->name) }}
                </th>
            @endforeach

            <th>TOTAL</th>
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
                <td>{{ $first->product_name }}</td>
                <td>{{ $first->measure_name }}</td>

                @foreach ($offices as $office)
                    @php
                        $qty = $byOffice[$office->id]->quantity ?? 0;
                        $total += $qty;
                    @endphp
                    <td>
                        {{ $qty ?: '' }}
                    </td>
                @endforeach

                <td><strong>{{ $total }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

<br><br>

<table style="width:100%; text-align:center;">
    <tr>
        <td colspan="{{ 3 + $offices->count() }}">
            __________________________________________
        </td>
    </tr>
    <tr>
        <td colspan="{{ 3 + $offices->count() }}">
            <strong>Licda. Dora Jeannette Timas Trujillo</strong>
        </td>
    </tr>
    <tr>
        <td colspan="{{ 3 + $offices->count() }}">
            Gerente Administrativa
        </td>
    </tr>
    <tr>
        <td colspan="{{ 3 + $offices->count() }}">
            Dirección General de Energía, Hidrocarburos y Minas
        </td>
    </tr>
</table>
