@php
    $fontFamily = $config->font_family;
    $fontSize = $config->font_size;
    $headerAlignment = $config->header_alignment;
    $detailsAlignment = $config->details_alignment; // Added this line
    $fontFamilyCss = $fontFamily === 'Courier New' ? "'Courier New', monospace" : ($fontFamily ? "'$fontFamily', sans-serif" : "monospace");
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Comprobante de Producto</title>
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
            padding: 5px 0;
            border-bottom: 1px dashed #000;
        }

        .client-info,
        .items {
            margin-top: 10px;
        }

        .client-info {
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px dashed #ddd;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .total {
            text-align: right;
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

    <div class="details" style="text-align: {{ $detailsAlignment }};">
        <p>{{ $comprobante->tipo === 'b' ? 'BOLETA' : 'FACTURA' }} DE VENTA ELECTRÓNICA</p>
        <p>{{ $comprobante->serie }}-{{ str_pad($comprobante->correlativo, 8, '0', STR_PAD_LEFT) }}</p>
        <p>Fecha de Emisión: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="client-info">
        <h3>DATOS DEL CLIENTE</h3>
        @if($comprobante->productoComprobante)
            <div>
                <p><strong>Nombres:</strong> {{ $comprobante->productoComprobante->nombres }}</p>
                @if($comprobante->productoComprobante->dni_ce)
                    <p><strong>DNI/CE:</strong> {{ $comprobante->productoComprobante->dni_ce }}</p>
                @endif
                <p><strong>Teléfono:</strong> {{ $comprobante->productoComprobante->telefono }}</p>
            </div>
        @else
            <p>Información del cliente no disponible.</p>
        @endif
    </div>

    <div class="items">
        <h3>DETALLES DE LOS PRODUCTOS</h3>
        @if($comprobante->productoComprobante && $comprobante->productoComprobante->items->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comprobante->productoComprobante->items as $item)
                        <tr>
                            <td style="text-align: left;">
                                @php
                                    $tipo = $item->stock->tipo_producto ?? '';
                                    $tipoLabel = '';
                                    switch($tipo) {
                                        case 'l': $tipoLabel = 'Lentes de Sol'; break;
                                        case 'm': $tipoLabel = 'Montura'; break;
                                        case 'c': $tipoLabel = 'Lentes de Contacto'; break;
                                        case 'u': $tipoLabel = 'Lunas'; break;
                                        default: $tipoLabel = $tipo;
                                    }
                                @endphp
                                {{ $tipoLabel }} - {{ $item->stock->descripcion ?? 'N/A' }}
                            </td>
                            <td>
                                @php
                                    $isLuna = isset($item->stock) && $item->stock->tipo_producto === 'u';
                                @endphp
                                {{ $isLuna ? 'N/A' : $item->cantidad }}
                            </td>
                            <td style="text-align: right;">{{ number_format($item->precio * $item->cantidad, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No hay items registrados.</p>
        @endif
    </div>

    @php
        $subtotal = $comprobante->monto_total / 1.18;
        $igv = $comprobante->monto_total - $subtotal;
    @endphp
    <div class="total">
        <p><strong>VALOR DE VENTA: S/ {{ number_format($subtotal, 2) }}</strong></p>
        <p><strong>IGV (18%): S/ {{ number_format($igv, 2) }}</strong></p>
        <p><strong>VALOR TOTAL: S/ {{ number_format($comprobante->monto_total, 2) }}</strong></p>
    </div>

    <div class="footer">
        <p>¡Gracias por su preferencia!</p>


        <p>Representación impresa de la {{ $comprobante->tipo === 'b' ? 'BOLETA' : 'FACTURA' }} de venta electrónica
        </p>
    </div>
</body>

</html>