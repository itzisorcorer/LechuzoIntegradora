<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramaEducativoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('programas_educativos')->insert([
            ['nombre' => 'Gestión del Capital Humano', 'rango_inicio' => 0, 'rango_fin' => 9999],
            ['nombre' => 'Biotecnología', 'rango_inicio' => 10000, 'rango_fin' => 19999],
            ['nombre' => 'Mercadotecnia', 'rango_inicio' => 20000, 'rango_fin' => 29999],
            ['nombre' => 'Automatización', 'rango_inicio' => 30000, 'rango_fin' => 34999],
            ['nombre' => 'Energía Turbo-Solar', 'rango_inicio' => 35000, 'rango_fin' => 39999],
            ['nombre' => 'Mantenimiento Industrial', 'rango_inicio' => 40000, 'rango_fin' => 49999],
            ['nombre' => 'Procesos Productivos', 'rango_inicio' => 50000, 'rango_fin' => 59999],
            ['nombre' => 'Sistemas de Gestión de Calidad', 'rango_inicio' => 55000, 'rango_fin' => 59999],
            ['nombre' => 'Desarrollo de Software Multiplataforma', 'rango_inicio' => 60000, 'rango_fin' => 64999],
            ['nombre' => 'Infraestructura de Redes Digitales', 'rango_inicio' => 65000, 'rango_fin' => 69999],
            ['nombre' => 'Nanotecnología', 'rango_inicio' => 70000, 'rango_fin' => 79999],
            ['nombre' => 'Emprendimiento, Formulación y Evaluación de Proyectos', 'rango_inicio' => 80000, 'rango_fin' => 89999],
        ]);
    }
}
