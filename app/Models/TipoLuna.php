<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoLuna extends Model
{
    use HasFactory;
    protected $table = 'tipo_lunas';
    protected $fillable = ['nombre'];
}
