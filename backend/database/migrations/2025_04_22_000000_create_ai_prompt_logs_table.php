<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_prompt_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tarefa')->index();
            $table->text('prompt_used');
            $table->text('user_input')->nullable();
            $table->text('ai_response')->nullable();
            $table->boolean('validated')->default(false);
            $table->json('validation_errors')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->float('execution_time')->nullable();
            $table->integer('tokens_used')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('message_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_prompt_logs');
    }
};
