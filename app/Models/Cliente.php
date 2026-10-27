<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'corretor_id',
        'tipo_pessoa',
        'nome_razao_social',
        'email',
        'telefone',
        'documento',
        'documento_hash',
        'rg',
        'profissao',
        'estado_civil',
        'renda_comprovada',
        'comprovante_renda_path',
        'inscricao_estadual',
        'representante_legal',
        'is_proprietario',
        'is_interessado',
        'is_locatario_comprador',
        'documentacao_validada',
        'pref_finalidade',
        'pref_tipo_imovel',
        'pref_bairro',
        'pref_preco_min',
        'pref_preco_max',
        'anonimizado_em',
    ];

    protected $casts = [
        //'documento' => 'encrypted',
        //'rg' => 'encrypted',
        'is_proprietario' => 'boolean',
        'is_interessado' => 'boolean',
        'is_locatario_comprador' => 'boolean',
        'documentacao_validada' => 'boolean',
        'renda_comprovada' => 'decimal:2',
        'pref_preco_min' => 'decimal:2',
        'pref_preco_max' => 'decimal:2',
        'anonimizado_em' => 'datetime',
    ];

    public static function gerarHashDocumento(string $documento): string
    {
        $somenteNumeros = preg_replace('/\D/', '', $documento);
        return hash('sha256', $somenteNumeros ?: trim($documento));
    }

    public function corretor()
    {
        return $this->belongsTo(User::class, 'corretor_id');
    }

    public function interacoes()
    {
        return $this->hasMany(ClienteInteracao::class)->latest();
    }

    public function podeSerProprietario(): bool
    {
        return $this->is_proprietario && $this->documentacao_validada;
    }

    public function anonimizarDados(): void
    {
        $hashAnonimo = 'ANON-' . $this->id . '-' . time();
        $this->update([
            'nome_razao_social' => "Cliente Anonimizado #{$this->id}",
            'email' => null,
            'telefone' => null,
            'documento' => '000.000.000-00',
            'documento_hash' => hash('sha256', $hashAnonimo),
            'rg' => null,
            'representante_legal' => null,
            'comprovante_renda_path' => null,
            'anonimizado_em' => now(),
        ]);
    }
}
