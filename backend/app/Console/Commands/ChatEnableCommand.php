<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ChatEnableCommand extends Command
{
    protected $signature = 'chat:enable {--global : Ativar globalmente (não apenas piloto)} {--gestor= : ID do gestor para ativar como piloto} {--disable : Desativar}';
    protected $description = 'Ativa ou desativa o módulo de chat';

    public function handle(): int
    {
        $global = $this->option('global');
        $gestorId = $this->option('gestor');
        $disable = $this->option('disable');

        if ($disable) {
            Setting::set('chat_enabled', false);
            $this->info('Chat módulo desativado globalmente.');
            return 0;
        }

        if (!$global && !$gestorId) {
            $this->error('Especificar --global ou --gestor=<id>');
            return 1;
        }

        if ($global) {
            Setting::set('chat_enabled', true);
            $this->info('Chat módulo ativado globalmente.');
            Log::info('Chat: Ativado globalmente');
            return 0;
        }

        if ($gestorId) {
            $exists = \App\Models\User::where('id', $gestorId)->where('perfil', 'gestor')->exists();
            if (!$exists) {
                $this->error("Gestor {$gestorId} não encontrado.");
                return 1;
            }

            Setting::set('chat_enabled', true);
            
            \Illuminate\Support\Facades\DB::table('chat_gestor_configs')->updateOrInsert(
                ['gestor_id' => $gestorId],
                ['chat_enabled' => true]
            );

            $this->info("Chat módulo ativado para gestor {$gestorId} (piloto).");
            Log::info('Chat: Ativado para gestor piloto', ['gestor_id' => $gestorId]);
            return 0;
        }

        return 0;
    }
}