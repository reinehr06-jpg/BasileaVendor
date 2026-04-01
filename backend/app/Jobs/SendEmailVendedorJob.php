<?php

namespace App\Jobs;

use App\Models\Venda;
use App\Models\Pagamento;
use App\Mail\ConfirmacaoVendedorMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailVendedorJob implements ShouldQueue
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
        $vendedor = $this->venda->vendedor;

        if (!$vendedor || !$vendedor->user || !$vendedor->user->email) {
            Log::warning('SendEmailVendedorJob: Vendedor sem email configurado', [
                'venda_id' => $this->venda->id,
            ]);
            return;
        }

        if ($this->venda->email_vendedor_enviado) {
            Log::info('SendEmailVendedorJob: Email já enviado anteriormente', [
                'venda_id' => $this->venda->id,
            ]);
            return;
        }

        try {
            Mail::to($vendedor->user->email)
                ->send(new ConfirmacaoVendedorMail($this->venda, $this->pagamento));

            $this->venda->email_vendedor_enviado = true;
            $this->venda->save();

            Log::info('SendEmailVendedorJob: Email enviado com sucesso', [
                'venda_id' => $this->venda->id,
                'email' => $vendedor->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('SendEmailVendedorJob: Falha ao enviar email', [
                'venda_id' => $this->venda->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendEmailVendedorJob: Job falhou definitivamente', [
            'venda_id' => $this->venda->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}