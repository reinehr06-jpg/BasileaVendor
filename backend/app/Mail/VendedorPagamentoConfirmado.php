<?php

namespace App\Mail;

use App\Models\Venda;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendedorPagamentoConfirmado extends Mailable
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
            subject: '✅ Pagamento Confirmado — ' . ($this->venda->cliente->nome_igreja ?? $this->venda->cliente->nome ?? 'Cliente'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendedor_confirmacao',
        );
    }

    public function build()
    {
        return $this
            ->subject('✅ Pagamento Confirmado — ' . ($this->venda->cliente->nome_igreja ?? $this->venda->cliente->nome ?? 'Cliente'))
            ->view('emails.vendedor_confirmacao');
    }
}
