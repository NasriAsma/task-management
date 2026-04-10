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
            // User Management Permissions
            ['name' => 'create_user', 'description' => 'Créer un utilisateur'],
            ['name' => 'update_user', 'description' => 'Modifier un utilisateur'],
            ['name' => 'delete_user', 'description' => 'Supprimer un utilisateur'],
            ['name' => 'view_user', 'description' => 'Voir les utilisateurs'],
            ['name' => 'view_user_par_id', 'description' => 'Voir un utilisateur par ID'],
            ['name' => 'active_compte', 'description' => 'Activer un compte utilisateur'],
            ['name' => 'desactive_compte', 'description' => 'Désactiver un compte utilisateur'],
            ['name' => 'recherche_user', 'description' => 'Rechercher un utilisateur'],
            // Audit Permissions
            ['name' => 'view_audits', 'description' => 'Voir les audits'],

            // Permission Management Permissions
            ['name' => 'view_all_permission', 'description' => 'Voir toutes les permissions'],
            ['name' => 'create_permission', 'description' => 'Créer une permission'],
            ['name' => 'delete_permission', 'description' => 'Supprimer une permission'],
            ['name' => 'update_permission', 'description' => 'Modifier une permission'],

            // Role Management Permissions
            ['name' => 'assign_role', 'description' => 'Assigner un rôle'],
            ['name' => 'delete_role', 'description' => 'Supprimer un rôle'],

            // Statistics Permissions
            ['name' => 'view_statistique_user', 'description' => 'Voir les statistiques des utilisateurs'],
            ['name' => 'view_statistique_task', 'description' => 'Voir les statistiques des tâches'],
            ['name' => 'view_all_Number_of_user', 'description' => 'Voir le nombre total d\'utilisateurs'],
            ['name' => 'view_all_Number_of_task', 'description' => 'Voir le nombre total de tâches'],
            ['name' => 'view_Number_of_task_for_user', 'description' => 'Voir le nombre de tâches pour un utilisateur'],
            ['name' => 'view_Number_of_team_tasks_for_user', 'description' => 'Voir le nombre de tâches d\'équipe pour un utilisateur'],

            // Task Permissions
            ['name' => 'create_task', 'description' => 'Créer une tâche'],
            ['name' => 'update_task', 'description' => 'Modifier une tâche'],
            ['name' => 'delete_task', 'description' => 'Supprimer une tâche'],
            ['name' => 'view_task', 'description' => 'Voir les tâches'],
            ['name' => 'view_task_details', 'description' => 'Voir les détails d\'une tâche'],
            ['name' => 'view_own_tasks', 'description' => 'Voir ses propres tâches'],
            ['name' => 'assign_task', 'description' => 'Assigner une tâche'],
            ['name' => 'getUserPermissions', 'description' => 'Obtenir les permissions d\'un utilisateur'],
            // Team Task Permissions
            ['name' => 'create_team_task', 'description' => 'Créer une tâche d\'équipe'],
            ['name' => 'update_team_task', 'description' => 'Modifier une tâche d\'équipe'],
            ['name' => 'delete_team_task', 'description' => 'Supprimer une tâche d\'équipe'],
            ['name' => 'view_team_tasks', 'description' => 'Voir les tâches de l\'équipe'],
            ['name' => 'view_permission_by_id', 'description' => 'Voir une permission par ID'],
            // Employee Task Permissions
            ['name' => 'view_task_for_user', 'description' => 'Voir une tâche pour l\'utilisateur'],
            ['name' => 'update_task_status', 'description' => 'Mettre à jour le statut d\'une tâche'],
        ];

        foreach ($permissions as $data) {
            Permission::updateOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );
        }

        // Assign permissions to Admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::all());
        }

        // Assign permissions to Manager role
        $manager = Role::where('name', 'manager')->first();
        if ($manager) {
            $managerPermissions = Permission::whereIn('name', [
                'view_user',
                'create_team_task',
                'update_team_task',
                'delete_team_task',
                'view_team_tasks',
                'view_Number_of_team_tasks_for_user',
            ])->get();

            $manager->permissions()->sync($managerPermissions);
        }

        // Assign permissions to Employee role
        $employe = Role::where('name', 'employe')->first();
        if ($employe) {
            $employePermissions = Permission::whereIn('name', [
                'view_task_for_user',
                'update_task_status',
            ])->get();

            $employe->permissions()->sync($employePermissions);
        }
    }
}

