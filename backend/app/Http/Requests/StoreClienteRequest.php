<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'nullable|string|max:255',
            'nome_igreja' => 'required|string|max:255',
            'nome_pastor' => 'nullable|string|max:255',
            'localidade' => 'nullable|string|max:255',
            'moeda' => 'nullable|in:BRL,USD,EUR',
            'quantidade_membros' => 'nullable|integer|min:0',
            'documento' => 'required|string|min:11|max:14',
            'whatsapp' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'cep' => 'nullable|string|max:10',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
        ];
    }

    public function messages(): array
    {
        return [
            'nome_igreja.required' => 'O nome da igreja é obrigatório.',
            'documento.required' => 'O CPF/CNPJ é obrigatório.',
            'documento.min' => 'O CPF/CNPJ deve ter pelo menos 11 dígitos.',
            'documento.max' => 'O CPF/CNPJ deve ter no máximo 14 dígitos.',
            'email.email' => 'O e-mail deve ser válido.',
            'moeda.in' => 'Moeda inválida. Use: BRL, USD ou EUR.',
        ];
    }
}
