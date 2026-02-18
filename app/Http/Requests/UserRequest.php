<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('idUser'); // Récupère l'ID depuis l'URL /users/{idUser}

        if ($this->isMethod('post')) {
            return [
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ];
        }

        return [
            'name'     => 'sometimes|required|string|max:255', // On ignore l'ID actuel pour la règle unique lors de la modification
            'email'    => 'sometimes|required|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'sometimes|required|string|min:8',
        ];
    }
}