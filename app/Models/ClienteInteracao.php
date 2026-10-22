<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ClienteInteracao extends Model
{
    protected $table = 'cliente_interacoes';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'tipo',
        'descricao',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
