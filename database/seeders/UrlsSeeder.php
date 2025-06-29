<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UrlsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('urls')->insert([
            [
                'ruta' => '/dashboard',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ruta' => '/admin/usuarios',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ruta' => '/admin/roles',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
