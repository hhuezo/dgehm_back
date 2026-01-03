<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Solicitud de Insumos</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* SOLO tablas del contenido */
        .border th,
        .border td {
            border: 1px solid #000;
            padding: 4px;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }

        h3 {
            margin: 5px 0;
        }
    </style>
</head>

<body>

    {{-- HEADER (IMÁGENES) --}}
    <table style="width:100%; margin-bottom:10px;">
        <tr>
            <td style="width:50%; text-align:left;">
                <img src="{{ public_path('escudo.png') }}" width="50">
            </td>

            <td style="width:50%; text-align:right;">
                <img src="{{ public_path('logo_azul.png') }}" height="50">
            </td>
        </tr>
    </table>

    {{-- TÍTULO --}}
    <h3 class="center">
        FICHA DE SOLICITUD DE INSUMOS DE ALMACÉN
    </h3>

    {{-- DATOS GENERALES --}}
    <table style="margin-bottom:10px;">
        <tr>
            <td style="width:50%;">
                <strong>Fecha:</strong>
                {{ \Carbon\Carbon::parse($request->date)->format('d/m/Y') }}
            </td>
            <td style="width:50%;">
                <strong>Unidad Organizativa:</strong>
                {{ $request->office_name }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Nombre del solicitante:</strong>
                {{ $request->requester_name }}
            </td>
            <td>
                <strong>Número de extensión telefónica:</strong>
                —
            </td>
        </tr>
    </table>

    {{-- TABLA DE PRODUCTOS --}}
    <table class="border">
        <thead>
            <tr class="bold center">
                <th style="width:40%;">Producto</th>
                <th style="width:20%;">Unidad de Medida</th>
                <th style="width:20%;">Cantidad Entregada</th>
                <th style="width:20%;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="center">{{ $item->measure_name }}</td>
                    <td class="center">{{ $item->delivered_quantity }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- OBSERVACIONES --}}
    <br>
    <strong>Observaciones generales:</strong>
    <p>
        {{ $request->observation }}
    </p>

    <br><br>

    {{-- FIRMAS --}}
    <table>
        <tr>
            <td class="center" style="width:50%;">
                F. _______________________________<br>
                {{ $request->requester_name }}<br>
                <strong>Solicitado por</strong>
            </td>
            <td class="center" style="width:50%;">
                F. _______________________________<br>
                {{ $request->boss_name ?? '____________________' }}<br>
                <strong>Jefe inmediato</strong>
            </td>
        </tr>
    </table>

    <br><br>

    <table>
        <tr>
            <td class="center">
                F. _______________________________<br>
                {{ $request->delivered_name ?? '____________________' }}<br>
                <strong>Entregado por</strong>
            </td>
        </tr>
    </table>

    <br><br>

    {{-- CÓDIGO DEL FORMULARIO --}}
    <div class="right bold">
        DAF-F-GA-10<br>
        13/01/2025
    </div>

</body>
</html>
