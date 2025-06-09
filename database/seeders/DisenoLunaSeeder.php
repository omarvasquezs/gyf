<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisenoLunaSeeder extends Seeder
{
    public function run()
    {
        $disenos = [
            'Monofocal',
            'Bifocal Flat top',
            'Bifocal Invisible',
            'Multifocal',
        ];
        foreach ($disenos as $nombre) {
            DB::table('diseno_lunas')->insert([
                'nombre' => $nombre,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
