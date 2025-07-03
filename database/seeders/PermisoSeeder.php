<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;
use App\Models\MenuItem;
use Illuminate\Support\Carbon;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $menuItems = MenuItem::all();

        foreach ($menuItems as $item) {
            Permiso::create([
                'id_menu_item' => $item->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
