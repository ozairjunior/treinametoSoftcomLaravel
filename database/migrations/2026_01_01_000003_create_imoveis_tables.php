<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imoveis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corretor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('codigo_referencia', 30)->unique();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['casa', 'apartamento', 'terreno', 'comercial']);
            $table->enum('finalidade', ['venda', 'locacao', 'ambos']);
            $table->enum('status', ['rascunho', 'disponivel', 'reservado', 'vendido', 'alugado', 'inativo'])->default('rascunho');
            $table->timestamp('reservado_ate')->nullable();
            $table->string('cep', 10);
            $table->string('logradouro');
            $table->string('numero', 20);
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 120);
            $table->string('cidade', 120);
            $table->string('uf', 2);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('area_util', 10, 2)->nullable();
            $table->decimal('area_total', 10, 2)->nullable();
            $table->unsignedSmallInteger('quartos')->default(0);
            $table->unsignedSmallInteger('suites')->default(0);
            $table->unsignedSmallInteger('banheiros')->default(0);
            $table->unsignedSmallInteger('vagas_garagem')->default(0);
            $table->enum('mobiliado', ['nao', 'semimobiliado', 'mobiliado'])->default('nao');
            $table->boolean('aceita_pets')->default(false);
            $table->boolean('imovel_habitado')->default(false);
            $table->decimal('valor_venda', 14, 2)->nullable();
            $table->decimal('valor_aluguel', 14, 2)->nullable();
            $table->decimal('valor_condominio', 12, 2)->default(0);
            $table->decimal('valor_iptu', 12, 2)->default(0);
            $table->decimal('taxa_administracao', 5, 2)->default(10.00);
            $table->string('tour_virtual_url')->nullable();
            $table->string('video_url')->nullable();
            $table->timestamps();

            $table->index(['status', 'finalidade', 'tipo'], 'idx_imoveis_busca_principal');
            $table->index(['cidade', 'bairro'], 'idx_imoveis_localizacao');
            $table->index('valor_venda');
            $table->index('valor_aluguel');
        });

        Schema::create('imovel_proprietario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->decimal('percentual_propriedade', 5, 2)->default(100.00);
            $table->timestamps();

            $table->unique(['imovel_id', 'cliente_id']);
        });

        Schema::create('imovel_midias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->enum('categoria', ['foto', 'planta', 'documento'])->default('foto');
            $table->string('titulo')->nullable();
            $table->string('arquivo_path');
            $table->boolean('is_capa')->default(false);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('acesso_restrito')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imovel_midias');
        Schema::dropIfExists('imovel_proprietario');
        Schema::dropIfExists('imoveis');
    }
};
