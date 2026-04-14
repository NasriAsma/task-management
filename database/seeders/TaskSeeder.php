<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TaskSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('email', 'nasriasma91@gmail.com')->first();
        $manager = User::where('email', 'manager@example.com')->first();
        $sou = User::where('email', 'sou@exemple.com')->first();
        $sli = User::where('email', 'sli@exemple.com')->first();

        if (!$admin || !$manager || !$sou || !$sli) {
            return;
        }

        $tasks = [
            [
                'title' => 'Configurer les rôles et permissions',
                'description' => 'Vérifier les permissions admin et manager après le seed.',
                'status' => 'completed',
                'deadline' => Carbon::now()->addDays(2)->toDateString(),
                'priority' => 'high',
                'assigned_to' => $manager->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Préparer le tableau de bord équipe',
                'description' => 'Ajouter les données nécessaires pour la vue manager.',
                'status' => 'in_progress',
                'deadline' => Carbon::now()->addDays(4)->toDateString(),
                'priority' => 'medium',
                'assigned_to' => $sou->id,
                'created_by' => $manager->id
            ],
            [
                'title' => 'Corriger les validations frontend',
                'description' => 'Aligner les champs du formulaire avec l’API.',
                'status' => 'pending',
                'deadline' => Carbon::now()->addDays(6)->toDateString(),
                'priority' => 'medium',
                'assigned_to' => $sli->id,
                'created_by' => $manager->id,
            ],
            [
                'title' => 'Relire les routes API',
                'description' => 'Vérifier les routes /users, /tasks et /permissions.',
                'status' => 'pending',
                'deadline' => Carbon::now()->addDays(3)->toDateString(),
                'priority' => 'low',
                'assigned_to' => null,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Mettre à jour la documentation',
                'description' => 'Documenter les réponses JSON des endpoints.',
                'status' => 'in_progress',
                'deadline' => Carbon::now()->addDays(5)->toDateString(),
                'priority' => 'low',
                'assigned_to' => $sou->id,
                'created_by' => $admin->id,
            ],
        ];

        foreach ($tasks as $data) {
            Task::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}