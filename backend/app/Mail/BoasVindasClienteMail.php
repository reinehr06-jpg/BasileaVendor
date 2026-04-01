<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Venda;
use App\Models\Cliente;

class BoasVindasClienteMail extends Mailable
{
    use Queueable, SerializesModels;

    public Venda $venda;
    public Cliente $cliente;
    public string $senhaGerada;

    public function __construct(Venda $venda, Cliente $cliente, string $senhaGerada)
    {
        $this->venda = $venda;
        $this->cliente = $cliente;
        $this->senhaGerada = $senhaGerada;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Bem-vindo à Basiléia Church! Seus dados de acesso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.boas-vindas-cliente',
            with: [
                'venda' => $this->venda,
                'cliente' => $this->cliente,
                'senha' => $this->senhaGerada,
                'linkLogin' => url('/login'),
                'linkVideos' => 'https://basileiachurch.com/videos',
                'linkSuporte' => 'https://wa.me/5511999999999',
            ],
        );
    }
}
