<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align:center; font-weight:bold;">
                DIRECCIÓN GENERAL DE ENERGÍA, HIDROCARBUROS Y MINAS
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align:center;">
                INFORME INICIAL DE PRODUCTO
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align:center;">
                AL {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align:center; font-weight:bold;">
                RECURSOS PROPIOS &nbsp; CEL
            </th>
        </tr>
        <tr></tr>

        <tr>
            <th>Código</th>
            <th>Cuenta</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Precio Unitario</th>
            <th>Valor</th>
        </tr>
    </thead>

    <tbody>
    @php
        $groupedByAccount = $stock->groupBy('accounting_account_id');
        $grandTotal = 0;
    @endphp

    @foreach ($groupedByAccount as $accountStock)
        @php
            $rowspan = $accountStock->count();
            $accountTotal = 0;
            $firstRow = true;
        @endphp

        @foreach ($accountStock as $item)
            @php
                $rowValue = $item->stock_quantity * $item->unit_price;
                $accountTotal += $rowValue;
            @endphp

            <tr>
                @if ($firstRow)
                    <td rowspan="{{ $rowspan }}">
                        {{ $item->account_code }}
                    </td>
                    <td rowspan="{{ $rowspan }}">
                        {{ $item->account_name }}
                    </td>
                    @php $firstRow = false; @endphp
                @endif

                <td>{{ $item->product_name }}</td>
                <td>{{ number_format($item->stock_quantity, 0) }}</td>
                <td>{{ $item->measure_name }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($rowValue, 2) }}</td>
            </tr>
        @endforeach

        @php $grandTotal += $accountTotal; @endphp

        {{-- SUBTOTAL POR ESPECÍFICO --}}
        <tr>
            <td colspan="6" style="text-align:right; font-weight:bold;">
                Sub total por específico:
            </td>
            <td style="font-weight:bold;">
                {{ number_format($accountTotal, 2) }}
            </td>
        </tr>
    @endforeach

    {{-- TOTAL GENERAL --}}
    <tr>
        <td colspan="6" style="text-align:right; font-weight:bold;">
            TOTAL GENERAL:
        </td>
        <td style="font-weight:bold;">
            {{ number_format($grandTotal, 2) }}
        </td>
    </tr>

    </tbody>
</table>


<table style="width:100%; margin-top:30px;">
    <tr>
        <td colspan="7" style="text-align:center;">
            __________________________________________
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align:center; font-weight:bold;">
            Licda. Dora Jeannette Timas Trujillo
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align:center;">
            Gerente Administrativa
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align:center;">
            Dirección General de Energía, Hidrocarburos y Minas
        </td>
    </tr>
</table>
