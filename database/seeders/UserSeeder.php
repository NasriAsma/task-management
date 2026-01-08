<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            ['name' => 'sou', 'email' => 'sou@exemple.com', 'password' => Hash::make('sou12'), 'is_2fa_enabled' => false],
            ['name' => 'sli', 'email' => 'sli@exemple.com', 'password' => Hash::make('sli12'), 'is_2fa_enabled' => false]
        ];
        
        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
