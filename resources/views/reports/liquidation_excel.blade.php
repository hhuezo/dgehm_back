<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight:bold;">
                REPORTE DE LIQUIDACIÓN DE INVENTARIO
            </th>
        </tr>
        <tr>
            <th colspan="7">
                Desde: {{ $startDate }} | Hasta: {{ $endDate }}
            </th>
        </tr>
        <tr>
            <th>Código</th>
            <th>Concepto</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Precio Unitario</th>
            <th>Valor</th>
        </tr>
    </thead>

    <tbody>
    @foreach ($accounts as $account)

        <tr>
            <td>{{ $account->account_code }}</td>
            <td colspan="6"><strong>{{ $account->account_name }}</strong></td>
        </tr>

        @php
            $productsByAccount = $products->where(
                'accounting_account_id',
                $account->accounting_account_id
            );
        @endphp

        @foreach ($productsByAccount as $product)
            <tr>
                <td></td>
                <td></td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->quantity }}</td>
                <td>{{ $product->measure_name }}</td>
                <td>{{ $product->unit_price }}</td>
                <td>{{ $product->product_total }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="6"><strong>SUB TOTAL POR ESPECÍFICO</strong></td>
            <td><strong>{{ $account->subtotal }}</strong></td>
        </tr>

    @endforeach
    </tbody>
</table>
