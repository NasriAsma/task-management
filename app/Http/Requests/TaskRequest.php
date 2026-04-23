<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        // On laisse la logique d'autorisation dans le contrôleur (via les Policies)
        // ou on peut la mettre ici. Pour l'instant, on met true.
        return true;
    }

    public function rules(): array
    {
        // Si c'est la méthode updateStatus
        if ($this->routeIs('*.updateStatus')) {
            return [
                'status' => 'required|in:pending,in_progress,completed'
            ];
        }

        // Si c'est une création (POST)
        if ($this->isMethod('post')) {
            return [
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'deadline'    => 'nullable|date',
                'priority'    => 'nullable|in:low,medium,high',
                'assigned_to' => 'nullable|exists:users,id',
                'status'      => 'required|in:pending,in_progress,completed',
            ];
        }

        // Si c'est une mise à jour (PUT/PATCH)
        return [
            'title'    => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'deadline' => 'sometimes|nullable|date',
            'priority' => 'sometimes|in:low,medium,high',
            'status'   => 'sometimes|in:pending,in_progress,completed',
            'assigned_to' => 'sometimes|nullable|exists:users,id',

        ];
    }
}