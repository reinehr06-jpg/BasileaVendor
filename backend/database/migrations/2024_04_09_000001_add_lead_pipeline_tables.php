<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (!Schema::hasColumn('leads', 'etapa')) {
                    $table->string('etapa', 20)->default('novo')->after('status');
                }
                if (!Schema::hasColumn('leads', 'agendamento_id')) {
                    $table->foreignId('agendamento_id')->nullable()->after('etapa');
                }
                if (!Schema::hasColumn('leads', 'motivo_perda')) {
                    $table->text('motivo_perda')->nullable()->after('agendamento_id');
                }
                if (!Schema::hasColumn('leads', 'first_contact_at')) {
                    $table->timestamp('first_contact_at')->nullable()->after('motivo_perda');
                }
                if (!Schema::hasColumn('leads', 'converted_at')) {
                    $table->timestamp('converted_at')->nullable()->after('first_contact_at');
                }
            });
        }

        if (!Schema::hasTable('lead_schedules')) {
            Schema::create('lead_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
                $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
                $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('set null');
                $table->datetime('scheduled_at');
                $table->string('status', 20)->default('pending');
                $table->text('notes')->nullable();
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
                $table->index(['vendedor_id', 'scheduled_at']);
                $table->index(['status', 'scheduled_at']);
            });
        }

        if (!Schema::hasTable('lead_fields')) {
            Schema::create('lead_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->string('name')->unique();
                $table->string('label');
                $table->string('type')->default('text');
                $table->json('options')->nullable();
                $table->boolean('is_required')->default(false);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('lead_field_values')) {
            Schema::create('lead_field_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
                $table->foreignId('field_id')->constrained('lead_fields')->onDelete('cascade');
                $table->text('value')->nullable();
                $table->timestamps();
                $table->unique(['lead_id', 'field_id']);
            });
        }

        if (!Schema::hasTable('lead_transfer_history')) {
            Schema::create('lead_transfer_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
                $table->foreignId('from_vendedor_id')->nullable()->constrained('vendedores')->onDelete('set null');
                $table->foreignId('to_vendedor_id')->nullable()->constrained('vendedores')->onDelete('set null');
                $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('set null');
                $table->string('motivo')->nullable();
                $table->string('type')->default('manual');
                $table->timestamps();
                $table->index('lead_id');
            });
        }

        if (!Schema::hasTable('quick_replies')) {
            Schema::create('quick_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->onDelete('cascade');
                $table->string('shortcut')->unique();
                $table->text('content');
                $table->string('category')->nullable();
                $table->boolean('is_global')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_replies');
        Schema::dropIfExists('lead_transfer_history');
        Schema::dropIfExists('lead_field_values');
        Schema::dropIfExists('lead_fields');
        Schema::dropIfExists('lead_schedules');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['converted_at', 'first_contact_at', 'motivo_perda', 'agendamento_id', 'etapa']);
        });
    }
};