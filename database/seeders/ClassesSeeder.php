<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Busca el específico por código en fa_specifics e inserta name y code de la clase.
     */
    public function run(): void
    {
        $specificIds = [
            '0101' => DB::table('fa_specifics')->where('code', '0101')->value('id'),
            '0102' => DB::table('fa_specifics')->where('code', '0102')->value('id'),
            '0103' => DB::table('fa_specifics')->where('code', '0103')->value('id'),
            '0104' => DB::table('fa_specifics')->where('code', '0104')->value('id'),
            '0105' => DB::table('fa_specifics')->where('code', '0105')->value('id'),
            '0199' => DB::table('fa_specifics')->where('code', '0199')->value('id'),
            '0403' => DB::table('fa_specifics')->where('code', '0403')->value('id'),
        ];

        $classes = [
            // Mobiliario (0101): cod 01–05
            ['fa_specific_id' => $specificIds['0101'], 'code' => '01', 'name' => 'Archivos'],
            ['fa_specific_id' => $specificIds['0101'], 'code' => '02', 'name' => 'Escritorios'],
            ['fa_specific_id' => $specificIds['0101'], 'code' => '03', 'name' => 'Mesas'],
            ['fa_specific_id' => $specificIds['0101'], 'code' => '04', 'name' => 'Silla'],
            ['fa_specific_id' => $specificIds['0101'], 'code' => '05', 'name' => 'Varios'],
            // Maquinaria y equipo (0102): 01–05
            ['fa_specific_id' => $specificIds['0102'], 'code' => '01', 'name' => 'Teléfono'],
            ['fa_specific_id' => $specificIds['0102'], 'code' => '02', 'name' => 'Contómetro'],
            ['fa_specific_id' => $specificIds['0102'], 'code' => '03', 'name' => 'Proyector (03)'],
            ['fa_specific_id' => $specificIds['0102'], 'code' => '04', 'name' => 'Televisor (04)'],
            ['fa_specific_id' => $specificIds['0102'], 'code' => '05', 'name' => 'Varios (05)'],
            // Equipo médico y de laboratorio (0103): 01–10
            ['fa_specific_id' => $specificIds['0103'], 'code' => '01', 'name' => 'Botiquín'],
            ['fa_specific_id' => $specificIds['0103'], 'code' => '02', 'name' => 'Canapé'],
            ['fa_specific_id' => $specificIds['0103'], 'code' => '03', 'name' => 'Camilla'],
            ['fa_specific_id' => $specificIds['0103'], 'code' => '04', 'name' => 'Equipo de oxígeno'],
            ['fa_specific_id' => $specificIds['0103'], 'code' => '05', 'name' => 'Temómetro'],
            ['fa_specific_id' => $specificIds['0103'], 'code' => '06', 'name' => 'Carro de curación'],
            ['fa_specific_id' => $specificIds['0103'], 'code' => '07', 'name' => 'Medidor de octanos y cetanos'],
            ['fa_specific_id' => $specificIds['0103'], 'code' => '08', 'name' => 'Embudo'],
            ['fa_specific_id' => $specificIds['0103'], 'code' => '09', 'name' => 'Medidor volumétrico'],
            ['fa_specific_id' => $specificIds['0103'], 'code' => '10', 'name' => 'Varios'],
            // Equipo informático (0104): 01–09
            ['fa_specific_id' => $specificIds['0104'], 'code' => '01', 'name' => 'Laptop'],
            ['fa_specific_id' => $specificIds['0104'], 'code' => '02', 'name' => 'CPU'],
            ['fa_specific_id' => $specificIds['0104'], 'code' => '03', 'name' => 'Monitor'],
            ['fa_specific_id' => $specificIds['0104'], 'code' => '04', 'name' => 'Teclado'],
            ['fa_specific_id' => $specificIds['0104'], 'code' => '05', 'name' => 'Ups'],
            ['fa_specific_id' => $specificIds['0104'], 'code' => '06', 'name' => 'Parlantes'],
            ['fa_specific_id' => $specificIds['0104'], 'code' => '07', 'name' => 'Servidores'],
            ['fa_specific_id' => $specificIds['0104'], 'code' => '08', 'name' => 'Swich'],
            ['fa_specific_id' => $specificIds['0104'], 'code' => '09', 'name' => 'Varios'],
            // Equipo de transporte (0105): 01
            ['fa_specific_id' => $specificIds['0105'], 'code' => '01', 'name' => 'Vehículo'],
            // Diversos (0199): 01–04
            ['fa_specific_id' => $specificIds['0199'], 'code' => '01', 'name' => 'Pizarras'],
            ['fa_specific_id' => $specificIds['0199'], 'code' => '02', 'name' => 'Astas'],
            ['fa_specific_id' => $specificIds['0199'], 'code' => '03', 'name' => 'Extintores'],
            ['fa_specific_id' => $specificIds['0199'], 'code' => '04', 'name' => 'Varios'],
            // Derechos de propiedad intelectual (0403): 01
            ['fa_specific_id' => $specificIds['0403'], 'code' => '01', 'name' => 'Licencias'],
        ];

        $now = now();
        $rows = array_map(fn ($c) => [
            'fa_specific_id' => $c['fa_specific_id'],
            'code' => $c['code'],
            'name' => $c['name'],
            'useful_life' => 5,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $classes);

        DB::table('fa_classes')->insert($rows);
    }
}
