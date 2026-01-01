<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Devolución de Insumos</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

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
        FICHA DE DEVOLUCIÓN DE INSUMOS DE ALMACÉN
    </h3>

    {{-- DATOS GENERALES --}}
    <table style="margin-bottom:10px;">
        <tr>
            <td style="width:60%;">
                <strong>Nombre del (la) persona que devuelve insumos:</strong>
                {{ $return->returned_name }}
            </td>
            <td style="width:40%;">
                <strong>Fecha:</strong>
                {{ \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Unidad Organizativa:</strong>
                {{ $return->office_name }}
            </td>
            <td>
                <strong>Número de extensión telefónica:</strong>
                {{ $return->phone_extension ?? '—' }}
            </td>
        </tr>
    </table>

    {{-- TABLA DE PRODUCTOS --}}
    <table class="border">
        <thead>
            <tr class="bold center">
                <th style="width:40%;">Producto</th>
                <th style="width:20%;">Unidad de medida*</th>
                <th style="width:20%;">Cantidad Devuelta</th>
                <th style="width:20%;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="center">{{ $item->measure_name }}</td>
                    <td class="center">{{ $item->returned_quantity }}</td>
                    <td>{{ $item->observation }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-size:10px; margin-top:3px;">
        * Libras, galones, unidades, paquetes, fardos, cajas.
    </p>

    {{-- OBSERVACIONES GENERALES --}}
    <br>
    <strong>Observaciones generales:</strong>
    <p>{{ $return->general_observations }}</p>

    <br><br>

    {{-- FIRMAS --}}
    <table>
        <tr>
            <td class="center" style="width:50%;">
                F. _______________________________<br>
                {{ $return->returned_name }}<br>
                <strong>Devuelto por</strong>
            </td>
            <td class="center" style="width:50%;">
                F. _______________________________<br>
                {{ $return->supervisor_name ?? '____________________' }}<br>
                <strong>Jefe inmediato</strong>
            </td>
        </tr>
    </table>

    <br><br>

    <table>
        <tr>
            <td class="center">
                F. _______________________________<br>
                {{ $return->received_name ?? '____________________' }}<br>
                <strong>Técnico Administrativo (Encargado(a) de almacén)</strong>
            </td>
        </tr>
    </table>

    <br><br>

    {{-- CÓDIGO DEL FORMULARIO --}}
    <div class="right bold">
        DAF-F-GA-09<br>
        13/01/2025
    </div>

</body>
</html>
