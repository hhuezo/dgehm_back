<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Traslado de Activo Fijo</title>
    <style>
        @page {
            margin: 108px 36px 48px 36px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #150D2D;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .pdf-header {
            position: fixed;
            top: -88px;
            left: 0;
            right: 0;
            height: 88px;
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
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.1px;
            color: #150D2D;
            margin: 4px 0 0;
            padding: 0 6px;
            line-height: 1.25;
            text-transform: uppercase;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .form-table th,
        .form-table td {
            border: 1px solid #000;
            padding: 4px 4px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .center { text-align: center; }
        .left { text-align: left; }
        .bold { font-weight: bold; }
        .small { font-size: 9px; }
        .xsmall { font-size: 8px; }
        .italic { font-style: italic; }
        .upper { text-transform: uppercase; }

        .bg-muted {
            background-color: #f3f3f3;
        }

        .bg-section {
            background-color: #e5e5e5;
        }

        .half-cell { width: 50%; }

        .check-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            text-align: center;
            line-height: 11px;
            font-size: 10px;
            font-weight: bold;
        }

        .asset-row td {
            height: 22px;
            font-size: 9px;
        }

        .sign-row td {
            height: 42px;
        }

        .sign-row-sm td {
            height: 36px;
        }

        .obs-line {
            border-bottom: 1px solid #000;
            height: 16px;
            margin-top: 4px;
        }

        .obs-wrap {
            padding: 6px 4px 8px;
        }

        .pdf-footer {
            position: fixed;
            bottom: -36px;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #150D2D;
        }
    </style>
</head>
<body>
@php
    $fecha = \Carbon\Carbon::parse($transfer->date);

    $unitName = $unitName
        ?? $transfer->personReceives?->organizationalUnit?->name
        ?? $transfer->organizationalUnit?->name
        ?? '';
    $receiverName = $transfer->personReceives
        ? trim(($transfer->personReceives->name ?? '') . ' ' . ($transfer->personReceives->lastname ?? ''))
        : ($receiver
            ? trim(($receiver->name ?? '') . ' ' . ($receiver->lastname ?? ''))
            : '');
    $delivererName = $transfer->personDelivers
        ? trim(($transfer->personDelivers->name ?? '') . ' ' . ($transfer->personDelivers->lastname ?? ''))
        : ($deliverer
            ? trim(($deliverer->name ?? '') . ' ' . ($deliverer->lastname ?? ''))
            : '');

    $detailRows = $transfer->details->values()->all();
    $rows = array_pad($detailRows, 10, null);

    $escudoPath = public_path('escudo.png');
    $logoPath = public_path('logo_azul.png');

    $formatDate = function ($date) {
        return $date ? $date->format('d/m/Y') : '';
    };

    $inventoryNumber = function ($asset) {
        if (! $asset) {
            return '';
        }
        $code = trim((string) ($asset->code ?? ''));
        $correlative = trim((string) ($asset->correlative ?? ''));
        if ($code && $correlative) {
            return $code . '-' . $correlative;
        }
        return $code ?: $correlative;
    };

    $brandModel = function ($asset) {
        if (! $asset) {
            return '';
        }
        $brand = trim((string) ($asset->brand ?? ''));
        $model = trim((string) ($asset->model ?? ''));
        if ($brand && $model) {
            return $brand . ' / ' . $model;
        }
        return $brand ?: $model;
    };

    $mark = function ($checked) {
        return $checked ? 'X' : '';
    };
@endphp

<div class="pdf-header">
    <table class="header-logos">
        <tr>
            <td class="logo-left">
                @if (file_exists($escudoPath))
                    <img src="{{ $escudoPath }}" width="54" alt="">
                @endif
            </td>
            <td class="logo-spacer"></td>
            <td class="logo-right">
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" height="42" alt="">
                @endif
            </td>
        </tr>
    </table>

    <div class="header-title">
        Ficha de traslado de activo fijo
    </div>
</div>

{{-- Fecha --}}
<table class="form-table" style="margin-top: 6px;">
    <tr>
        <td class="bold small half-cell bg-muted">Fecha de solicitud (DD/MM/AAAA)</td>
        <td class="center bold half-cell">{{ $formatDate($fecha) }}</td>
    </tr>
</table>

{{-- Tipo de movimiento --}}
<table class="form-table" style="margin-top: -1px;">
    <colgroup>
        <col style="width:38%;">
        <col style="width:12%;">
        <col style="width:38%;">
        <col style="width:12%;">
    </colgroup>
    <tr>
        <td class="small left">Traslado permanente (marque con X)</td>
        <td class="center bold">
            <span class="check-box">{{ $mark(true) }}</span>
        </td>
        <td class="small left">Traslado temporal (marque con X)</td>
        <td class="center bold">
            <span class="check-box">{{ $mark(false) }}</span>
        </td>
    </tr>
</table>

{{-- Unidad del receptor --}}
<table class="form-table" style="margin-top: -1px;">
    <tr>
        <td class="bold small bg-muted left">
            Unidad organizativa de quien recibe:
            <span class="upper">{{ $unitName !== '' ? ' ' . $unitName : '' }}</span>
        </td>
    </tr>
</table>

{{-- Persona que entrega --}}
<table class="form-table" style="margin-top: -1px;">
    <colgroup>
        <col style="width:42%;">
        <col style="width:33%;">
        <col style="width:25%;">
    </colgroup>
    <tr class="bg-muted">
        <td class="center bold xsmall upper">Nombre de la persona que entrega el activo:</td>
        <td class="center bold xsmall upper">Firma</td>
        <td class="center bold xsmall upper">Sello</td>
    </tr>
    <tr class="sign-row">
        <td class="left bold">{{ $delivererName }}</td>
        <td></td>
        <td></td>
    </tr>
</table>

{{-- Persona que recibe --}}
<table class="form-table" style="margin-top: -1px;">
    <colgroup>
        <col style="width:42%;">
        <col style="width:33%;">
        <col style="width:25%;">
    </colgroup>
    <tr class="bg-muted">
        <td class="center bold xsmall upper">Nombre de la persona que recibe el activo:</td>
        <td class="center bold xsmall upper">Firma</td>
        <td class="center bold xsmall upper">Sello</td>
    </tr>
    <tr class="sign-row">
        <td class="left bold">{{ $receiverName }}</td>
        <td></td>
        <td></td>
    </tr>
</table>

{{-- Tabla de activos --}}
<table class="form-table" style="margin-top: -1px;">
    <tr>
        <td class="xsmall center bg-section bold" style="padding: 3px 4px;">
            Detalle de bienes — Traslado
        </td>
    </tr>
</table>
<table class="form-table" style="margin-top: -1px;">
    <colgroup>
        <col style="width:5%;">
        <col style="width:24%;">
        <col style="width:14%;">
        <col style="width:16%;">
        <col style="width:12%;">
        <col style="width:29%;">
    </colgroup>
    <thead>
        <tr class="bold center xsmall upper bg-muted">
            <th>N°</th>
            <th>Nombre del bien</th>
            <th>No. De Inventario</th>
            <th>Marca / Modelo</th>
            <th>Traslado</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $index => $detail)
            @php
                $asset = $detail?->fixedAsset;
                $detailObservation = $detail?->observation ?? '';
            @endphp
            <tr class="asset-row {{ $index % 2 === 1 ? 'bg-muted' : '' }}">
                <td class="center bold">{{ $index + 1 }}</td>
                <td class="left">{{ $asset?->category?->name ?? '' }}</td>
                <td class="center">{{ $inventoryNumber($asset) }}</td>
                <td class="center">{{ $brandModel($asset) }}</td>
                <td class="center bold">{{ $detail ? 'X' : '' }}</td>
                <td class="left">{{ $detailObservation }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Firmas UAF --}}
<table class="form-table" style="margin-top: -1px;">
    <colgroup>
        <col style="width:42%;">
        <col style="width:33%;">
        <col style="width:25%;">
    </colgroup>
    <tr class="bg-muted">
        <td class="center bold xsmall upper">Nombre del colaborador(a) de activo fijo que atiende la solicitud</td>
        <td class="center bold xsmall upper">Firma</td>
        <td class="center bold xsmall upper">Sello UAF</td>
    </tr>
    <tr class="sign-row-sm">
        <td></td>
        <td></td>
        <td></td>
    </tr>
</table>

{{-- Observaciones --}}
<table class="form-table" style="margin-top: -1px;">
    <tr>
        <td class="obs-wrap left">
            <span class="bold">Observaciones:</span>
            @if (filled($transfer->observation))
                <span>{{ $transfer->observation }}</span>
            @endif
            <div class="obs-line"></div>
            <div class="obs-line"></div>
            <div class="obs-line"></div>
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
