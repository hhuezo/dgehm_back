<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Solicitud de Insumos de Almacén</title>
    <style>
        @page {
            margin: 128px 42px 62px 42px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #150D2D;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .pdf-header {
            position: fixed;
            top: -92px;
            left: 0;
            right: 0;
            height: 92px;
        }

        .header-logos td {
            vertical-align: middle;
            padding: 0;
        }

        .header-logos .logo-left {
            width: 14%;
            text-align: left;
        }

        .header-logos .logo-right {
            width: 36%;
            text-align: right;
        }

        .header-logos .logo-spacer {
            width: 50%;
        }

        .header-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.2px;
            color: #150D2D;
            margin: 2px 0 0;
            padding: 0 8px;
            line-height: 1.25;
        }

        .header-date-wrap {
            width: 100%;
            margin-top: 2px;
        }

        .date-box {
            width: 188px;
            margin-left: auto;
            margin-right: 0;
            border-collapse: collapse;
        }

        .date-box td {
            border: 1px solid #000;
            text-align: center;
            padding: 3px 2px;
            font-size: 10px;
            height: 16px;
        }

        .date-box .date-label {
            border: none;
            text-align: right;
            padding-right: 8px;
            font-weight: bold;
            font-size: 11px;
            width: 46px;
        }

        .date-box .date-label-row td {
            border: none;
            font-size: 9px;
            padding-top: 1px;
            height: auto;
        }

        .border th,
        .border td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: middle;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }

        .info-table {
            margin-top: 28px;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 0 0 10px;
            vertical-align: bottom;
        }

        .info-label {
            width: 38%;
            font-weight: bold;
            padding-bottom: 2px;
            white-space: nowrap;
        }

        .info-value {
            border-bottom: 1px solid #000;
            text-align: center;
            padding-bottom: 2px;
            min-height: 16px;
        }

        .footnote {
            font-size: 10px;
            margin: 4px 0 10px;
        }

        .obs-title {
            font-weight: bold;
            margin: 0 0 4px;
        }

        .obs-line {
            border-bottom: 1px solid #000;
            min-height: 20px;
            margin-bottom: 8px;
        }

        .sign-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .sign-block {
            font-size: 11px;
            line-height: 1.55;
        }

        .sign-name-line {
            margin-top: 6px;
        }

        .delivered-block {
            text-align: center;
        }

        .pdf-footer {
            position: fixed;
            bottom: -48px;
            left: 0;
            right: 0;
            font-size: 10px;
            color: #150D2D;
        }
    </style>
</head>
<body>
@php
    $fecha = \Carbon\Carbon::parse($request->date);
    $rows = $products->values()->all();
    $isCompleted = (int) $request->status_id === 4;
    $escudoPath = public_path('escudo.png');
    $logoPath = public_path('logo_azul.png');
@endphp

{{-- ENCABEZADO FIJO (logos + título + fecha) --}}
<div class="pdf-header">
    <table class="header-logos">
        <tr>
            <td class="logo-left">
                @if (file_exists($escudoPath))
                    <img src="{{ $escudoPath }}" width="58" alt="">
                @endif
            </td>
            <td class="logo-spacer"></td>
            <td class="logo-right">
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" height="46" alt="">
                @endif
            </td>
        </tr>
    </table>

    <div class="header-title">FICHA DE SOLICITUD DE INSUMOS DE ALMACÉN</div>

    <table class="header-date-wrap">
        <tr>
            <td style="border:none; padding:0;">
                <table class="date-box">
                    <tr>
                        <td class="date-label">Fecha</td>
                        <td class="bold">{{ $fecha->format('d') }}</td>
                        <td class="bold">{{ $fecha->format('m') }}</td>
                        <td class="bold">{{ $fecha->format('Y') }}</td>
                    </tr>
                    <tr class="date-label-row">
                        <td></td>
                        <td>DD</td>
                        <td>MM</td>
                        <td>AAAA</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

