<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Venda;

class VendaConfirmadaVendedorMail extends Mailable
{
    use Queueable, SerializesModels;

    public Venda $venda;

    public function __construct(Venda $venda)
    {
        $this->venda = $venda;
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
            view: 'emails.venda-confirmada-vendedor',
            with: [
                'venda' => $this->venda,
                'linkVenda' => route('vendedor.vendas.cobranca', $this->venda->id),
            ],
        );
    }
}
