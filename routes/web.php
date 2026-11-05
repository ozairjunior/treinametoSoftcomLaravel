<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('/catalogo', 'CatalogoController@index')->name('catalogo.index');
Route::get('/catalogo/{imovel}', 'CatalogoController@show')->name('catalogo.show');
Route::post('/catalogo/{imovel}/interesse', 'CatalogoController@registrarInteresse')
    ->middleware('throttle:5,1')
    ->name('catalogo.interesse');

Auth::routes();

Route::middleware(['auth', 'status.role'])->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('interesses', 'InteresseController@index')->name('interesses.index');
    Route::patch('interesses/{interesse}/status', 'InteresseController@alterarStatus')->name('interesses.status');

    Route::resource('clientes', 'ClienteController')->except(['destroy']);
    Route::post('clientes/{cliente}/interacoes', 'ClienteController@storeInteracao')->name('clientes.interacoes.store');
    Route::post('clientes/{cliente}/anonimizar', 'ClienteController@anonimizar')->name('clientes.anonimizar');

    Route::resource('imoveis', 'ImovelController')->except(['destroy']);
    Route::patch('imoveis/{imovei}/status', 'ImovelController@alterarStatus')->name('imoveis.status');
    Route::post('imoveis/{imovei}/midias', 'ImovelController@uploadMidia')->name('imoveis.midias.upload');
    Route::patch('imoveis/{imovei}/midias/{midia}/capa', 'ImovelController@definirCapa')->name('imoveis.midias.capa');
    Route::delete('imoveis/{imovei}/midias/{midia}', 'ImovelController@removerMidia')->name('imoveis.midias.remover');

    Route::resource('visitas', 'VisitaController')->only(['index', 'create', 'store', 'show']);
    Route::patch('visitas/{visita}/feedback', 'VisitaController@registrarFeedback')->name('visitas.feedback');

    Route::resource('propostas', 'PropostaController')->only(['index', 'create', 'store', 'show']);
    Route::post('propostas/{proposta}/contraproposta', 'PropostaController@contraproposta')->name('propostas.contraproposta');
    Route::patch('propostas/{proposta}/decidir', 'PropostaController@decidir')->name('propostas.decidir');
    Route::post('propostas/{proposta}/assinar', 'PropostaController@assinar')->name('propostas.assinar');

    Route::get('contratos', 'ContratoController@index')->name('contratos.index');
    Route::get('propostas/{proposta}/gerar-contrato', 'ContratoController@createFromProposta')->name('contratos.create.proposta');
    Route::post('contratos', 'ContratoController@store')->name('contratos.store');
    Route::get('contratos/{contrato}', 'ContratoController@show')->name('contratos.show');
    Route::patch('contratos/{contrato}/termos', 'ContratoController@updateTermos')->name('contratos.update.termos');
    Route::post('contratos/{contrato}/assinar', 'ContratoController@assinar')->name('contratos.assinar');
    Route::get('contratos/{contrato}/pdf', 'ContratoController@pdf')->name('contratos.pdf');

    Route::middleware(['status.role:admin'])->group(function () {
        Route::resource('colaboradores', 'ColaboradorController')->except(['show', 'destroy']);
        Route::patch('colaboradores/{colaboradore}/status', 'ColaboradorController@alterarStatus')->name('colaboradores.status');
        Route::get('auditoria', 'ColaboradorController@auditoria')->name('auditoria.index');
    });
});
