<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('corretor_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('data_hora_inicio');
            $table->dateTime('data_hora_fim');
            $table->enum('status', [
                'agendada',
                'confirmada',
                'realizada',
                'cancelada',
                'nao_compareceu',
            ])->default('agendada');
            $table->boolean('notificar_proprietario')->default(false);
            $table->text('observacoes_agendamento')->nullable();
            $table->timestamp('checkin_em')->nullable();
            $table->enum('nivel_interesse', ['baixo', 'medio', 'alto'])->nullable();
            $table->text('feedback_objecoes')->nullable();
            $table->boolean('intencao_proposta')->default(false);
            $table->timestamps();

            $table->index(['corretor_id', 'data_hora_inicio', 'data_hora_fim'], 'idx_visitas_agenda_corretor');
            $table->index(['imovel_id', 'data_hora_inicio', 'data_hora_fim'], 'idx_visitas_ocupacao_imovel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
