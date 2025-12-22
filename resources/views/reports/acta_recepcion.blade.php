<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Acta de Recepción de Insumos de Almacén</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.6;
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
            min-width: 120px;
            padding: 0 4px;
        }

        .block {
            margin-top: 15px;
            text-align: justify;
        }

        .signatures {
            margin-top: 40px;
            width: 100%;
        }

        .signature-box {
            width: 45%;
            display: inline-block;
            vertical-align: top;
            text-align: left;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin: 30px 0 5px 0;
        }

        .footer {
            margin-top: 25px;
            font-size: 11px;
        }
    </style>
</head>

<body>

    <div class="center bold">
        ACTA DE RECEPCIÓN DE INSUMOS DE ALMACÉN<br>
        DAF-F-GA-08
    </div>

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
        <span class="underline" style="min-width:200px;">{{ $order->supplier_representative }}</span>,
        en representación de
        <span class="underline" style="min-width:180px;">{{ $order->supplier->name }}</span>;
        el(la) Sr.(Sra.)
        <span class="underline" style="min-width:200px;">{{ $order->administrative_manager }}</span>,
        Gerente Administrativo(a) de la DGEHM; y el(la) Sr.(Sra.)
        <span class="underline" style="min-width:200px;">{{ $order->administrative_technician }}</span>,
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

    <table class="signatures">
        <tr>
            <td class="signature-box">
                ENTREGA:
                <div class="signature-line"></div>
                Nombre:
                <div class="signature-line"></div>
                Por Empresa:
                <div class="signature-line"></div>
                Sello
            </td>

            <td class="signature-box">
                RECIBE:
                <div class="signature-line"></div>
                Nombre:
                <div class="signature-line"></div>
                Cargo: Gerente Administrativo(a)<br>
                (Administrador de O/C)
            </td>
        </tr>
    </table>

    <div class="block">
        <strong>POR ALMACÉN:</strong>
        <div class="signature-line" style="width:40%;"></div>
        Nombre:
        <div class="signature-line" style="width:40%;"></div>
        Cargo: Técnico Administrativo (Encargado de Almacén)
    </div>

    <div class="footer">
        Original: Contabilidad &nbsp;&nbsp;
        Duplicado: Administrador de Contrato &nbsp;&nbsp;
        Triplicado: UCP u Orden de Compra
    </div>

</body>

</html>
