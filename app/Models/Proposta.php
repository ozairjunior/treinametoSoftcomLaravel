<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Proposta extends Model
{
    protected $table = 'propostas';

    protected $fillable = [
        'imovel_id',
        'cliente_id',
        'corretor_id',
        'valor_oferecido',
        'forma_pagamento',
        'garantia_locaticia',
        'valida_ate',
        'rodada_atual',
        'status',
        'condicoes_observacoes',
        'assinatura_cliente',
        'assinado_em',
    ];

    protected $casts = [
        'valor_oferecido' => 'decimal:2',
        'valida_ate' => 'datetime',
        'assinado_em' => 'datetime',
    ];

    public function imovel()
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function corretor()
    {
        return $this->belongsTo(User::class, 'corretor_id');
    }

    public function rodadas()
    {
        return $this->hasMany(PropostaRodada::class, 'proposta_id')->orderBy('numero_rodada');
    }

    public function contrato()
    {
        return $this->hasOne(Contrato::class, 'proposta_id');
    }

    public static function expirarPropostasVencidas(): void
    {
        self::where('status', 'em_analise')
            ->where('valida_ate', '<', now())
            ->update(['status' => 'expirada']);
    }
}
