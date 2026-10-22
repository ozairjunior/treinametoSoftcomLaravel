<?php

namespace App;

use App\Models\AuditLog;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_CORRETOR = 'corretor';
    public const ROLE_FINANCEIRO = 'financeiro';
    public const ROLE_CLIENTE = 'cliente';

    public const STATUS_ATIVO = 'ativo';
    public const STATUS_SUSPENSO = 'suspenso';
    public const STATUS_INATIVO = 'inativo';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'cpf',
        'creci',
        'telefone',
        'foto',
        'comissao_padrao',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'comissao_padrao' => 'decimal:2',
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCorretor(): bool
    {
        return $this->role === self::ROLE_CORRETOR;
    }

    public function isFinanceiro(): bool
    {
        return $this->role === self::ROLE_FINANCEIRO;
    }

    public function isAtivo(): bool
    {
        return $this->status === self::STATUS_ATIVO;
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
