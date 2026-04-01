<?php

namespace App\Mail;

use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacaoClienteMail extends Mailable
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
            subject: '🎉 Compra Confirmada! Seja bem-vindo à Basilélia Church',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmacao-cliente',
            with: [
                'venda' => $this->venda,
                'pagamento' => $this->pagamento,
                'plano' => $this->venda->plano ?? 'Basiléia',
                'valorPago' => number_format($this->pagamento->valor, 2, ',', '.'),
                'linkLogin' => url('/login'),
                'linkVideos' => Setting::get('link_videoaulas', 'https://basileiachurch.com/videos'),
                'linkTermos' => Setting::get('link_termos_pdf', null),
                'notaFiscalUrl' => $this->pagamento->nota_fiscal_url,
            ],
        );
    }
}