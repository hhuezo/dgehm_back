<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Asignación de Activo Fijo</title>
    <style>
        @page {
            margin: 108px 36px 56px 36px;
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
        }

        .form-table th,
        .form-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .center { text-align: center; }
        .left { text-align: left; }
        .bold { font-weight: bold; }
        .small { font-size: 9px; }
        .xsmall { font-size: 8px; }

        .half-cell {
            width: 50%;
        }

        .asset-row td {
            height: 24px;
            font-size: 9px;
        }

        .sign-row td {
            height: 34px;
        }

        .obs-row td {
            height: 22px;
            vertical-align: bottom;
        }

        .pdf-footer {
            position: fixed;
            bottom: -44px;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #150D2D;
        }
    </style>
</head>
<body>
@php
    $fecha = \Carbon\Carbon::parse($assignment->date);

    $unitName = $assignment->organizationalUnit->name ?? '';
    $personName = $assignment->person
        ? trim(($assignment->person->name ?? '') . ' ' . ($assignment->person->lastname ?? ''))
        : '';

    $detailRows = $assignment->details->values()->all();
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
        FICHA DE ASIGNACIÓN DE ACTIVO FIJO
    </div>
</div>

<table class="form-table" style="margin-top: 6px;">
    <tr>
        <td class="bold small half-cell">Fecha de solicitud (DD/MM/AAAA)</td>
        <td class="center bold half-cell">{{ $formatDate($fecha) }}</td>
    </tr>

    <tr>
        <td class="bold small half-cell">Unidad solicitante:</td>
        <td class="left half-cell">{{ $unitName }}</td>
    </tr>
</table>

<table class="form-table" style="margin-top: -1px;">
    <colgroup>
        <col style="width:52%;">
        <col style="width:24%;">
        <col style="width:24%;">
    </colgroup>
    <tr>
        <td class="bold small">
            Nombre de la persona a la que se asigna el activo:
        </td>
        <td class="center bold small">Firma</td>
        <td class="center bold small">Sello</td>
    </tr>
    <tr class="sign-row">
        <td class="left">{{ $personName }}</td>
        <td></td>
        <td></td>
    </tr>
</table>

<table class="form-table" style="margin-top: -1px;">
    <colgroup>
        <col style="width:5%;">
        <col style="width:27%;">
        <col style="width:14%;">
        <col style="width:16%;">
        <col style="width:6%;">
        <col style="width:32%;">
    </colgroup>
    <thead>
        <tr class="bold center small">
            <th>N°</th>
            <th>Nombre del bien</th>
            <th>No. De Inventario</th>
            <th>Marca / Modelo</th>
            <th>A</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $index => $detail)
            @php
                $asset = $detail?->fixedAsset;
                $detailObservation = $detail?->observation ?? '';
            @endphp
            <tr class="asset-row">
                <td class="center">{{ $index + 1 }}</td>
                <td class="left">{{ $asset?->description ?? '' }}</td>
                <td class="center">{{ $inventoryNumber($asset) }}</td>
                <td class="center">{{ $brandModel($asset) }}</td>
                <td class="center bold">{{ $detail ? 'X' : '' }}</td>
                <td class="left">{{ $detailObservation }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="form-table" style="margin-top: -1px;">
    <tr>
        <td class="bold small">Observaciones:</td>
    </tr>
    <tr class="obs-row">
        <td>{{ $assignment->observation }}</td>
    </tr>
    <tr class="obs-row">
        <td>&nbsp;</td>
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
