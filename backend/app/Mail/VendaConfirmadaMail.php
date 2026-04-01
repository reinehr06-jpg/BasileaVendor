<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Venda;
use App\Models\Pagamento;

class VendaConfirmadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Venda $venda;
    public float $comissao;
    public string $linkVenda;
    public ?Pagamento $pagamento;

    public function __construct(Venda $venda, float $comissao, string $linkVenda, ?Pagamento $pagamento = null)
    {
        $this->venda = $venda;
        $this->comissao = $comissao;
        $this->linkVenda = $linkVenda;
        $this->pagamento = $pagamento;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pagamento confirmado da venda — Basiléia Vendas',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.venda-confirmada',
        );
    }
}
