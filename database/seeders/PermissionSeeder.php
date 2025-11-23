<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'Level User',     'route' => 'roles.index',        'urut' => 1],
            ['name' => 'Kelola User',    'route' => 'users.index',        'urut' => 2],
            ['name' => 'Kelola Menu',    'route' => 'permissions.index',  'urut' => 3],
            ['name' => 'hasil',          'route' => 'hasil.index',        'urut' => 4],
            ['name' => 'Evaluasi',       'route' => 'evaluasi.index',     'urut' => 5],
            ['name' => 'laporan',        'route' => 'laporan.index',      'urut' => 6],
            ['name' => 'prediksi',       'route' => 'prediksi.index',     'urut' => 7],
            ['name' => 'Data Set',       'route' => 'dataset.index',      'urut' => 8],
            ['name' => 'Kelola Halaman', 'route' => 'halaman.index',      'urut' => 9],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm['name'],
                'route' => $perm['route'],
                'urut' => $perm['urut'],
            ]);
        }
    }
}
