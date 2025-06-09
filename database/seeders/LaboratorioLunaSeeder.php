<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaboratorioLunaSeeder extends Seeder
{
    public function run()
    {
        $labs = [
            'Trimex',
            'OXO',
            'Vision 20/20',
        ];
        foreach ($labs as $nombre) {
            DB::table('laboratorios_luna')->insert([
                'nombre' => $nombre,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
