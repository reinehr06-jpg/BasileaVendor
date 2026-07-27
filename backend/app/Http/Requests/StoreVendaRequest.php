<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'vendedor_id' => 'nullable|exists:vendedores,id',
            'plano' => 'required|string|max:255',
            'plano_id' => 'nullable|exists:planos,id',
            'valor' => 'required|numeric|min:0',
            'valor_final' => 'nullable|numeric|min:0',
            'forma_pagamento' => 'required|in:pix,boleto,cartao',
            'tipo_negociacao' => 'required|in:avulso,mensal,anual',
            'modo_cobranca' => 'nullable|in:payment,subscription,installment',
            'desconto' => 'nullable|numeric|min:0|max:100',
            'percentual_desconto' => 'nullable|numeric|min:0|max:100',
            'parcelas' => 'nullable|integer|min:1|max:12',
            'observacao' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'O cliente é obrigatório.',
            'cliente_id.exists' => 'Cliente não encontrado.',
            'plano.required' => 'O plano é obrigatório.',
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'O valor deve ser numérico.',
            'valor.min' => 'O valor deve ser maior que zero.',
            'forma_pagamento.required' => 'A forma de pagamento é obrigatória.',
            'forma_pagamento.in' => 'Forma de pagamento inválida. Use: pix, boleto ou cartao.',
            'tipo_negociacao.required' => 'O tipo de negociação é obrigatório.',
            'tipo_negociacao.in' => 'Tipo de negociação inválido. Use: avulso, mensal ou anual.',
            'desconto.max' => 'O desconto não pode ser maior que 100%.',
            'parcelas.max' => 'O número máximo de parcelas é 12.',
        ];
    }
}
