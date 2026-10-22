<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        Event::listen(Login::class, function (Login $event) {
            AuditLog::registrar(
                'login_sucesso',
                "Login realizado com sucesso ({$event->user->email})",
                $event->user->id
            );
        });

        Event::listen(Failed::class, function (Failed $event) {
            $emailTentado = $event->credentials['email'] ?? 'desconhecido';
            AuditLog::registrar(
                'login_falha',
                "Tentativa de login falhou para o e-mail: {$emailTentado}",
                $event->user ? $event->user->id : null,
                ['email_tentado' => $emailTentado]
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuditLog::registrar(
                    'logout',
                    "Usuário saiu do sistema ({$event->user->email})",
                    $event->user->id
                );
            }
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event) {
            AuditLog::registrar(
                'senha_redefinida',
                "Senha redefinida via recuperação de senha ({$event->user->email})",
                $event->user->id
            );
        });
    }
}
