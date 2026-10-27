<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatusAndRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        /*esse Bloco verifica se esta altenticado, não estando ele passa por ele e atribui o autenticador em user.
        em seguida ele faz a verificação se o ususario esta ativo. essa verificação ela é registrada como log.
        caso o usuario não esteja ativo, ele é redirecionado para a página de login. forçando o logout 
        e ja regenerando o token.
        o ultimo if faz uma verificação se a rota tem alguma restrição de perfil. tipo se ele é admin, corretor... 
        sobre esses pergis de usuarios foi usado o php artisan make:model User 
        e o autenticador foi usado o php artisan make:auth*/
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->isAtivo()) {
            AuditLog::registrar(
                'sessao_bloqueada',
                "Acesso bloqueado pois a conta está com status: {$user->status}",
                $user->id
            );

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Sua conta está ' . $user->status . '. Entre em contato com o administrador.',
            ]);
        }

        if (!empty($roles) && !in_array($user->role, $roles)) {
            AuditLog::registrar(
                'acesso_negado',
                "Tentou acessar a rota [{$request->path()}] sem permissão (Perfil atual: {$user->role})"
            );

            abort(403, 'Você não tem permissão para acessar este módulo.');
        }

        return $next($request);
    }
}
