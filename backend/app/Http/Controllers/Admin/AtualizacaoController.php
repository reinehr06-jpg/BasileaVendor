<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AtualizacaoController extends Controller
{
    public function index()
    {
        return view('admin.atualizacao', [
            'instructions' => [
                '1. Faça backup completo do banco de dados e arquivos',
                '2. Pare os containers: docker-compose down',
                '3. Atualize o código: git pull origin main',
                '4. Reconstrua as imagens: docker-compose build --no-cache',
                '5. Rode as migrations: docker-compose exec backend php artisan migrate',
                '6. Limpe cache: docker-compose exec backend php artisan optimize',
                '7. Inicie: docker-compose up -d',
            ],
            'changelogUrl' => 'https://github.com/reinehr06-jpg/BasileaVendor/releases/latest'
        ]);
    }
}
