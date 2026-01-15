<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['name' => 'sou', 'email' => 'sou@exemple.com', 'password' => Hash::make('sou12'), 'is_2fa_enabled' => false],
            ['name' => 'sli', 'email' => 'sli@exemple.com', 'password' => Hash::make('sli12'), 'is_2fa_enabled' => false],
            ['name' => 'Admin','email' => 'admin@example.com','password' => Hash::make('password'),'is_2fa_enabled' => false ]
        
            ];
        
       

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->roles->contains($adminRole)) {
            $admin->roles()->attach($adminRole);
        }
    }
}
