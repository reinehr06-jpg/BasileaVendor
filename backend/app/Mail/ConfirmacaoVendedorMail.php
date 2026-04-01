<?php

namespace App\Mail;

use App\Models\Venda;
use App\Models\Pagamento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacaoVendedorMail extends Mailable
{
    use Queueable, SerializesModels;

    public Venda $venda;
    public Pagamento $pagamento;

    public function __construct(Venda $venda, Pagamento $pagamento)
    {
        $this->venda = $venda;
        $this->pagamento = $pagamento;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Venda Confirmada - Basiléia Vendas',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmacao-vendedor',
            with: [
                'venda' => $this->venda,
                'pagamento' => $this->pagamento,
                'nomeIgreja' => $this->venda->cliente->nome_igreja ?? 'N/A',
                'nomeResponsavel' => $this->venda->cliente->nome ?? 'N/A',
                'valor' => number_format($this->pagamento->valor, 2, ',', '.'),
                'formaPagamento' => $this->formatarFormaPagamento($this->pagamento->forma_pagamento_real),
                'dataPagamento' => $this->pagamento->data_pagamento 
                    ? \Carbon\Carbon::parse($this->pagamento->data_pagamento)->format('d/m/Y') 
                    : now()->format('d/m/Y'),
                'linkVenda' => route('vendedor.vendas.cobranca', $this->venda->id),
            ],
        );
    }

    private function formatarFormaPagamento(?string $forma): string
    {
        return match ($forma) {
            'pix' => 'PIX',
            'boleto' => 'Boleto',
            'cartao' => 'Cartão de Crédito',
            default => ucfirst($forma ?? 'Não informado'),
        };
    }
}