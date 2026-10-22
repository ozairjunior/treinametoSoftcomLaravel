<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Imovel extends Model
{
    protected $table = 'imoveis';

    protected $fillable = [
        'corretor_id',
        'codigo_referencia',
        'titulo',
        'descricao',
        'tipo',
        'finalidade',
        'status',
        'reservado_ate',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'latitude',
        'longitude',
        'area_util',
        'area_total',
        'quartos',
        'suites',
        'banheiros',
        'vagas_garagem',
        'mobiliado',
        'aceita_pets',
        'imovel_habitado',
        'valor_venda',
        'valor_aluguel',
        'valor_condominio',
        'valor_iptu',
        'taxa_administracao',
        'tour_virtual_url',
        'video_url',
    ];

    protected $casts = [
        'aceita_pets' => 'boolean',
        'imovel_habitado' => 'boolean',
        'reservado_ate' => 'datetime',
        'valor_venda' => 'decimal:2',
        'valor_aluguel' => 'decimal:2',
        'valor_condominio' => 'decimal:2',
        'valor_iptu' => 'decimal:2',
        'taxa_administracao' => 'decimal:2',
    ];

    public function corretor()
    {
        return $this->belongsTo(User::class, 'corretor_id');
    }

    public function proprietarios()
    {
        return $this->belongsToMany(Cliente::class, 'imovel_proprietario', 'imovel_id', 'cliente_id')
            ->withPivot('percentual_propriedade')
            ->withTimestamps();
    }

    public function midias()
    {
        return $this->hasMany(ImovelMidia::class, 'imovel_id')->orderBy('ordem');
    }

    public function fotos()
    {
        return $this->hasMany(ImovelMidia::class, 'imovel_id')
            ->where('categoria', 'foto')
            ->orderByDesc('is_capa')
            ->orderBy('ordem');
    }

    public function fotoCapa()
    {
        return $this->hasOne(ImovelMidia::class, 'imovel_id')
            ->where('categoria', 'foto')
            ->orderByDesc('is_capa')
            ->orderBy('ordem');
    }
}
