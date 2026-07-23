<?php
$validator = validator(
    ['nome' => 'Equipe Nova'],
    [
        'nome' => 'required|string|max:255',
        'gestor_id' => 'nullable|exists:users,id',
        'meta_mensal' => 'nullable|numeric',
        'cor' => 'nullable|string',
        'status' => 'string'
    ]
);
if ($validator->fails()) {
    echo "Fails: \n";
    print_r($validator->errors()->toArray());
} else {
    echo "Passes.\n";
}
