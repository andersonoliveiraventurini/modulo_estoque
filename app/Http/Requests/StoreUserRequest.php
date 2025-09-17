<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // já que o acesso está protegido pelo middleware auth
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'cpf' => 'nullable|string|size:11|unique:users,cpf',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Este e-mail já está em uso.',
            'cpf.unique' => 'Este CPF já está em uso.',
            'password.confirmed' => 'As senhas não coincidem.',
        ];
    }
}
