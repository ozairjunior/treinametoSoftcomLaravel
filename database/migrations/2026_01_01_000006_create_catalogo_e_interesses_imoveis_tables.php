<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imoveis', function (Blueprint $table) {
            $table->boolean('publicar_catalogo')->default(false)->after('status');
            $table->index(['publicar_catalogo', 'status'], 'idx_imoveis_catalogo_publico');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->string('origem', 40)->default('interno')->after('documentacao_validada');
            $table->enum('cadastro_status', ['pre_cadastro', 'completo'])->default('completo')->after('origem');
            $table->timestamp('consentimento_lgpd_at')->nullable()->after('cadastro_status');
        });

        Schema::create('interesses_imoveis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('corretor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo', ['contato', 'proposta'])->default('contato');
            $table->enum('status', ['novo', 'em_atendimento', 'contatado', 'convertido', 'encerrado'])->default('novo');
            $table->decimal('valor_proposto', 14, 2)->nullable();
            $table->text('mensagem');
            $table->timestamps();

            $table->index(['status', 'tipo'], 'idx_interesses_status_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interesses_imoveis');

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['origem', 'cadastro_status', 'consentimento_lgpd_at']);
        });

        Schema::table('imoveis', function (Blueprint $table) {
            $table->dropIndex('idx_imoveis_catalogo_publico');
            $table->dropColumn('publicar_catalogo');
        });
    }
};
