<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corretor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo_pessoa', ['PF', 'PJ'])->default('PF');
            $table->string('nome_razao_social');
            $table->string('email')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->text('documento');
            $table->string('documento_hash', 64)->unique();
            $table->text('rg')->nullable();
            $table->string('profissao')->nullable();
            $table->string('estado_civil', 40)->nullable();
            $table->decimal('renda_comprovada', 12, 2)->nullable();
            $table->string('comprovante_renda_path')->nullable();
            $table->string('inscricao_estadual', 40)->nullable();
            $table->string('representante_legal')->nullable();
            $table->boolean('is_proprietario')->default(false);
            $table->boolean('is_interessado')->default(true);
            $table->boolean('is_locatario_comprador')->default(false);
            $table->boolean('documentacao_validada')->default(false);
            $table->enum('pref_finalidade', ['venda', 'locacao', 'ambos'])->nullable();
            $table->string('pref_tipo_imovel', 50)->nullable();
            $table->string('pref_bairro')->nullable();
            $table->decimal('pref_preco_min', 14, 2)->nullable();
            $table->decimal('pref_preco_max', 14, 2)->nullable();
            $table->timestamp('anonimizado_em')->nullable();
            $table->timestamps();
        });

        Schema::create('cliente_interacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 40);
            $table->text('descricao');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_interacoes');
        Schema::dropIfExists('clientes');
    }
};
