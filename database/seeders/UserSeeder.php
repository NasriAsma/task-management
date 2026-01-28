<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Récupérer les rôles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $employeRole = Role::where('name', 'employe')->first();

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'nasriasma91@gmail.com'],
            ['name' => 'Admin', 'password' => Hash::make('asma12'), 'is_2fa_enabled' => false]
        );
        if ($adminRole && !$admin->roles->contains($adminRole)) {
            $admin->roles()->attach($adminRole->id);
        }

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            ['name' => 'Manager', 'password' => Hash::make('manager12'), 'is_2fa_enabled' => false]
        );
        if ($managerRole && !$manager->roles->contains($managerRole)) {
            $manager->roles()->attach($managerRole->id);
        }

        // Employés
        $sou = User::firstOrCreate(
            ['email' => 'sou@exemple.com'],
            ['name' => 'sou', 'password' => Hash::make('sou12'), 'is_2fa_enabled' => false]
        );
        if ($employeRole && !$sou->roles->contains($employeRole)) {
            $sou->roles()->attach($employeRole->id);
        }

        $sli = User::firstOrCreate(
            ['email' => 'sli@exemple.com'],
            ['name' => 'sli', 'password' => Hash::make('sli12'), 'is_2fa_enabled' => false]
        );
        if ($employeRole && !$sli->roles->contains($employeRole)) {
            $sli->roles()->attach($employeRole->id);
        }
    }
}