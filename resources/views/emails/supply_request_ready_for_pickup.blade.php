<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud lista para retirar</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">
    @php
        $requester = $supplyRequest->requester;
        $requesterName = trim(($requester->name ?? '') . ' ' . ($requester->lastname ?? ''));
        $organizationalUnit = $supplyRequest->organizationalUnit?->name;
        $requestDate = $supplyRequest->date
            ? \Illuminate\Support\Carbon::parse($supplyRequest->date)->format('d/m/Y')
            : '—';
        $deliveryDate = $supplyRequest->delivery_date
            ? \Illuminate\Support\Carbon::parse($supplyRequest->delivery_date)->format('d/m/Y')
            : '—';
    @endphp

    <p>Estimado(a) {{ $requesterName ?: 'solicitante' }},</p>

    <p>
        Le informamos que su solicitud de insumos <strong>#{{ $supplyRequest->id }}</strong>
        ha sido procesada y está <strong>lista para ser retirada</strong> en almacén.
    </p>

    <ul>
        <li><strong>Fecha de solicitud:</strong> {{ $requestDate }}</li>
        <li><strong>Fecha de entrega registrada:</strong> {{ $deliveryDate }}</li>
        @if ($organizationalUnit)
            <li><strong>Unidad organizativa:</strong> {{ $organizationalUnit }}</li>
        @endif
    </ul>

    @if ($supplyRequest->observation)
        <p><strong>Observación:</strong> {{ $supplyRequest->observation }}</p>
    @endif

    <p>Por favor, acérquese al almacén para retirar los insumos correspondientes.</p>

    <p style="color: #666; font-size: 12px; margin-top: 24px;">
        Este es un mensaje automático del sistema. No responda a este correo.
    </p>
</body>
</html>
