<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'admin')->first();

        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin123'), // Ganti jika perlu
                'role_id' => $role->id,
            ]
        );

        // Beri semua permission ke admin
        $user->role->permissions()->sync(\App\Models\Permission::pluck('id')->toArray());
    }
}
