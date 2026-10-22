<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('corretor_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('valor_oferecido', 14, 2);
            $table->enum('forma_pagamento', ['a_vista', 'financiamento', 'parcelamento_direto', 'aluguel_mensal']);
            $table->enum('garantia_locaticia', ['nenhuma', 'caucao', 'fiador', 'seguro_fianca'])->default('nenhuma');
            $table->dateTime('valida_ate');
            $table->unsignedSmallInteger('rodada_atual')->default(1);
            $table->enum('status', ['em_analise', 'aceita', 'recusada', 'expirada'])->default('em_analise');
            $table->text('condicoes_observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('proposta_rodadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposta_id')->constrained('propostas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('numero_rodada');
            $table->enum('autor_tipo', ['cliente', 'proprietario', 'corretor']);
            $table->decimal('valor_proposto', 14, 2);
            $table->text('justificativa')->nullable();
            $table->timestamps();
        });

        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_contrato', 30)->unique();
            $table->foreignId('proposta_id')->nullable()->constrained('propostas')->nullOnDelete();
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('corretor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipo_contrato', ['intermediacao', 'compra_venda', 'locacao']);
            $table->decimal('valor_final', 14, 2);
            $table->date('vigencia_inicio');
            $table->date('vigencia_fim')->nullable();
            $table->enum('indice_reajuste', ['IPCA', 'IGP-M', 'INPC', 'NENHUM'])->default('IPCA');
            $table->unsignedTinyInteger('dia_vencimento')->nullable();
            $table->enum('garantia_locaticia', ['nenhuma', 'caucao', 'fiador', 'seguro_fianca'])->default('nenhuma');
            $table->decimal('valor_caucao', 14, 2)->nullable();
            $table->string('apolice_seguro_fianca', 80)->nullable();
            $table->longText('termos_contrato');
            $table->string('plataforma_assinatura', 40)->default('ICP-Brasil / Hash Interno');
            $table->string('hash_documento', 64)->nullable();
            $table->timestamp('assinado_cliente_em')->nullable();
            $table->timestamp('assinado_proprietario_em')->nullable();
            $table->enum('status', ['rascunho', 'aguardando_assinaturas', 'parcialmente_assinado', 'vigente_formalizado', 'cancelado'])->default('aguardando_assinaturas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
        Schema::dropIfExists('proposta_rodadas');
        Schema::dropIfExists('propostas');
    }
};
