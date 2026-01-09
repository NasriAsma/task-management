<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrateur du système'],
            ['name' => 'employe', 'description' => 'Employé'],
            ['name' => 'emploi', 'description' => 'Gestionnaire d\'emploi'],
        ];

        foreach ($roles as $data) {
            Role::updateOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );
        }
    }
}
