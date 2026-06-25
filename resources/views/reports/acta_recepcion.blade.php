<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Recepción de Insumos de Almacén</title>
    <style>
        @page {
            margin: 96pt 72pt 90pt 72pt;
        }

        body {
            font-family: museo-sans-300, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #150D2D;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .pdf-header {
            position: fixed;
            top: -78pt;
            left: 0;
            right: 0;
            height: 78pt;
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
            font-family: museo-sans-500, sans-serif;
            text-align: center;
            font-size: 12pt;
            line-height: 1.15;
            color: #150D2D;
            margin: 4pt 0 0;
            padding: 0 8pt;
        }

        .body-text {
            margin-top: 6pt;
            text-align: justify;
            text-justify: inter-word;
        }

        .body-text p {
            margin: 0 0 10pt;
            font-family: museo-sans-300, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
        }

        .underline {
            border-bottom: 0.5pt solid #150D2D;
            padding: 0 1pt 0.5pt;
        }

        .signatures {
            margin-top: 22pt;
            font-family: museo-sans-300, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
        }

        .signatures .sign-head {
            font-family: museo-sans-500, sans-serif;
            margin-bottom: 8pt;
        }

        .signatures td {
            vertical-align: top;
            width: 50%;
            padding-right: 14pt;
        }

        .signatures .sign-right {
            padding-right: 0;
            padding-left: 14pt;
        }

        .warehouse-sign {
            margin-top: 24pt;
        }

        .pdf-footer {
            position: fixed;
            bottom: -72pt;
            left: 0;
            right: 0;
            height: 72pt;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #150D2D;
        }

        .pdf-footer td {
            vertical-align: top;
        }

        .pdf-footer .footer-spacer td {
            height: 14pt;
            padding: 0;
            line-height: 0;
            font-size: 0;
        }

        .pdf-footer .code-row td {
            padding-bottom: 6pt;
        }

        .pdf-footer .dist-row td {
            font-size: 9pt;
            line-height: 1.15;
            padding-top: 0;
        }

        .pdf-footer .dist-center {
            text-align: center;
            line-height: 1.2;
        }

        .pdf-footer .dist-sub-row td {
            font-size: 9pt;
            line-height: 1.15;
            padding-top: 0;
        }
    </style>
</head>
<body>
@php
    $escudoPath = public_path('escudo.png');
    $logoPath = public_path('logo_azul.png');
    $adminName = trim(($order->purchaseOrderAdministrator->name ?? '') . ' ' . ($order->purchaseOrderAdministrator->lastname ?? ''));
    $techName = trim(($order->administrativeTechnician->name ?? '') . ' ' . ($order->administrativeTechnician->lastname ?? ''));
@endphp

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
    <div class="header-title">ACTA DE RECEPCIÓN DE INSUMOS DE ALMACÉN</div>
</div>

<div class="body-text">
    <p>
        San Salvador Centro, a las <span class="underline">{{ $acta_time_part }}</span> horas y
        <span class="underline">{{ $acta_minutes_part }}</span> minutos del día
        <span class="underline">{{ $acta_date_part }}</span> de
        <span class="underline">{{ $acta_month_part }}</span> de dos mil
        <span class="underline">{{ $acta_year_part }}</span>; en las instalaciones de la Dirección General
        de Energía, Hidrocarburos y Minas, ubicadas en Torre Futura Nivel 16 y 17, entre Calle el
        Mirador y 87 Avenida Norte, Colonia Escalón, Distrito de San Salvador Centro, Departamento
        de San Salvador; reunidos el(la) Sr(Sra.)
        <span class="underline">{{ $order->supplier_representative }}</span>, en
        representación de <span class="underline">{{ $order->supplier->name ?? '' }}</span>; el(la)
        Sr.(Sra.) <span class="underline">{{ $adminName }}</span>, Gerente Administrativo(a)
        de la DGEHM; y el(la) Sr.(Sra.) <span class="underline">{{ $techName }}</span>,
        Técnico(a) Administrativo(a) de la DGEHM; para hacer constar la entrega por parte del(la)
        Primero(a) y la recepción por parte del(la) segundo(a) de los insumos adquiridos mediante el
        proceso de compra No. <span class="underline">{{ $order->order_number }}</span> de fecha
        <span class="underline">{{ $oc_date_part }}</span> de
        <span class="underline">{{ $oc_month_part }}</span> de
        <span class="underline">{{ $oc_year_part }}</span>, y compromiso presupuestario No.
        <span class="underline">{{ $order->budget_commitment_number }}</span>, recibidos por medio de la factura No.
        <span class="underline">{{ $order->invoice_number }}</span> de fecha:
        <span class="underline">{{ $invoice_date_part }}</span> de
        <span class="underline">{{ $invoice_month_part }}</span> de
        <span class="underline">{{ $invoice_year_part }}</span>, por un monto total de
        US$ <span class="underline">{{ number_format($order->total_amount, 2) }}</span> emitida por el proveedor.
    </p>

    <p>
        Posterior a haber constado físicamente los insumos recibidos, los abajo suscritos
        firman a satisfacción.
    </p>
</div>

<table class="signatures">
    <tr>
        <td>
            <div class="sign-head">ENTREGA:</div>
            F.________________________________________<br><br>
            Nombre: <span class="underline">{{ $order->supplier_representative }}</span><br>
            Por Empresa: <span class="underline">{{ $order->supplier->name ?? '' }}</span><br>
            Sello
        </td>
        <td class="sign-right">
            <div class="sign-head">RECIBE:</div>
            F.________________________________________<br><br>
            Nombre: <span class="underline">{{ $adminName }}</span><br>
            Cargo: Gerente Administrativo(a)<br>
            (Administrador de O/C)

            <div class="warehouse-sign">
                <div class="sign-head">POR ALMACÉN:</div>
                F.________________________________<br><br>
                Nombre: <span class="underline">{{ $techName }}</span><br>
                Cargo: Técnico Administrativo (Encargado de Almacén)
            </div>
        </td>
    </tr>
</table>

<table class="pdf-footer">
    <tr class="footer-spacer">
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr class="code-row">
        <td style="width:50%; text-align:left;">
            DAF-F-GA-08<br>
            13/01/2025
        </td>
        <td style="width:50%; text-align:right;">&nbsp;</td>
    </tr>
    <tr class="dist-row">
        <td style="width:33%; text-align:left;">Original: Contabilidad</td>
        <td style="width:34%; text-align:center;">Duplicado: Administrador de Contrato</td>
        <td style="width:33%; text-align:right;">Triplicado: UCP</td>
    </tr>
    <tr class="dist-sub-row">
        <td style="width:33%;">&nbsp;</td>
        <td style="width:34%; text-align:center;">u Orden de Compra</td>
        <td style="width:33%;">&nbsp;</td>
    </tr>
</table>

<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_script('
            $font = $fontMetrics->get_font("Arial", "normal");
            $pdf->text(430, 768, "Página $PAGE_NUM de $PAGE_COUNT", $font, 9);
        ');
    }
</script>
</body>
</html>
