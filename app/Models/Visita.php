<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    public const MINUTOS_DESLOCAMENTO = 30;

    protected $table = 'visitas';

    protected $fillable = [
        'imovel_id',
        'cliente_id',
        'corretor_id',
        'data_hora_inicio',
        'data_hora_fim',
        'status',
        'notificar_proprietario',
        'observacoes_agendamento',
        'checkin_em',
        'nivel_interesse',
        'feedback_objecoes',
        'intencao_proposta',
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
        'checkin_em' => 'datetime',
        'notificar_proprietario' => 'boolean',
        'intencao_proposta' => 'boolean',
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

    public static function corretorTemConflito(
        int $corretorId,
        Carbon $inicio,
        Carbon $fim,
        ?int $ignorarVisitaId = null
    ): ?self {
        $inicioComMargem = $inicio->copy()->subMinutes(self::MINUTOS_DESLOCAMENTO);
        $fimComMargem = $fim->copy()->addMinutes(self::MINUTOS_DESLOCAMENTO);

        return self::where('corretor_id', $corretorId)
            ->whereNotIn('status', ['cancelada', 'nao_compareceu'])
            ->when($ignorarVisitaId, fn ($q) => $q->where('id', '!=', $ignorarVisitaId))
            ->where(function ($q) use ($inicioComMargem, $fimComMargem) {
                $q->where('data_hora_inicio', '<', $fimComMargem)
                  ->where('data_hora_fim', '>', $inicioComMargem);
            })
            ->first();
    }

    public static function imovelTemConflito(
        int $imovelId,
        Carbon $inicio,
        Carbon $fim,
        ?int $ignorarVisitaId = null
    ): ?self {
        return self::where('imovel_id', $imovelId)
            ->whereNotIn('status', ['cancelada', 'nao_compareceu'])
            ->when($ignorarVisitaId, fn ($q) => $q->where('id', '!=', $ignorarVisitaId))
            ->where(function ($q) use ($inicio, $fim) {
                $q->where('data_hora_inicio', '<', $fim)
                  ->where('data_hora_fim', '>', $inicio);
            })
            ->first();
    }
}
