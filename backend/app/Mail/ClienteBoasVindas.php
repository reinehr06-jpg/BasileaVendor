<?php

namespace App\Mail;

use App\Models\Cliente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClienteBoasVindas extends Mailable
{
    use Queueable, SerializesModels;

    public Cliente $cliente;

    public function __construct(Cliente $cliente)
    {
        $this->cliente = $cliente;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Bem-vindo(a) ao Basiléia Global, ' . ($this->cliente->nome_igreja ?? $this->cliente->nome ?? '') . '!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cliente_boas_vindas',
        );
    }

    public function build()
    {
        return $this
            ->subject('🎉 Bem-vindo(a) ao Basiléia Global, ' . ($this->cliente->nome_igreja ?? $this->cliente->nome ?? '') . '!')
            ->view('emails.cliente_boas_vindas');
    }
}
