<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIPromptLog extends Model
{
    protected $table = 'ai_prompt_logs';
    
    protected $fillable = [
        'tarefa',
        'prompt_used',
        'user_input',
        'ai_response',
        'validated',
        'validation_errors',
        'provider',
        'model',
        'execution_time',
        'tokens_used',
        'success',
        'error_message',
        'user_id',
        'lead_id',
        'message_id',
    ];

    protected $casts = [
        'validation_errors' => 'array',
        'success' => 'boolean',
        'validated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Contato::class, 'lead_id');
    }
}
