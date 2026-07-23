<?php
App\Models\Equipe::where('status', 'inativa')->update(['gestor_id' => null]);
echo "Fixed inativa equipes.\n";
