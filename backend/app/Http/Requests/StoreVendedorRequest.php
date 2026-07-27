<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'senha' => 'required|string|min:6',
            'telefone' => 'nullable|string|max:20',
            'equipe_id' => 'nullable|exists:equipes,id',
            'gestor_id' => 'nullable|exists:users,id',
            'is_gestor' => 'nullable|boolean',
            'percentual_comissao' => 'nullable|numeric|min:0|max:100',
            'comissao_inicial' => 'nullable|numeric|min:0|max:100',
            'comissao_recorrencia' => 'nullable|numeric|min:0|max:100',
            'comissao_gestor_primeira' => 'nullable|numeric|min:0|max:100',
            'comissao_gestor_recorrencia' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'senha.required' => 'A senha é obrigatória.',
            'senha.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'percentual_comissao.max' => 'O percentual de comissão não pode ser maior que 100%.',
            'comissao_inicial.max' => 'A comissão inicial não pode ser maior que 100%.',
            'comissao_recorrencia.max' => 'A comissão de recorrência não pode ser maior que 100%.',
        ];
    }
}
