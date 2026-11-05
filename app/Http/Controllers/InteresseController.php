<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InteresseImovel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InteresseController extends Controller
{
    public function index(Request $request)
    {
        $query = InteresseImovel::with(['cliente', 'imovel', 'corretor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->whereHas('cliente', function ($qc) use ($busca) {
                    $qc->where('nome_razao_social', 'like', "%{$busca}%")
                       ->orWhere('email', 'like', "%{$busca}%")
                       ->orWhere('telefone', 'like', "%{$busca}%");
                })->orWhereHas('imovel', function ($qi) use ($busca) {
                    $qi->where('codigo_referencia', 'like', "%{$busca}%")
                       ->orWhere('titulo', 'like', "%{$busca}%");
                });
            });
        }

        $interesses = $query->latest()->paginate(15);

        return view('interesses.index', compact('interesses'));
    }

    public function alterarStatus(Request $request, InteresseImovel $interesse)
    {
        $request->validate([
            'status' => ['required', Rule::in(['novo', 'em_atendimento', 'contatado', 'convertido', 'encerrado'])],
        ]);

        $statusAnterior = $interesse->status;
        $novoStatus = $request->status;

        $interesse->update(['status' => $novoStatus]);

        AuditLog::registrar(
            'interesse_status_alterado',
            "Alterou status do interesse #{$interesse->id} (Imóvel {$interesse->imovel->codigo_referencia}) de [{$statusAnterior}] para [{$novoStatus}]"
        );

        if ($novoStatus === 'convertido' && $statusAnterior !== 'convertido') {
            AuditLog::registrar(
                'interesse_convertido',
                "Interesse #{$interesse->id} do cliente {$interesse->cliente->nome_razao_social} no imóvel {$interesse->imovel->codigo_referencia} foi convertido em atendimento comercial."
            );
        }

        return back()->with('status', "Status do interesse #{$interesse->id} atualizado para " . strtoupper(str_replace('_', ' ', $novoStatus)) . "!");
    }
}
