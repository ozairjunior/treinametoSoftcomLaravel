<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $table = 'contratos';

    protected $fillable = [
        'codigo_contrato',
        'proposta_id',
        'imovel_id',
        'cliente_id',
        'corretor_id',
        'tipo_contrato',
        'valor_final',
        'vigencia_inicio',
        'vigencia_fim',
        'indice_reajuste',
        'dia_vencimento',
        'garantia_locaticia',
        'valor_caucao',
        'apolice_seguro_fianca',
        'termos_contrato',
        'plataforma_assinatura',
        'hash_documento',
        'assinatura_cliente',
        'assinado_cliente_em',
        'assinatura_proprietario',
        'assinado_proprietario_em',
        'status',
    ];

    protected $casts = [
        'valor_final' => 'decimal:2',
        'valor_caucao' => 'decimal:2',
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'assinado_cliente_em' => 'datetime',
        'assinado_proprietario_em' => 'datetime',
    ];

    public function proposta()
    {
        return $this->belongsTo(Proposta::class, 'proposta_id');
    }

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

    public function possuiAssinatura(): bool
    {
        return !is_null($this->assinado_cliente_em) || !is_null($this->assinado_proprietario_em);
    }

    public static function gerarTextoPorTemplate(
        string $tipo,
        Imovel $imovel,
        Cliente $cliente,
        float $valorFinal,
        string $indiceReajuste,
        ?int $diaVencimento,
        string $garantia
    ): string {
        $nomesProprietarios = $imovel->proprietarios->pluck('nome_razao_social')->join(', ') ?: 'Proprietário Cadastrado';
        $enderecoImovel = "{$imovel->logradouro}, {$imovel->numero} - {$imovel->bairro}, {$imovel->cidade}/{$imovel->uf} (CEP {$imovel->cep})";
        $valorFormatado = 'R$ ' . number_format($valorFinal, 2, ',', '.');

        $titulo = [
            'locacao' => 'INSTRUMENTO PARTICULAR DE CONTRATO DE LOCAÇÃO DE IMÓVEL',
            'compra_venda' => 'INSTRUMENTO PARTICULAR DE PROMESSA DE COMPRA E VENDA DE IMÓVEL',
            'intermediacao' => 'CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE INTERMEDIAÇÃO IMOBILIÁRIA',
        ][$tipo] ?? 'CONTRATO IMOBILIÁRIO';

        return "{$titulo}\n\n"
            . "1. DAS PARTES CONTRATANTES:\n"
            . "PROPRIETÁRIO(S)/OUTORGANTE(S): {$nomesProprietarios}\n"
            . "CLIENTE/CONTRATANTE: {$cliente->nome_razao_social}, inscrito no CPF/CNPJ sob nº {$cliente->documento}.\n\n"
            . "2. DO OBJETO DO CONTRATO:\n"
            . "Imóvel código de referência [{$imovel->codigo_referencia}] ({$imovel->titulo}), situado em: {$enderecoImovel}.\n\n"
            . "3. DO VALOR E CONDIÇÕES FINANCEIRAS:\n"
            . "O valor ajustado entre as partes é de {$valorFormatado}.\n"
            . "Índice de Reajuste Anual: {$indiceReajuste}.\n"
            . ($diaVencimento ? "Dia de vencimento mensal: todo dia {$diaVencimento}.\n" : "")
            . "Garantia Contratual: " . strtoupper(str_replace('_', ' ', $garantia)) . ".\n\n"
            . "4. DA IMUTABILIDADE E ASSINATURA ELETRÔNICA:\n"
            . "Após a aposição da primeira assinatura digital por qualquer das partes, este instrumento torna-se imutável, gerando hash criptográfico SHA-256 para validação jurídica.";
    }
}
