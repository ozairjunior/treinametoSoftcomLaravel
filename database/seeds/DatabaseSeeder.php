<?php

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\ClienteInteracao;
use App\Models\Contrato;
use App\Models\Imovel;
use App\Models\Proposta;
use App\Models\PropostaRodada;
use App\Models\Visita;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $admin = User::updateOrCreate(
            ['email' => 'fabrica@softcomtecnologia.com.br'],
            [
                'name' => 'Administrador Softcom',
                'password' => Hash::make('fab1478'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ATIVO,
                'cpf' => '111.222.333-44',
                'telefone' => '(83) 99999-1000',
                'comissao_padrao' => 0.00,
            ]
        );

        $corretorCarlos = User::updateOrCreate(
            ['email' => 'corretor@softcomtecnologia.com.br'],
            [
                'name' => 'Carlos Eduardo (Corretor Sênior)',
                'password' => Hash::make('fab1478'),
                'role' => User::ROLE_CORRETOR,
                'status' => User::STATUS_ATIVO,
                'cpf' => '123.456.789-00',
                'creci' => 'CRECI-PB 12345-F',
                'telefone' => '(83) 99888-1122',
                'comissao_padrao' => 5.00,
            ]
        );

        $corretoraMariana = User::updateOrCreate(
            ['email' => 'mariana@softcomtecnologia.com.br'],
            [
                'name' => 'Mariana Albuquerque',
                'password' => Hash::make('fab1478'),
                'role' => User::ROLE_CORRETOR,
                'status' => User::STATUS_ATIVO,
                'cpf' => '234.567.890-11',
                'creci' => 'CRECI-PB 15890-F',
                'telefone' => '(83) 99777-3344',
                'comissao_padrao' => 6.00,
            ]
        );

        $financeiro = User::updateOrCreate(
            ['email' => 'financeiro@softcomtecnologia.com.br'],
            [
                'name' => 'Dra. Fernanda Lima (Jurídico/Fin.)',
                'password' => Hash::make('fab1478'),
                'role' => User::ROLE_FINANCEIRO,
                'status' => User::STATUS_ATIVO,
                'cpf' => '345.678.901-22',
                'telefone' => '(83) 99666-5566',
                'comissao_padrao' => 0.00,
            ]
        );

        User::updateOrCreate(
            ['email' => 'suspenso@softcomtecnologia.com.br'],
            [
                'name' => 'Lucas Rocha (Conta Suspensa)',
                'password' => Hash::make('fab1478'),
                'role' => User::ROLE_CORRETOR,
                'status' => User::STATUS_SUSPENSO,
                'cpf' => '456.789.012-33',
                'creci' => 'CRECI-PB 10200-F',
                'telefone' => '(83) 99555-7788',
                'comissao_padrao' => 4.50,
            ]
        );

        $propRoberto = Cliente::create([
            'corretor_id' => $corretorCarlos->id,
            'tipo_pessoa' => 'PF',
            'nome_razao_social' => 'Dr. Roberto Cavalcanti de Melo',
            'email' => 'roberto.melo@email.com',
            'telefone' => '(83) 99111-2233',
            'documento' => '524.891.024-15',
            'documento_hash' => Cliente::gerarHashDocumento('524.891.024-15'),
            'rg' => '2.451.890 SSP/PB',
            'profissao' => 'Médico Cardiologista',
            'estado_civil' => 'Casado',
            'renda_comprovada' => 38500.00,
            'is_proprietario' => true,
            'is_interessado' => false,
            'is_locatario_comprador' => false,
            'documentacao_validada' => true,
        ]);

        $propPatrimonium = Cliente::create([
            'corretor_id' => $corretoraMariana->id,
            'tipo_pessoa' => 'PJ',
            'nome_razao_social' => 'Patrimonium Participações e Imóveis LTDA',
            'email' => 'contato@patrimoniumimoveis.com.br',
            'telefone' => '(83) 3247-8899',
            'documento' => '18.456.789/0001-90',
            'documento_hash' => Cliente::gerarHashDocumento('18.456.789/0001-90'),
            'inscricao_estadual' => '16.123.456-7',
            'representante_legal' => 'Henrique Vasconcelos',
            'is_proprietario' => true,
            'is_interessado' => true,
            'is_locatario_comprador' => false,
            'documentacao_validada' => true,
            'pref_finalidade' => 'venda',
            'pref_tipo_imovel' => 'terreno',
            'pref_bairro' => 'Altiplano',
            'pref_preco_min' => 500000.00,
            'pref_preco_max' => 2500000.00,
        ]);

        $clienteBeatriz = Cliente::create([
            'corretor_id' => $corretorCarlos->id,
            'tipo_pessoa' => 'PF',
            'nome_razao_social' => 'Beatriz Nogueira Fontes',
            'email' => 'beatriz.fontes@email.com',
            'telefone' => '(83) 98812-3344',
            'documento' => '741.852.963-08',
            'documento_hash' => Cliente::gerarHashDocumento('741.852.963-08'),
            'rg' => '3.120.445 SSP/PB',
            'profissao' => 'Arquiteta',
            'estado_civil' => 'Solteira',
            'renda_comprovada' => 19200.00,
            'is_proprietario' => false,
            'is_interessado' => true,
            'is_locatario_comprador' => true,
            'documentacao_validada' => true,
            'pref_finalidade' => 'venda',
            'pref_tipo_imovel' => 'apartamento',
            'pref_bairro' => 'Manaíra',
            'pref_preco_min' => 650000.00,
            'pref_preco_max' => 1200000.00,
        ]);

        $clienteTechSolutions = Cliente::create([
            'corretor_id' => $corretoraMariana->id,
            'tipo_pessoa' => 'PJ',
            'nome_razao_social' => 'Nordeste Tech Solutions S.A.',
            'email' => 'expansao@nordestetech.com.br',
            'telefone' => '(83) 3021-4050',
            'documento' => '32.987.654/0001-22',
            'documento_hash' => Cliente::gerarHashDocumento('32.987.654/0001-22'),
            'inscricao_estadual' => 'ISENTO',
            'representante_legal' => 'Rafael Gondim',
            'is_proprietario' => false,
            'is_interessado' => true,
            'is_locatario_comprador' => true,
            'documentacao_validada' => true,
            'pref_finalidade' => 'locacao',
            'pref_tipo_imovel' => 'comercial',
            'pref_bairro' => 'Altiplano',
            'pref_preco_min' => 4500.00,
            'pref_preco_max' => 12000.00,
        ]);

        $leadGustavo = Cliente::create([
            'corretor_id' => $corretorCarlos->id,
            'tipo_pessoa' => 'PF',
            'nome_razao_social' => 'Gustavo Henrique Tavares',
            'email' => 'gustavo.tavares@email.com',
            'telefone' => '(83) 98765-4321',
            'documento' => '654.321.987-40',
            'documento_hash' => Cliente::gerarHashDocumento('654.321.987-40'),
            'rg' => '4.889.112 SSP/PB',
            'profissao' => 'Engenheiro de Software',
            'estado_civil' => 'Casado',
            'renda_comprovada' => 22000.00,
            'is_proprietario' => false,
            'is_interessado' => true,
            'is_locatario_comprador' => false,
            'documentacao_validada' => false,
            'pref_finalidade' => 'venda',
            'pref_tipo_imovel' => 'casa',
            'pref_bairro' => 'Bessa',
            'pref_preco_min' => 800000.00,
            'pref_preco_max' => 1400000.00,
        ]);

        ClienteInteracao::create([
            'cliente_id' => $clienteBeatriz->id,
            'user_id' => $corretorCarlos->id,
            'tipo' => 'whatsapp',
            'descricao' => 'Cliente entrou em contato buscando apartamento com vista mar em Manaíra ou Cabo Branco com no mínimo 3 suítes.',
        ]);
        ClienteInteracao::create([
            'cliente_id' => $clienteBeatriz->id,
            'user_id' => $corretorCarlos->id,
            'tipo' => 'telefone',
            'descricao' => 'Enviamos seleção de 3 imóveis no perfil. Cliente gostou muito do IMO-CB101 e do IMO-ALT202.',
        ]);
        ClienteInteracao::create([
            'cliente_id' => $leadGustavo->id,
            'user_id' => $corretorCarlos->id,
            'tipo' => 'whatsapp',
            'descricao' => 'Lead captado pelo portal. Busca casa em condomínio fechado no Bessa que aceite pets.',
        ]);

        $imovelCaboBranco = Imovel::create([
            'corretor_id' => $corretorCarlos->id,
            'codigo_referencia' => 'IMO-CB101',
            'titulo' => 'Cobertura Duplex Beira-Mar em Cabo Branco',
            'descricao' => 'Cobertura exclusiva frente mar com piscina privativa, espaço gourmet integrado, 4 suítes amplas e 4 vagas cobertas.',
            'tipo' => 'apartamento',
            'finalidade' => 'venda',
            'status' => 'disponivel',
            'cep' => '58045-010',
            'logradouro' => 'Av. Cabo Branco',
            'numero' => '1850',
            'complemento' => 'Cobertura 1501',
            'bairro' => 'Cabo Branco',
            'cidade' => 'João Pessoa',
            'uf' => 'PB',
            'latitude' => -7.1315200,
            'longitude' => -34.8224100,
            'area_util' => 285.00,
            'area_total' => 340.00,
            'quartos' => 4,
            'suites' => 4,
            'banheiros' => 6,
            'vagas_garagem' => 4,
            'mobiliado' => 'mobiliado',
            'aceita_pets' => true,
            'imovel_habitado' => false,
            'valor_venda' => 2450000.00,
            'valor_condominio' => 1850.00,
            'valor_iptu' => 4200.00,
            'taxa_administracao' => 6.00,
            'tour_virtual_url' => 'https://my.matterport.com/show/?m=example360',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        $imovelCaboBranco->proprietarios()->sync([$propRoberto->id]);

        $imovelAltiplano = Imovel::create([
            'corretor_id' => $corretorCarlos->id,
            'codigo_referencia' => 'IMO-ALT202',
            'titulo' => 'Apartamento Alto Padrão 3 Suítes no Altiplano Nobre',
            'descricao' => 'Andar alto com vista definitiva para o mar, varanda gourmet com cortina de vidro e móveis planejados.',
            'tipo' => 'apartamento',
            'finalidade' => 'ambos',
            'status' => 'disponivel',
            'cep' => '58046-310',
            'logradouro' => 'Rua João Cyrillo da Silva',
            'numero' => '420',
            'complemento' => 'Apto 2201',
            'bairro' => 'Altiplano Cabo Branco',
            'cidade' => 'João Pessoa',
            'uf' => 'PB',
            'latitude' => -7.1412000,
            'longitude' => -34.8195000,
            'area_util' => 162.00,
            'area_total' => 210.00,
            'quartos' => 3,
            'suites' => 3,
            'banheiros' => 5,
            'vagas_garagem' => 3,
            'mobiliado' => 'semimobiliado',
            'aceita_pets' => true,
            'imovel_habitado' => true,
            'valor_venda' => 1150000.00,
            'valor_aluguel' => 6500.00,
            'valor_condominio' => 980.00,
            'valor_iptu' => 2100.00,
            'taxa_administracao' => 10.00,
        ]);
        $imovelAltiplano->proprietarios()->sync([$propRoberto->id, $propPatrimonium->id]);

        $imovelCasaBessa = Imovel::create([
            'corretor_id' => $corretoraMariana->id,
            'codigo_referencia' => 'IMO-BES303',
            'titulo' => 'Casa Contemporânea em Condomínio Fechado no Bessa',
            'descricao' => 'Casa duplex recém-construída com energia solar, área de lazer completa com piscina aquecida e segurança 24h.',
            'tipo' => 'casa',
            'finalidade' => 'venda',
            'status' => 'reservado',
            'reservado_ate' => now()->addWeekdays(5),
            'cep' => '58037-050',
            'logradouro' => 'Av. Presidente Afonso Pena',
            'numero' => '900',
            'complemento' => 'Cond. Atlântico Sul - Lote 14',
            'bairro' => 'Bessa',
            'cidade' => 'João Pessoa',
            'uf' => 'PB',
            'area_util' => 240.00,
            'area_total' => 360.00,
            'quartos' => 4,
            'suites' => 3,
            'banheiros' => 5,
            'vagas_garagem' => 4,
            'mobiliado' => 'semimobiliado',
            'aceita_pets' => true,
            'imovel_habitado' => false,
            'valor_venda' => 1320000.00,
            'valor_condominio' => 750.00,
            'valor_iptu' => 1950.00,
            'taxa_administracao' => 5.00,
        ]);
        $imovelCasaBessa->proprietarios()->sync([$propPatrimonium->id]);

        $imovelComercialManaira = Imovel::create([
            'corretor_id' => $corretoraMariana->id,
            'codigo_referencia' => 'IMO-MAN404',
            'titulo' => 'Laje Corporativa Empresarial em Manaíra',
            'descricao' => 'Andar corporativo com piso elevado, climatização central VRF, 6 vagas privativas e auditório no prédio.',
            'tipo' => 'comercial',
            'finalidade' => 'locacao',
            'status' => 'alugado',
            'cep' => '58038-100',
            'logradouro' => 'Av. Governador Flávio Ribeiro Coutinho',
            'numero' => '500',
            'complemento' => 'Salas 801 a 804',
            'bairro' => 'Manaíra',
            'cidade' => 'João Pessoa',
            'uf' => 'PB',
            'area_util' => 195.00,
            'area_total' => 230.00,
            'quartos' => 0,
            'suites' => 0,
            'banheiros' => 4,
            'vagas_garagem' => 6,
            'mobiliado' => 'semimobiliado',
            'aceita_pets' => false,
            'imovel_habitado' => false,
            'valor_aluguel' => 8900.00,
            'valor_condominio' => 1400.00,
            'valor_iptu' => 3100.00,
            'taxa_administracao' => 10.00,
        ]);
        $imovelComercialManaira->proprietarios()->sync([$propPatrimonium->id]);

        $imovelTerreno = Imovel::create([
            'corretor_id' => $corretorCarlos->id,
            'codigo_referencia' => 'IMO-TER505',
            'titulo' => 'Lote Esquina Nascente em Condomínio Clube',
            'descricao' => 'Terreno plano de esquina pronto para construir, posição nascente sul.',
            'tipo' => 'terreno',
            'finalidade' => 'venda',
            'status' => 'rascunho',
            'cep' => '58046-500',
            'logradouro' => 'Rua Projetada Portal do Sol',
            'numero' => 'S/N',
            'complemento' => 'Quadra G Lote 01',
            'bairro' => 'Portal do Sol',
            'cidade' => 'João Pessoa',
            'uf' => 'PB',
            'area_util' => 450.00,
            'area_total' => 450.00,
            'quartos' => 0,
            'suites' => 0,
            'banheiros' => 0,
            'vagas_garagem' => 0,
            'mobiliado' => 'nao',
            'aceita_pets' => true,
            'imovel_habitado' => false,
            'valor_venda' => 620000.00,
            'valor_condominio' => 650.00,
            'valor_iptu' => 980.00,
            'taxa_administracao' => 6.00,
        ]);
        $imovelTerreno->proprietarios()->sync([$propRoberto->id]);

        Visita::create([
            'imovel_id' => $imovelAltiplano->id,
            'cliente_id' => $clienteBeatriz->id,
            'corretor_id' => $corretorCarlos->id,
            'data_hora_inicio' => now()->subDays(2)->setHour(15)->setMinute(0),
            'data_hora_fim' => now()->subDays(2)->setHour(16)->setMinute(0),
            'status' => 'realizada',
            'notificar_proprietario' => true,
            'observacoes_agendamento' => 'Proprietário avisado com 24h de antecedência.',
            'checkin_em' => now()->subDays(2)->setHour(15)->setMinute(3),
            'nivel_interesse' => 'alto',
            'feedback_objecoes' => 'Cliente adorou a vista e a ventilação. Pediu para apresentar proposta com entrada de 40% e financiamento do saldo.',
            'intencao_proposta' => true,
        ]);

        Visita::create([
            'imovel_id' => $imovelCaboBranco->id,
            'cliente_id' => $clienteBeatriz->id,
            'corretor_id' => $corretorCarlos->id,
            'data_hora_inicio' => now()->addDay()->setHour(10)->setMinute(0),
            'data_hora_fim' => now()->addDay()->setHour(11)->setMinute(0),
            'status' => 'confirmada',
            'notificar_proprietario' => false,
            'observacoes_agendamento' => 'Cliente levará o marido para conhecer a área gourmet.',
        ]);

        Visita::create([
            'imovel_id' => $imovelAltiplano->id,
            'cliente_id' => $leadGustavo->id,
            'corretor_id' => $corretoraMariana->id,
            'data_hora_inicio' => now()->addDay()->setHour(15)->setMinute(0),
            'data_hora_fim' => now()->addDay()->setHour(16)->setMinute(0),
            'status' => 'agendada',
            'notificar_proprietario' => true,
            'observacoes_agendamento' => 'Primeira visita presencial do cliente Gustavo.',
        ]);

        $propostaEmAnalise = Proposta::create([
            'imovel_id' => $imovelAltiplano->id,
            'cliente_id' => $clienteBeatriz->id,
            'corretor_id' => $corretorCarlos->id,
            'valor_oferecido' => 1110000.00,
            'forma_pagamento' => 'financiamento',
            'garantia_locaticia' => 'nenhuma',
            'valida_ate' => now()->addWeekdays(2),
            'rodada_atual' => 2,
            'status' => 'em_analise',
            'condicoes_observacoes' => 'Sinal de R$ 450.000,00 à vista + financiamento bancário aprovado.',
        ]);

        PropostaRodada::create([
            'proposta_id' => $propostaEmAnalise->id,
            'user_id' => $corretorCarlos->id,
            'numero_rodada' => 1,
            'autor_tipo' => 'cliente',
            'valor_proposto' => 1060000.00,
            'justificativa' => 'Oferta inicial da cliente Beatriz com pagamento de sinal imediato.',
        ]);

        PropostaRodada::create([
            'proposta_id' => $propostaEmAnalise->id,
            'user_id' => $corretorCarlos->id,
            'numero_rodada' => 2,
            'autor_tipo' => 'proprietario',
            'valor_proposto' => 1110000.00,
            'justificativa' => 'Proprietário aceita fechar por R$ 1.110.000,00 mantendo todos os móveis planejados.',
        ]);

        $propostaAceitaCasa = Proposta::create([
            'imovel_id' => $imovelCasaBessa->id,
            'cliente_id' => $clienteBeatriz->id,
            'corretor_id' => $corretoraMariana->id,
            'valor_oferecido' => 1290000.00,
            'forma_pagamento' => 'a_vista',
            'garantia_locaticia' => 'nenhuma',
            'valida_ate' => now()->addWeekdays(2),
            'rodada_atual' => 1,
            'status' => 'aceita',
            'condicoes_observacoes' => 'Pagamento à vista via TED mediante assinatura da promessa.',
        ]);

        PropostaRodada::create([
            'proposta_id' => $propostaAceitaCasa->id,
            'user_id' => $corretoraMariana->id,
            'numero_rodada' => 1,
            'autor_tipo' => 'cliente',
            'valor_proposto' => 1290000.00,
            'justificativa' => 'Proposta à vista aceita pelo proprietário.',
        ]);

        $propostaLocacao = Proposta::create([
            'imovel_id' => $imovelComercialManaira->id,
            'cliente_id' => $clienteTechSolutions->id,
            'corretor_id' => $corretoraMariana->id,
            'valor_oferecido' => 8900.00,
            'forma_pagamento' => 'aluguel_mensal',
            'garantia_locaticia' => 'seguro_fianca',
            'valida_ate' => now()->subDays(5),
            'rodada_atual' => 1,
            'status' => 'aceita',
            'condicoes_observacoes' => 'Contrato de 36 meses com Seguro Fiança Porto Seguro.',
        ]);

        $textoLocacao = Contrato::gerarTextoPorTemplate(
            'locacao',
            $imovelComercialManaira,
            $clienteTechSolutions,
            8900.00,
            'IGP-M',
            10,
            'seguro_fianca'
        );

        Contrato::create([
            'codigo_contrato' => 'CTR-2026-LOC01',
            'proposta_id' => $propostaLocacao->id,
            'imovel_id' => $imovelComercialManaira->id,
            'cliente_id' => $clienteTechSolutions->id,
            'corretor_id' => $corretoraMariana->id,
            'tipo_contrato' => 'locacao',
            'valor_final' => 8900.00,
            'vigencia_inicio' => now()->subDays(10),
            'vigencia_fim' => now()->addMonths(36),
            'indice_reajuste' => 'IGP-M',
            'dia_vencimento' => 10,
            'garantia_locaticia' => 'seguro_fianca',
            'apolice_seguro_fianca' => 'APOLICE-PORTO-998877',
            'termos_contrato' => $textoLocacao,
            'plataforma_assinatura' => 'ICP-Brasil / Clicksign',
            'hash_documento' => hash('sha256', $textoLocacao),
            'assinado_cliente_em' => now()->subDays(9),
            'assinado_proprietario_em' => now()->subDays(8),
            'status' => 'vigente_formalizado',
        ]);

        $textoCompraVenda = Contrato::gerarTextoPorTemplate(
            'compra_venda',
            $imovelCasaBessa,
            $clienteBeatriz,
            1290000.00,
            'IPCA',
            15,
            'nenhuma'
        );

        Contrato::create([
            'codigo_contrato' => 'CTR-2026-VEN02',
            'proposta_id' => $propostaAceitaCasa->id,
            'imovel_id' => $imovelCasaBessa->id,
            'cliente_id' => $clienteBeatriz->id,
            'corretor_id' => $corretoraMariana->id,
            'tipo_contrato' => 'compra_venda',
            'valor_final' => 1290000.00,
            'vigencia_inicio' => now(),
            'vigencia_fim' => now()->addMonths(12),
            'indice_reajuste' => 'IPCA',
            'dia_vencimento' => 15,
            'garantia_locaticia' => 'nenhuma',
            'termos_contrato' => $textoCompraVenda,
            'plataforma_assinatura' => 'ICP-Brasil / Hash Interno',
            'hash_documento' => hash('sha256', $textoCompraVenda),
            'assinado_cliente_em' => null,
            'assinado_proprietario_em' => null,
            'status' => 'aguardando_assinaturas',
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'evento' => 'login_sucesso',
            'descricao' => 'Login realizado com sucesso (fabrica@softcomtecnologia.com.br)',
            'ip_address' => '172.18.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0',
        ]);
        AuditLog::create([
            'user_id' => $admin->id,
            'evento' => 'status_conta_alterado',
            'descricao' => 'Alterou status de Lucas Rocha de [ativo] para [suspenso]',
            'ip_address' => '172.18.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0',
        ]);
        AuditLog::create([
            'user_id' => $corretoraMariana->id,
            'evento' => 'proposta_aceita',
            'descricao' => 'Proposta #2 aceita. Imóvel IMO-BES303 reservado automaticamente por 5 dias úteis.',
            'ip_address' => '172.18.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0',
        ]);
        AuditLog::create([
            'user_id' => $financeiro->id,
            'evento' => 'contrato_assinatura_digital',
            'descricao' => 'Assinatura digital concluída no contrato CTR-2026-LOC01. Imóvel IMO-MAN404 marcado como ALUGADO.',
            'ip_address' => '172.18.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0',
        ]);
    }
}