{{-- DATOS DEL SOLICITANTE --}}
<table class="info-table">
    <tr>
        <td class="info-label">Nombre del (la) solicitante:</td>
        <td class="info-value">{{ $request->requester_name }}</td>
    </tr>
    <tr>
        <td class="info-label">Unidad Organizativa:</td>
        <td class="info-value">{{ $request->organizational_unit_name }}</td>
    </tr>
    <tr>
        <td class="info-label">Número de extensión telefónica:</td>
        <td class="info-value">{{ $request->phone_extension ?? '' }}</td>
    </tr>
</table>

{{-- DETALLE DE PRODUCTOS --}}
<table class="border">
    <thead>
        <tr class="bold center">
            <th style="width:33%;">Producto</th>
            <th style="width:15%;">Unidad<br>de medida*</th>
            <th style="width:14%;">Cantidad<br>Solicitada</th>
            <th style="width:14%;">Cantidad<br>Entregada</th>
            <th style="width:24%;">Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $item)
            <tr style="height:28px;">
                <td>{{ $item->product_name }}</td>
                <td class="center">{{ $item->measure_name }}</td>
                <td class="center">
                    {{ rtrim(rtrim(number_format((float) $item->requested_quantity, 2, '.', ''), '0'), '.') }}
                </td>
                <td class="center">
                    @if ($isCompleted && $item->delivered_quantity !== null && (float) $item->delivered_quantity > 0)
                        {{ rtrim(rtrim(number_format((float) $item->delivered_quantity, 2, '.', ''), '0'), '.') }}
                    @endif
                </td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="center">Sin productos registrados</td>
            </tr>
        @endforelse
    </tbody>
</table>

<p class="footnote">* Libras, galones, unidades, paquetes, fardos, cajas.</p>

<p class="obs-title">Observaciones generales:</p>
<div class="obs-line">{{ $request->observation }}</div>
<div class="obs-line"></div>

<br>

{{-- FIRMAS --}}
<table>
    <tr>
        <td style="width:50%; vertical-align:top; padding-right:12px;">
            <div class="sign-title">Solicitado por:</div>
            <div class="sign-block">
                <br>
                F.___________________________________<br>
                <div class="sign-name-line">Nombre: _____________________________</div>
                <div class="sign-name-line">Cargo : _____________________________</div>
            </div>
        </td>
        <td style="width:50%; vertical-align:top; padding-left:12px;">
            <div class="sign-title">Jefe (a) inmediato (a) de quien solicita:</div>
            <div class="sign-block">
                <br>
                F.________________________________<br>
                <div class="sign-name-line">Nombre: ___________________________</div>
                <div class="sign-name-line">Cargo: ____________________________</div>
            </div>
        </td>
    </tr>
</table>

<br><br>

<table>
    <tr>
        <td class="center">
            <div class="sign-title center">Entregado por:</div>
            <div class="delivered-block sign-block">
                <br>
                F.______________________________________<br>
                <div class="sign-name-line">
                    Nombre:
                    @if ($isCompleted && !empty($request->delivered_name))
                        {{ $request->delivered_name }}
                    @else
                        ______________________________________
                    @endif
                </div>
                <div class="sign-name-line">Técnico Administrativo (Encargado(a) de almacén)</div>
            </div>
        </td>
    </tr>
</table>

{{-- PIE DE PÁGINA --}}
<table class="pdf-footer">
    <tr>
        <td style="width:50%; text-align:left; vertical-align:top;">
            DAF-F-GA-10<br>
            13/01/2025
        </td>
        <td style="width:50%; text-align:right; vertical-align:top;">
            &nbsp;
        </td>
    </tr>
</table>

<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_script('
            $font = $fontMetrics->get_font("Arial", "normal");
            $pdf->text(468, 808, "Página $PAGE_NUM de $PAGE_COUNT", $font, 9);
        ');
    }
</script>
</body>
</html>
