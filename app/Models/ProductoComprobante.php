<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoComprobante extends Model
{
    protected $table = 'productos_comprobante';

    protected $fillable = [
        'comprobante_id',
        'nombres',
        'dni_ce',
        'telefono',
        'monto_total'
    ];

    public function items()
    {
        return $this->hasMany(ProductoComprobanteItem::class, 'producto_comprobante_id');
    }
}
