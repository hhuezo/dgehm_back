<!DOCTYPE html>
<html>
<head>
    <title>Acta de Recepción</title>
    <style>
        /* Aquí va el CSS para replicar el formato del PDF DAF-F-GA-08 */
        body { font-family: sans-serif; }
        .data-field { border-bottom: 1px solid black; display: inline-block; padding: 0 5px; }
    </style>
</head>
<body>
    <h1>ACTA DE RECEPCIÓN DE INSUMOS DE ALMACÉN</h1>
    <p>Dirección General de Energía, Hidrocarburos y Minas</p>

    <p>San Salvador Centro, a las
        <span class="data-field">{{ $acta_time_part }}</span>
        horas y
        <span class="data-field">{{ $acta_minutes_part }}</span>
        minutos del día
        <span class="data-field">{{ $acta_date_part }}</span>
        en las instalaciones de la Dirección General de Energía, Hidrocarburos y Minas...
    </p>

    <p>reunidos el(la) Sr(Sra.).
        <span class="data-field">{{ $order->supplier_representative }}</span>
        en representación de
        <span class="data-field">{{ $order->supplier->name }}</span>
        ...
    </p>

    <p>proceso de compra No.
        <span class="data-field">{{ $order->order_number }}</span>,
        compromiso presupuestario No.
        <span class="data-field">{{ $order->budget_commitment_number }}</span>
        ...
    </p>

    <p>por un monto total de
        <span class="data-field">US$ {{ number_format($order->total_amount, 2) }}</span>
        emitida por el proveedor
        <span class="data-field">{{ $order->supplier->name }}</span>.
    </p>

    {{-- Aquí irían las firmas y los datos del Gerente y Técnico Administrativo --}}

</body>
</html>
