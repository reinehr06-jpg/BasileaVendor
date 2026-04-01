<?php

namespace App\Mail;

use App\Models\Cliente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClienteAcessoSuspenso extends Mailable
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
            subject: '⚠️ Seu acesso ao Basiléia Global foi suspenso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cliente_acesso_suspenso',
        );
    }

    public function build()
    {
        // Usa o mesmo from configurado para emails de cliente
        $fromEmail = \App\Models\Setting::get('email_cliente_from', config('mail.from.address', 'noreply@basileia.global'));
        $fromName = config('mail.from.name', 'Basiléia Global');

        return $this
            ->from($fromEmail, $fromName)
            ->subject('⚠️ Seu acesso ao Basiléia Global foi suspenso')
            ->view('emails.cliente_acesso_suspenso');
    }
}
