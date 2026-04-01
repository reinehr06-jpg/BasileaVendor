<?php

namespace App\Jobs;

use App\Models\Venda;
use App\Models\Pagamento;
use App\Mail\ConfirmacaoClienteMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailClienteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Venda $venda;
    public Pagamento $pagamento;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(Venda $venda, Pagamento $pagamento)
    {
        $this->venda = $venda;
        $this->pagamento = $pagamento;
    }

    public function handle(): void
    {
        $cliente = $this->venda->cliente;

        if (!$cliente || !$cliente->email) {
            Log::warning('SendEmailClienteJob: Cliente sem email configurado', [
                'venda_id' => $this->venda->id,
            ]);
            return;
        }

        if ($this->venda->email_cliente_enviado) {
            Log::info('SendEmailClienteJob: Email já enviado anteriormente', [
                'venda_id' => $this->venda->id,
            ]);
            return;
        }

        try {
            Mail::to($cliente->email)
                ->send(new ConfirmacaoClienteMail($this->venda, $this->pagamento));

            $this->venda->email_cliente_enviado = true;
            $this->venda->save();

            Log::info('SendEmailClienteJob: Email enviado com sucesso', [
                'venda_id' => $this->venda->id,
                'cliente_id' => $cliente->id,
                'email' => $cliente->email,
            ]);
        } catch (\Exception $e) {
            Log::error('SendEmailClienteJob: Falha ao enviar email', [
                'venda_id' => $this->venda->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendEmailClienteJob: Job falhou definitivamente', [
            'venda_id' => $this->venda->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}