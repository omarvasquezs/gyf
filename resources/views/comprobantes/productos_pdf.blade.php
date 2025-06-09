<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Comprobante de Producto</title>
    <style>
        @page {
            margin: 1.1rem;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .details {
            text-align: center;
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

<body>
    <div class="header">
        <!-- Logo del comprobante -->

        <h2 style="margin:0;">G & F oftalmólogas. S.A.C.</h2>
        <p>Dirección: Calle almenara 124 interior 201 surquillo</p>
        <p>RUC: 20613814265</p>
        <p>Teléfono: 940 213 168</p>
    </div>

    <div class="details">
        <p>{{ $comprobante->tipo === 'b' ? 'BOLETA' : 'FACTURA' }} DE VENTA ELECTRÓNICA</p>
        <p>{{ $comprobante->serie }}-{{ str_pad($comprobante->correlativo, 8, '0', STR_PAD_LEFT) }}</p>
        <p>Fecha de Emisión: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="client-info">
        <h3>DATOS DEL CLIENTE</h3>
        @if($comprobante->productoComprobante)
            <div>
                <p><strong>Nombres:</strong> {{ $comprobante->productoComprobante->nombres }}</p>
                <p><strong>Teléfono:</strong> {{ $comprobante->productoComprobante->telefono }}</p>
                <p><strong>Correo:</strong> {{ $comprobante->productoComprobante->correo }}</p>
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
                            <td style="text-align: left;">{{ $item->stock->descripcion ?? 'N/A' }}</td>
                            <td>{{ $item->cantidad }}</td>
                            <td style="text-align: right;">{{ number_format($item->precio * $item->cantidad, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No hay items registrados.</p>
        @endif
    </div>

    <div class="total">
        <p><strong>TOTAL: S/ {{ number_format($comprobante->monto_total, 2) }}</strong></p>
    </div>

    <div class="footer">
        <p>¡Gracias por su preferencia!</p>


        <p>Representación impresa de la {{ $comprobante->tipo === 'b' ? 'BOLETA' : 'FACTURA' }} de venta electrónica
        </p>
    </div>
</body>

</html>