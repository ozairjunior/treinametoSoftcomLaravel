# 🏢 Sistema de Gestão Imobiliária — Softcom

Sistema web completo para gestão de imobiliárias e corretores, desenvolvido em **PHP 8.2** e **Laravel 10** com interface **AdminLTE 3** e ambiente 100% preparado em **Docker**.

---

## 📋 O que este sistema faz?

O sistema controla ponta a ponta a operação de uma imobiliária, desde o primeiro contato com o cliente até a assinatura eletrônica do contrato:

1. **Controle de Acesso e Auditoria**:
   - Perfis de acesso separados (**Administrador**, **Corretor**, **Financeiro/Jurídico** e **Cliente**).
   - Ativação, suspensão e inativação de contas com bloqueio imediato da sessão caso um usuário ativo seja suspenso.
   - Registro automático de auditoria (logins com sucesso, falhas de login, trocas de senha e ações importantes com IP e data/hora).
2. **CRM de Clientes e Adequação à LGPD**:
   - Cadastro unificado para **Pessoa Física (PF)** e **Pessoa Jurídica (PJ)**.
   - Classificação flexível de papéis (**Proprietário**, **Interessado/Lead** e **Locatário/Comprador**).
   - **Segurança LGPD**: CPF, CNPJ e RG são salvos criptografados no banco de dados (**AES-256**), com opção de anonimização de dados.
   - **Linha do Tempo**: Histórico visual de todas as conversas (WhatsApp, ligações, visitas, propostas e contratos) dentro da ficha do cliente.
3. **Carteira de Imóveis e Multimídia**:
   - Cadastro detalhado (código de referência, tipo, finalidade, área, quartos, suítes, vagas, valores e taxas).
   - Preenchimento automático de endereço ao digitar o **CEP** (integração com **ViaCEP**).
   - Galeria multimídia para fotos (com definição de foto de capa), plantas baixas, links de **Tour Virtual 360°**, vídeos e documentos/matrículas restritos à equipe interna.
   - Ciclo de vida do imóvel: `Rascunho` $\rightarrow$ `Disponível` $\rightarrow$ `Reservado` $\rightarrow$ `Vendido / Alugado` $\rightarrow$ `Inativo`.
4. **Agenda de Visitas Inteligente**:
   - Agendamento vinculando Imóvel, Cliente e Corretor.
   - **Trava automática de deslocamento**: impede que o mesmo corretor tenha duas visitas sem um intervalo mínimo de **30 minutos** entre elas.
   - **Trava de ocupação**: impede que dois corretores diferentes agendem o mesmo imóvel no mesmo horário.
   - Registro de Check-in e Feedback pós-visita (nível de interesse do cliente, objeções e intenção de enviar proposta).
5. **Propostas, Contrapropostas e Contratos Imutáveis**:
   - Envio de propostas com controle de validade (48h úteis) e histórico de rodadas de **contraproposta**.
   - **Reserva Automática**: ao aceitar uma proposta, o imóvel muda sozinho para o status **Reservado** por 5 dias úteis.
   - **Geração Automática de Contratos**: cria a minuta preenchida automaticamente com os dados do imóvel, proprietários, cliente e valores.
   - **Assinatura Eletrônica e Imutabilidade**: gera código **Hash SHA-256** do documento. Após a primeira assinatura digital, o contrato fica **bloqueado contra alterações**. Quando ambas as partes assinam, o imóvel passa automaticamente para **Vendido** ou **Alugado**.

---

## 🛠️ Pré-requisitos (O que você precisa ter instalado)

Para rodar este projeto no seu computador sem precisar configurar PHP ou MySQL manualmente, você só precisa instalar dois programas gratuitos:

1. **[Git](https://git-scm.com/downloads)** (para baixar o projeto)
2. **[Docker Desktop](https://www.docker.com/products/docker-desktop/)** (programa que cria e roda o servidor PHP, banco de dados MySQL e Nginx automaticamente)

> ⚠️ **Importante**: Antes de começar os passos abaixo, abra o **Docker Desktop** no seu computador e aguarde alguns segundos até ele indicar que está ativo (*Engine running*).

---

## 🚀 Passo a Passo para Rodar o Projeto (Guia para Iniciantes)

Abra o terminal do seu computador (**PowerShell** no Windows) e execute os comandos abaixo, **um por vez**:

### Passo 1: Baixar o projeto e entrar na pasta
```powershell
git clone https://github.com/ozairjunior/treinametoSoftcomLaravel.git
cd treinametoSoftcomLaravel
```

### Passo 2: Criar o arquivo de configurações (`.env`)
O arquivo `.env` guarda as configurações de conexão com o banco de dados. Para criá-lo a partir do modelo pronto, rode:
```powershell
copy .env.example .env
```

### Passo 3: Criar a rede do Docker e ligar os servidores
```powershell
docker network create softcom_global_network
docker compose up -d --build
```
*(Na primeira vez, esse comando pode levar alguns minutos enquanto o Docker baixa o PHP 8.2, o MySQL 8 e o Nginx).*

### Passo 4: Instalar os pacotes do sistema e preparar o banco de dados
Agora que os containers estão ligados, rode estes 5 comandos para instalar as bibliotecas do Laravel, criar as tabelas e já popular o sistema com dados de exemplo:
```powershell
docker exec -it softcom_php composer install
docker exec -it softcom_php php artisan key:generate
docker exec -it softcom_php php artisan storage:link
docker exec -it softcom_php chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker exec -it softcom_php php artisan migrate:fresh --seed
```

---

## 🌐 Como Acessar o Sistema

Pronto! Agora basta abrir o seu navegador e acessar:

- 🖥️ **Sistema Imobiliário**: **[http://localhost:8000](http://localhost:8000)**
- 🗄️ **Gerenciador do Banco de Dados (phpMyAdmin)**: **[http://localhost:8080](http://localhost:8080)**
  - **Usuário do banco**: `root`
  - **Senha do banco**: `ar7711`

---

## 🔑 Usuários de Teste Já Cadastrados

Ao rodar o comando `migrate:fresh --seed`, o sistema já cria usuários prontos para você testar cada perfil (todos usam a senha **`fab1478`**):

| Perfil | E-mail de Login | Senha | O que pode fazer |
| :--- | :--- | :--- | :--- |
| **Administrador** | `fabrica@softcomtecnologia.com.br` | `fab1478` | Acesso total ao sistema, gestão de colaboradores e auditoria de acesso. |
| **Corretor Sênior** | `corretor@softcomtecnologia.com.br` | `fab1478` | Gestão de clientes (CRM), imóveis, visitas, propostas e contratos. |
| **Corretora** | `mariana@softcomtecnologia.com.br` | `fab1478` | Gestão de clientes (CRM), imóveis, visitas, propostas e contratos. |
| **Financeiro / Jurídico** | `financeiro@softcomtecnologia.com.br` | `fab1478` | Conferência de contratos, assinaturas, garantias e validação documental. |
| **Conta Suspensa** | `suspenso@softcomtecnologia.com.br` | `fab1478` | Usuário suspenso (ao tentar entrar, o sistema bloqueia o acesso e registra na auditoria). |

---

## 🔄 Comandos Úteis do Dia a Dia

- **Ligar o sistema** (quando você reiniciar o computador):
  ```powershell
  docker compose up -d
  ```
- **Desligar o sistema**:
  ```powershell
  docker compose stop
  ```
- **Resetar o banco de dados com os dados originais de apresentação**:
  ```powershell
  docker exec -it softcom_php php artisan migrate:fresh --seed
  ```
