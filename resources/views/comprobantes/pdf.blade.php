@php
    $fontFamily = $config->font_family;
    $fontSize = $config->font_size;
    $headerAlignment = $config->header_alignment;
    $detailsAlignment = $config->details_alignment; // Added this line
    $fontFamilyCss = $fontFamily === 'Courier New' ? "'Courier New', monospace" : ($fontFamily ? "'$fontFamily', sans-serif" : "monospace");
    $subtotal = $comprobante->monto_total / 1.18;
    $igv = $comprobante->monto_total - $subtotal;
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $comprobante->serie }}-{{ str_pad($comprobante->correlativo, 8, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 0.5rem 0.3rem;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .header {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .details {
            margin-bottom: 10px;
        }

        .item {
            margin-bottom: 5px;
        }

        .total {
            border-top: 1px dashed #000;
            margin-top: 10px;
            padding-top: 5px;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
        }

        .qr {
            margin: 20px;
        }
    </style>
</head>

<body style="font-family: {!! $fontFamilyCss !!}; font-size: {{ $fontSize }}px;">
    <div class="header" style="text-align: {{ $headerAlignment }};">
        <!-- Logo del comprobante -->
        @if($config->show_logo && $config->logo_path)
            <img src="{{ public_path('storage/' . $config->logo_path) }}" alt="Logo" style="max-height: 50px; margin-bottom: 10px;">
        @endif

        <h2 style="margin:0;">{{ $config->company_name }}</h2>
        <p>Dirección: {{ $config->address }}</p>
        @if($config->sub_title)
            <p>{{ $config->sub_title }}</p>
        @endif
        @if($config->address_2)
            <p>{{ $config->address_2 }}</p>
        @endif
        <p>RUC: {{ $config->ruc }}</p>
        <p>Teléfono: {{ $config->phone }}</p>
        @if($config->email)
            <p>Email: {{ $config->email }}</p>
        @endif
        @if($config->website)
            <p>Web: {{ $config->website }}</p>
        @endif
    </div>

    <div class="details" style="text-align: {{ $detailsAlignment }}; padding-bottom: 5px;border-bottom: 1px dashed #000;">
        <p style="margin:5px 0;">{{ $comprobante->tipo === 'b' ? 'BOLETA' : 'FACTURA' }} DE VENTA ELECTRÓNICA</p>
        <p style="margin:5px 0;">
            {{ $comprobante->serie }}-{{ str_pad($comprobante->correlativo, 8, '0', STR_PAD_LEFT) }}
        </p>
    </div>

    <div class="items">
        <h3>DETALLE DE SERVICIO</h3>
        @if($comprobante->citas->isNotEmpty())
            @foreach($comprobante->citas as $cita)
                <div class="item">
                    <p><strong>Fecha de Emision:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
                    <p><strong>Número de Historia:</strong> {{ optional($comprobante->citas->first()->paciente)->num_historia ?? 'N/A' }}</p>
                    <p><strong>Paciente:</strong> {{ optional($comprobante->citas->first()->paciente)->nombres ?? 'N/A' }}
                        {{ optional($comprobante->citas->first()->paciente)->ap_paterno ?? '' }}
                        {{ optional($comprobante->citas->first()->paciente)->ap_materno ?? '' }}</p>
                    @if($comprobante->tipo === 'f' && $comprobante->numero_ruc)
                        <p><strong>RUC:</strong> {{ $comprobante->numero_ruc }}</p>
                        <p><strong>Razón Social:</strong> {{ $comprobante->razon_social }}</p>
                    @endif
                    <p><strong>Tipo de Cita:</strong> {{ optional($cita->tipoCita)->tipo_cita ?? 'N/A' }}</p>
                    <p><strong>Médico:</strong> {{ $cita->medico->nombres ?? 'N/A' }} {{ $cita->medico->ap_paterno ?? '' }}
                        {{ $cita->medico->ap_materno ?? '' }}</p>
                    <p><strong>Fecha de la Cita:</strong> {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y H:i') }}</p>
                    <p><strong>Método de pago:</strong> {{ optional($comprobante->metodoPago)->nombre ?? 'N/A' }}</p>
                    <p><strong>Monto:</strong> S/ {{ number_format($cita->pivot->monto, 2) }}</p>
                </div>
                @if(!$loop->last)
                    <hr style="border-top: 1px dashed #ccc">
                @endif
            @endforeach
        @endif
    </div>

    <div class="total">
        <p><strong>VALOR DE VENTA: S/ {{ number_format($subtotal, 2) }}</strong></p>
        <p><strong>IGV (18%): S/ {{ number_format($igv, 2) }}</strong></p>
        <p><strong>VALOR TOTAL: S/ {{ number_format($comprobante->monto_total, 2) }}</strong></p>
    </div>

    <div class="footer">
        <p>¡Gracias por su preferencia!</p>

        <p>Representación impresa de la {{ $comprobante->tipo === 'b' ? 'BOLETA' : 'FACTURA' }} de venta electrónica</p>
    </div>
</body>

</html>
