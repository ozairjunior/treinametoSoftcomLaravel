<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'corretor', 'financeiro', 'cliente'])->default('corretor');
            $table->enum('status', ['ativo', 'suspenso', 'inativo'])->default('ativo');
            $table->string('cpf', 14)->nullable()->unique();
            $table->string('creci', 30)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('foto')->nullable();
            $table->decimal('comissao_padrao', 5, 2)->default(0.00);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
