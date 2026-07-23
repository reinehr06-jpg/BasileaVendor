<?php
$equipe = App\Models\Equipe::first();
if (!$equipe) {
    echo "No equipe found\n";
    exit;
}
echo "Updating equipe: " . $equipe->id . "\n";
$validator = validator(
    ['nome' => 'Equipe Atualizada', 'status' => 'ativa', 'meta_mensal' => 1500, 'gestor_id' => $equipe->gestor_id],
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
    try {
        $equipe->update($validator->validated());
        echo "Update passed.\n";
    } catch (\Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
}
