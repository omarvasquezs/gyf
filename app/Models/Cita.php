<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'num_historia',
        'id_medico',
        'fecha',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'num_historia', 'num_historia');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'id_medico', 'id');
    }
}
