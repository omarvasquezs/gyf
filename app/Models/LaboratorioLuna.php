<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaboratorioLuna extends Model
{
    use HasFactory;
    protected $table = 'laboratorios_lunas';
    protected $fillable = ['nombre'];
}
