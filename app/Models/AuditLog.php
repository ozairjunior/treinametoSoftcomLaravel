<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'evento',
        'descricao',
        'ip_address',
        'user_agent',
        'dados_extras',
    ];

    protected $casts = [
        'dados_extras' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function registrar(string $evento, ?string $descricao = null, ?int $userId = null, array $dadosExtras = []): self
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'evento' => $evento,
            'descricao' => $descricao,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'dados_extras' => !empty($dadosExtras) ? $dadosExtras : null,
        ]);
    }
}
