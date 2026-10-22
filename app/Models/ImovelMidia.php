<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImovelMidia extends Model
{
    protected $table = 'imovel_midias';

    protected $fillable = [
        'imovel_id',
        'categoria',
        'titulo',
        'arquivo_path',
        'is_capa',
        'ordem',
        'acesso_restrito',
    ];

    protected $casts = [
        'is_capa' => 'boolean',
        'acesso_restrito' => 'boolean',
    ];

    public function imovel()
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }
}
