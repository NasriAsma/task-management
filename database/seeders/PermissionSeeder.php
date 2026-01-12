<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            ['name' => 'create_user', 'description' => 'Créer un utilisateur'],
            ['name' => 'edit_user', 'description' => 'Modifier un utilisateur'],
            ['name' => 'delete_user', 'description' => 'Supprimer un utilisateur'],
            ['name' => 'view_user', 'description' => 'Voir un utilisateur'],
            ['name' => 'all', 'description' => 'Accès total'],
            ['name' => 'create_task', 'description' => 'Créer une tâche'],
            ['name' => 'edit_task', 'description' => 'Modifier une tâche'],
            ['name' => 'delete_task', 'description' => 'Supprimer une tâche'],
            ['name' => 'view_task', 'description' => 'Voir une tâche']
            
            
        ];

        foreach ($permissions as $data) {
            Permission::updateOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );
        }
        
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::all());
        }
    }
}
