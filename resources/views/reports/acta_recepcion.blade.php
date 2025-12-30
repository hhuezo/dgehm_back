<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Acta de Recepción de Insumos de Almacén</title>

    <style>
        @page {
            margin: 30px 30px 60px 30px;
            /* espacio inferior para footer */
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.6;
        }

        .header-logos {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-box {
            width: 15%;
            text-align: center;
        }

        .logo-box img {
            max-width: 100%;
            height: auto;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 30px;
            padding: 0 4px;
        }

        .block {
            margin-top: 15px;
            text-align: justify;
        }

        .signatures {
            margin-top: 40px;
            width: 100%;
            font-size: 12px;
        }

        .signatures td {
            padding-right: 40px;
            vertical-align: top;
        }

        /* FOOTER FIJO */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 30px;
            font-size: 11px;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
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



    <div class="center bold">
        ACTA DE RECEPCIÓN DE INSUMOS DE ALMACÉN
    </div>

    {{-- CUERPO --}}
    <div class="block">
        San Salvador Centro, a las
        <span class="underline">{{ $acta_time_part }}</span>
        horas y
        <span class="underline">{{ $acta_minutes_part }}</span>
        minutos del día
        <span class="underline">{{ $acta_date_part }}</span>
        de
        <span class="underline">{{ \Carbon\Carbon::parse($order->acta_date)->translatedFormat('F') }}</span>
        de dos mil
        <span class="underline">{{ $acta_year_part }}</span>;
        en las instalaciones de la Dirección General de Energía, Hidrocarburos y Minas,
        ubicadas en Torre Futura Nivel 16 y 17, entre Calle el Mirador y 87 Avenida Norte,
        Colonia Escalón, Distrito de San Salvador Centro, Departamento de San Salvador;
        reunidos el(la) Sr(Sra.)
        <span class="underline">{{ $order->supplier_representative }}</span>,
        en representación de
        <span class="underline">{{ $order->supplier->name ?? '' }}</span>;
        el(la) Sr.(Sra.)
        <span class="underline">{{ $order->administrative_manager }}</span>,
        Gerente Administrativo(a) de la DGEHM; y el(la) Sr.(Sra.)
        <span class="underline">
            {{ $order->administrativeTechnician->name ?? '' }}
            {{ $order->administrativeTechnician->lastname ?? '' }}
        </span>,
        Técnico(a) Administrativo(a) de la DGEHM; para hacer constar la entrega por parte
        del(la) Primero(a) y la recepción por parte del(la) segundo(a) de los insumos
        adquiridos mediante el proceso de compra No.
        <span class="underline">{{ $order->order_number }}</span>
        y compromiso presupuestario No.
        <span class="underline">{{ $order->budget_commitment_number }}</span>,
        recibidos por medio de la factura No.
        <span class="underline">{{ $order->invoice_number }}</span>
        de fecha
        <span class="underline">{{ optional($order->invoice_date)->format('d/m/Y') }}</span>,
        por un monto total de
        <span class="underline">US$ {{ number_format($order->total_amount, 2) }}</span>
        emitida por el proveedor.
    </div>

    <div class="block">
        Posterior a haber constado físicamente los insumos recibidos, los abajo suscritos
        firman a satisfacción.
    </div>

    {{-- FIRMAS --}}
    <table class="signatures">
        <tr>
            {{-- ENTREGA --}}
            <td style="width:50%;">
                <strong>ENTREGA:</strong><br><br>
                F.__________________________________________<br>
                Nombre: {{ $order->supplier_representative }}<br>
                Por Empresa: {{ $order->supplier->name ?? '' }}<br>
                Sello
            </td>

            {{-- RECIBE + POR ALMACÉN --}}
            <td style="width:50%;">
                <strong>RECIBE:</strong><br><br>
                F.__________________________________________<br>
                Nombre: {{ $order->administrative_manager }}<br>
                Cargo: Gerente Administrativo(a)<br>
                (Administrador de O/C)

                <div style="margin-top:35px;">
                    <strong>POR ALMACÉN:</strong><br><br>
                    F.__________________________________________<br>
                    Nombre: {{ $order->administrativeTechnician->name ?? '' }}
                    {{ $order->administrativeTechnician->lastname ?? '' }}<br>
                    Cargo: Técnico Administrativo (Encargado de Almacén)
                </div>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <table class="footer" style="width:100%; table-layout:fixed;">
        <tr>
            <td style="text-align:left;">
                Original: Contabilidad
            </td>

            <td style="text-align:center;">
                Duplicado: Administrador de Contrato<br>
                u Orden de Compra
            </td>

            <td style="text-align:right;">
                Triplicado: UCP
            </td>
        </tr>
    </table>


</body>

</html>
