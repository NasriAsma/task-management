<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
  
     {  $permissions = [
          
            ['name' => 'create_user', 'description' => 'Créer un utilisateur'],
            ['name' => 'edit_user', 'description' => 'Modifier un utilisateur'],
            ['name' => 'delete_user', 'description' => 'Supprimer un utilisateur'],
            ['name' => 'view_user', 'description' => 'Voir un utilisateur'],
            ['name' => 'view_user_for_user', 'description' => 'Voir les utilisateurs'],

        
            ['name' => 'create_task', 'description' => 'Créer une tâche'],
            ['name' => 'edit_task', 'description' => 'Modifier une tâche'],
            ['name' => 'delete_task', 'description' => 'Supprimer une tâche'],
            ['name' => 'view_task', 'description' => 'Voir une tâche'],
            ['name' => 'assign_task', 'description' => 'Assigner une tâche'],
            ['name' => 'view_team_tasks', 'description' => 'Gérer les tâches de l\'équipe'],
            ['name' => 'delete_team_tasks', 'description' => 'Gérer les tâches de l\'équipe'],
            ['name' => 'update_team_tasks', 'description' => 'Gérer les tâches de l\'équipe'],
            ['name' => 'create_team_tasks', 'description' => 'Gérer les tâches de l\'équipe'],
            ['name' => 'view_own_tasks', 'description' => 'Voir ses propres tâches'],
            ['name' => 'update_task_status', 'description' => 'Mettre à jour le statut d\'une tâche'],
            ['name' =>'active_compte', 'description' => 'Activer un compte utilisateur'],
            ['name' =>'desactive_compte', 'description' => 'Désactiver un compte utilisateur'],

         
      
            ['name' => 'all', 'description' => 'Accès total'],
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

        $manager = Role::where('name', 'manager')->first();
        if ($manager) {
            $managerPermissions = Permission::whereIn('name', [
                'view_user',
                'view_task',
                'create_task',
                'edit_task',
                'delete_task',
                'assign_task',
                'manage_team_tasks',
            ])->get();
            
            $manager->permissions()->sync($managerPermissions);
        }

        $employe = Role::where('name', 'employe')->first();
        if ($employe) {
            $employePermissions = Permission::whereIn('name', [
                'view_task_for_user',
                'update_task_status'
            ])->get();
            
            $employe->permissions()->sync($employePermissions);
        }
    }
}

