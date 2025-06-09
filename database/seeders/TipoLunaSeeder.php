<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoLunaSeeder extends Seeder
{
    public function run()
    {
        $tipos = [
            'Simple',
            'Resina AR',
            'Resina Blue',
            'Resina Blue Verde',
            'Policarbonato UV',
            'Policarbonato AR',
            'Policarbonato Blue',
            'Fotocromatico AR',
            'Fotocromatico Blue',
            'Transitions',
        ];
        foreach ($tipos as $nombre) {
            DB::table('tipo_lunas')->insert([
                'nombre' => $nombre,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
