<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Encryption\Encrypter;

class AppKeyFixProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Verifica se a APP_KEY existe e é válida
        if (empty(config('app.key')) || strlen(config('app.key')) < 32) {
            // Gerar nova chave automaticamente
            $key = 'base64:' . base64_encode(Encrypter::generateKey(config('app.cipher')));
            
            // Salvar no .env
            $this->updateEnvFile('APP_KEY', $key);
            
            // Atualizar configuração em tempo real
            config(['app.key' => $key]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Atualiza variável no arquivo .env
     */
    private function updateEnvFile(string $key, string $value): void
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envPath);
            } else {
                return;
            }
        }

        $content = file_get_contents($envPath);
        $pattern = "/^{$key}=.*$/m";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "{$key}={$value}", $content);
        } else {
            $content .= "\n{$key}={$value}\n";
        }

        file_put_contents($envPath, $content);
    }
}
