<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PropostaRodada extends Model
{
    protected $table = 'proposta_rodadas';

    protected $fillable = [
        'proposta_id',
        'user_id',
        'numero_rodada',
        'autor_tipo',
        'valor_proposto',
        'justificativa',
    ];

    protected $casts = [
        'valor_proposto' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
