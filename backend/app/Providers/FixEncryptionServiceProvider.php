<?php

namespace App\Providers;

use Illuminate\Encryption\EncryptionServiceProvider as BaseEncryptionServiceProvider;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;

class FixEncryptionServiceProvider extends BaseEncryptionServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton('encrypter', function ($app) {
            $config = $app->make('config')->get('app');

            // Se a chave não existir, GERA UMA NOVA AUTOMATICAMENTE
            if (empty($config['key'])) {
                $key = Encrypter::generateKey($config['cipher']);
                
                // Tenta salvar no .env se possível
                $envPath = base_path('.env');
                if (file_exists($envPath) && is_writable($envPath)) {
                    $content = file_get_contents($envPath);
                    $base64Key = 'base64:' . base64_encode($key);
                    
                    if (preg_match('/^APP_KEY=.*$/m', $content)) {
                        $content = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$base64Key}", $content);
                    } else {
                        $content .= "\nAPP_KEY={$base64Key}\n";
                    }
                    
                    file_put_contents($envPath, $content);
                    
                    // Atualiza a configuração em tempo real
                    $app->make('config')->set('app.key', $base64Key);
                    $config['key'] = $base64Key;
                }
            }

            // Reimplementado corretamente para Laravel 13 - parent::registerEncrypter retorna void
            return (new Encrypter($this->parseKey($config), $config['cipher']))
                ->previousKeys(array_map(
                    fn ($key) => $this->parseKey(['key' => $key]),
                    $config['previous_keys'] ?? []
                ));
        });

        $this->registerSerializableClosureSecurityKey();
    }
}
