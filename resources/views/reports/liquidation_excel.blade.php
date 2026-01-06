<table>
    <thead>
        <tr>
            <th colspan="6" style="font-weight:bold;">
                REPORTE DE LIQUIDACIÓN DE INVENTARIO
            </th>
        </tr>
        <tr>
            <th colspan="6">
                Desde: {{ $startDate }} | Hasta: {{ $endDate }}
            </th>
        </tr>
        <tr>
            <th>Código</th>
            <th>Concepto</th>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Precio Unitario</th>
            <th>Valor</th>
        </tr>
    </thead>

    <tbody>
    @foreach ($accounts as $account)

        {{-- ENCABEZADO DE CUENTA --}}
        <tr>
            <td>{{ $account->account_code }}</td>
            <td colspan="5">
                <strong>{{ $account->account_name }}</strong>
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
                <td>{{ number_format($product->quantity, 0) }}</td>
                <td>{{ $product->measure_name }}</td>
                <td>${{ number_format($product->unit_price, 2) }}</td>
                <td>${{ number_format($product->product_total, 2) }}</td>
            </tr>
        @endforeach

        {{-- SUBTOTAL POR CUENTA --}}
        <tr>
            <td colspan="5">
                <strong>SUB TOTAL POR ESPECÍFICO</strong>
            </td>
            <td>
                <strong>${{ number_format($account->subtotal, 2) }}</strong>
            </td>
        </tr>

    @endforeach
    </tbody>
</table>
