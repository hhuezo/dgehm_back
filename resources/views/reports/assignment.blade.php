<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Asignación y Desasignación de Activo Fijo</title>
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

        .name-cell {
            width: 52%;
        }

        .sign-cell {
            width: 24%;
        }

        .check-box {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #000;
            text-align: center;
            line-height: 10px;
            font-size: 8px;
            font-weight: bold;
            vertical-align: middle;
            margin-left: 3px;
        }

        .gray-row td {
            background-color: #d9d9d9;
            font-size: 9px;
            font-style: italic;
            text-align: center;
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
    $temporalStart = $assignment->temporal_start_date
        ? \Carbon\Carbon::parse($assignment->temporal_start_date)
        : null;
    $temporalEnd = $assignment->temporal_end_date
        ? \Carbon\Carbon::parse($assignment->temporal_end_date)
        : null;

    $isAssignmentPermanent = $assignment->is_assignment && $assignment->is_permanent;
    $isAssignmentTemporal = $assignment->is_assignment && ! $assignment->is_permanent;
    $isUnassignmentPermanent = $assignment->is_unassignment && $assignment->is_permanent;
    $isUnassignmentTemporal = $assignment->is_unassignment && ! $assignment->is_permanent;

    $unitName = $assignment->organizationalUnit->name ?? '';
    $personName = $person ? trim(($person->name ?? '') . ' ' . ($person->lastname ?? '')) : '';
    $collaboratorName = $collaborator
        ? trim(($collaborator->name ?? '') . ' ' . ($collaborator->lastname ?? ''))
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
        FICHA DE ASIGNACIÓN Y DESASIGNACIÓN PERMANENTE O TEMPORAL DE ACTIVO FIJO
    </div>
</div>

<table class="form-table" style="margin-top: 6px;">
    {{-- Fila 1: Fecha de solicitud --}}
    <tr>
        <td class="bold small half-cell">Fecha de solicitud (DD/MM/AAAA)</td>
        <td class="center bold half-cell">{{ $formatDate($fecha) }}</td>
    </tr>

    {{-- Fila 2: Asignación permanente / temporal --}}
    <tr>
        <td class="small half-cell">
            Asignación permanente (marque con X)
            <span class="check-box">{{ $isAssignmentPermanent ? 'X' : '' }}</span>
        </td>
        <td class="small half-cell">
            Asignación temporal (marque con X)
            <span class="check-box">{{ $isAssignmentTemporal ? 'X' : '' }}</span>
        </td>
    </tr>

    {{-- Fila 3: Desasignación permanente / temporal --}}
    <tr>
        <td class="small half-cell">
            Desasignación permanente (marque con X)
            <span class="check-box">{{ $isUnassignmentPermanent ? 'X' : '' }}</span>
        </td>
        <td class="small half-cell">
            Desasignación temporal (marque con X)
            <span class="check-box">{{ $isUnassignmentTemporal ? 'X' : '' }}</span>
        </td>
    </tr>

    {{-- Fila 4: Nota temporal --}}
    <tr class="gray-row">
        <td colspan="2">
            * Si la asignación es temporal indique el periodo de tiempo que se utilizará el activo.
        </td>
    </tr>

    {{-- Fila 5: Etiquetas fechas temporal --}}
    <tr>
        <td class="bold small half-cell center">Fecha inicial (DD/MM/AAAA)</td>
        <td class="bold small half-cell center">Fecha final (DD/MM/AAAA)</td>
    </tr>

    {{-- Fila 6: Valores fechas temporal (en blanco si no hay fecha) --}}
    <tr>
        <td class="center half-cell">
            @if ($formatDate($temporalStart))
                {{ $formatDate($temporalStart) }}
            @else
                &nbsp;
            @endif
        </td>
        <td class="center half-cell">
            @if ($formatDate($temporalEnd))
                {{ $formatDate($temporalEnd) }}
            @else
                &nbsp;
            @endif
        </td>
    </tr>

    {{-- Fila 7: Unidad solicitante --}}
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
        <col style="width:6%;">
        <col style="width:26%;">
    </colgroup>
    <thead>
        <tr class="bold center small">
            <th rowspan="2">N°</th>
            <th rowspan="2">Nombre del bien</th>
            <th rowspan="2">No. De Inventario</th>
            <th rowspan="2">Marca / Modelo</th>
            <th colspan="2">Asignación (A) / Desasignación (D)</th>
            <th rowspan="2">Observación</th>
        </tr>
        <tr class="bold center small">
            <th>A</th>
            <th>D</th>
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
                <td class="center bold">{{ $detail && $assignment->is_assignment ? 'X' : '' }}</td>
                <td class="center bold">{{ $detail && $assignment->is_unassignment ? 'X' : '' }}</td>
                <td class="left">{{ $detailObservation }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="form-table" style="margin-top: -1px;">
    <colgroup>
        <col style="width:52%;">
        <col style="width:24%;">
        <col style="width:24%;">
    </colgroup>
    <tr>
        <td class="bold small">
            Nombre del colaborador(a) de activo fijo que atiende la solicitud:
        </td>
        <td class="center bold small">Firma</td>
        <td class="center bold small">Sello UAF</td>
    </tr>
    <tr class="sign-row">
        <td class="left">{{ $collaboratorName }}</td>
        <td></td>
        <td></td>
    </tr>
</table>

<table class="form-table" style="margin-top: -1px;">
    <colgroup>
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
    </colgroup>
    <tr class="gray-row">
        <td colspan="4">
            Si la solicitud fue para uso temporal, firmar y sellar en los espacios siguientes:
        </td>
    </tr>
    <tr class="bold center xsmall">
        <td>Firma devolución<br>(persona responsable)</td>
        <td>Sello</td>
        <td>Firma de recepción<br>(Unidad e Activo Fijo)</td>
        <td>Sello UAF</td>
    </tr>
    <tr class="sign-row">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
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
