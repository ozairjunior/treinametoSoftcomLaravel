<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class InteresseImovel extends Model
{
    protected $table = 'interesses_imoveis';

    protected $fillable = [
        'imovel_id',
        'cliente_id',
        'corretor_id',
        'tipo',
        'status',
        'valor_proposto',
        'mensagem',
    ];

    protected $casts = [
        'valor_proposto' => 'decimal:2',
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
}
