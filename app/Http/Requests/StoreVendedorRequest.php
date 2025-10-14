<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendedorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
            'user_id' => 'required|exists:users,id|unique:vendedores,user_id',
            'externo' => 'required|boolean',
            'desconto' => 'required|numeric|min:0|max:30',
       ];
    }
    
    public function messages()
    {
        return [
            'user_id.unique' => 'Este usuário já está cadastrado como vendedor.',
        ];
    }
}
