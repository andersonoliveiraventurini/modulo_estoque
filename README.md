# 📦 Sistema de Gestão — ERP Modular

> **Stack**: Laravel 12 · Livewire 3 · MySQL · Tailwind CSS · FluxUI Pro · Docker

---

## 🚀 Instalação e Configuração

### Pré-requisitos
- Docker + Docker Compose
- PHP 8.2+
- Node.js 20+
- Composer 2+

### Setup

```bash
cp .env.example .env
docker compose up -d
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run dev
```

### Variáveis de ambiente obrigatórias

| Variável | Descrição |
|---|---|
| `DB_DATABASE` | Nome do banco MySQL |
| `RDSTATION_TOKEN` | Token da API do RD Station CRM |
| `RDSTATION_USER_ID` | ID do usuário padrão no RD Station |
| `WOOCOMMERCE_URL` | URL base da loja WordPress |
| `WOOCOMMERCE_KEY` | Consumer Key da API REST do WooCommerce |
| `WOOCOMMERCE_SECRET` | Consumer Secret da API REST do WooCommerce |
| `MAIL_*` | Configurações SMTP para alertas de estoque |

---

## 🗺️ Mapa de Rotas e Funcionalidades

### Módulo: Orçamentos / Vendas

| Rota | Método | Função |
|---|---|---|
| `clientes.index` | GET | Lista de clientes (ponto de início para criar orçamento) |
| `orcamentos.index` | GET | Lista de orçamentos ativos |
| `orcamentos.criar` | GET | Cria novo orçamento para um cliente (`/clientes/{id}/orcamento`) |
| `orcamentos.show` | GET | Detalhe do orçamento |
| `orcamentos.copiar` | GET | Tela para duplicar um orçamento existente |
| `orcamentos.concluidos` | GET | Orçamentos finalizados / Movimentações financeiras |
| `orcamentos.kanban_orcamentos` | GET | Kanban de orçamentos por status |
| `orcamentos.status_orcamentos` | GET | Painel de status dos pedidos |
| `orcamentos.balcao` | GET | Caixa do balcão (venda presencial) |
| `orcamentos.balcao_concluidos` | GET | Pedidos finalizados do balcão |

**Status possíveis de um orçamento:** `Pendente` → `Aprovar desconto` → `Aprovar pagamento` → `Aprovado` → `Sem estoque` → `Pago` → `Cancelado`

---

### Módulo: Clientes

| Rota | Método | Função |
|---|---|---|
| `clientes.create` | GET | Pré-cadastro rápido |
| `clientes.create_completo` | GET | Cadastro completo com dados fiscais |
| `clientes.index` | GET | Lista de clientes com filtros e botão de WhatsApp |
| `clientes.edit` | GET/PUT | Edição de dados do cliente |
| `clientes.destroy` | DELETE | Exclusão (soft delete) |
| `bloqueios.index` | GET | Clientes com crédito bloqueado |
| `analise_creditos.index` | GET | Análise de crédito por cliente |

#### Accessor: `Cliente::getWhatsappUrlAttribute()`
Retorna a URL de WhatsApp (`https://wa.me/{numero}`) baseada no primeiro contato cadastrado.
Retorna `null` se o cliente não tiver contato com telefone.

---

### Módulo: Logística e Separação

| Rota | Função |
|---|---|
| `separacao.index` | Fila de batches de separação abertos |
| `logistica.separacao.lista` | Lista de itens individuais a separar |
| `conferencia.index` | Conferência pós-separação |
| `romaneios.index` | Gerenciamento de romaneios de entrega |
| `relatorios.separacao_por_roteiro` | Fila de carga agrupada por endereço |
| `relatorios.divergencias` | Relatório de divergências de conferência |

---

### Módulo: Compras

| Rota | Função |
|---|---|
| `fornecedores.index` / `create` | Listagem e cadastro de fornecedores |
| `consulta_preco.index` | Cotações e grupos de consulta de preço |
| `pedido_compras.index` | Pedidos de compra pendentes/recebidos |
| `requisicao_compras.index` | Requisições de compra (manuais ou automáticas) |
| `entrada_encomendas.index` | Recebimento de encomendas |
| `entrada_encomendas.kanban` | Kanban de encomendas por estágio |
| `relatorios.historico_compras` | Histórico de pedidos de compra |
| `relatorios.fornecedores_frequentes` | Fornecedores mais utilizados |
| `relatorios.comparativo_precos` | Comparativo de preços com gráfico |
| `relatorios.estoque_critico` | Produtos abaixo do estoque mínimo |

#### Automação: Estoque Crítico
Quando o estoque cai abaixo do mínimo (via `EstoqueService::verificarAlertaEstoqueBaixo()`):
- Envia e-mail para roles `admin` e `compras`
- Cria uma `RequisicaoCompra` automática com 2x o estoque mínimo

---

### Módulo: Estoque

| Rota | Função |
|---|---|
| `movimentacao.index` | Lista de movimentações de entrada/saída |
| `movimentacao.create` | Nova movimentação de estoque |
| `armazens.index` | Cadastro de armazéns físicos |
| `corredores.index` | Cadastro de corredores nos armazéns |
| `posicoes.index` | Cadastro de posições (endereçamento) |
| `inconsistencias.index` | Inconsistências de recebimento detectadas |
| `relatorios.index` | Central de relatórios de estoque |
| `reposicao.index` | **HUB Reposição** — Gestão de saldo e ordens |
| `reposicao.pdf` | Formulário de retirada para reposição |

#### Serviço: `EstoqueService`

| Método | Descrição |
|---|---|
| `reservarParaOrcamento(Orcamento $orcamento)` | Reserva estoque dos itens do orçamento. Lança exceção se insuficiente. |
| `liberarReservaDoOrcamento(Orcamento $orcamento)` | Cancela todas as reservas ativas de um orçamento (usado em cancelamentos). |
| `liberarReservas(Orcamento $orcamento, array $consumos)` | Marca reservas como `consumida` após conferência. |
| `baixarSaida(Conferencia $conf)` | Debita o `estoque_atual` dos produtos conferidos. Protegido por `DB::transaction`. |
| `checarEstoqueMinimo(Produto $produto, float $qtd)` | Retorna `true` se houver estoque disponível após reserva + quantidade solicitada. |
| `verificarAlertaEstoqueBaixo(Produto $produto)` | Dispara e-mail e cria requisição automática se estoque ≤ mínimo. |

---

### Módulo: HUB – Reposição de Produtos

Centraliza a movimentação de itens para o **HUB (Armazém ID 1)** para facilitar a separação e picking.

#### Fluxo de Operação:
1. **Solicitação**: Criada via botão "Solicitar Reposição" (gera `OrdemReposicao` pendente).
2. **Impressão**: O repositor imprime o **Formulário de Retirada (PDF)** com as localizações de origem.
3. **Execução**: Após a coleta física, o repositor confirma a ação no sistema, que realiza a transferência entre endereços físicos e o HUB.

#### Serviço: `ReposicaoService`

| Método | Descrição |
|---|---|
| `solicitarReposicao($produtoId, $qtd)` | Cria uma ordem de reposição pendente. |
| `confirmarReposicao($ordem, ...)` | Transfere o saldo do endereço físico para o HUB. |
| `devolverAoEstoque($produtoId, $qtd, ...)` | Retira do HUB e devolve para um endereço físico. |

---

### Módulo: Produtos

| Rota | Função |
|---|---|
| `produtos.index` | Lista de produtos com filtros |
| `produtos.create` | Cadastro de novo produto |
| `produtos.edit` | Edição de produto, preço e NCM |

---

### Módulo: Industria / Produção

| Rota | Função |
|---|---|
| `blocok.index` | Ordens de Produção |
| `blocok.descartes.index` | Registro de descartes de produção |
| `blocok.insumos.index` | Insumos consumidos na produção |
| `blocok.fiscal.index` | **Bloco K — Gerador SPED Fiscal** |

#### Serviço: `BlocokService`

| Método | Descrição |
|---|---|
| `gerarRegistro0200()` | Gera registros de itens (produtos) para o Bloco K SPED. |
| `gerarRegistroK200(Carbon $dataFim)` | Gera registros de saldos de estoque por produto. |
| `exportarTxt(Carbon $dataInicio, Carbon $dataFim)` | Consolida todos os registros e salva em `storage/app/public/sped/`. Retorna o path do arquivo. |
| `limparTexto(string $texto)` | Remove caracteres especiais para conformidade com o layout SPED. |

---

### Módulo: Financeiro

| Rota | Função |
|---|---|
| `faturamento.index` | Contas a Receber (faturas pendentes) |
| `solicitacoes-pagamento.index` | Contas a Pagar (aprovação de pagamentos especiais) |
| `faturamento.conferidos` | Orçamentos enviados ao financeiro |
| `faturamento.inadimplencia` | Painel de inadimplência |
| `faturamento.historicoCliente` | Histórico financeiro por cliente |
| `relatorios.fluxo_caixa` | **Relatório de Fluxo de Caixa** |
| `notas.index` | Notas fiscais emitidas |
| `orcamentos.concluidos` | Movimentações financeiras realizadas |

#### Serviço: `FaturaService`

| Método | Descrição |
|---|---|
| `gerarFaturasVenda($registro, array $dados)` | Cria parcelas de fatura para um orçamento/pedido pago. |
| `gerarFaturaPorOrcamento(Orcamento $orc)` | Cria uma fatura simples com vencimento em 30 dias. Ignora se já existir. |
| `verificarInadimplencia()` | Atualiza faturas `pendente` vencidas para `vencido`. Retorna a quantidade. |

#### Serviço: `FluxoCaixaService`

| Método | Descrição |
|---|---|
| `obterDadosFluxo($inicio, $fim)` | Retorna array consolidado com entradas/saídas previstas e realizadas no período. |
| `getEntradasPrevistas($inicio, $fim)` | Faturas pendentes com vencimento no período (agrupadas por data). |
| `getEntradasRealizadas($inicio, $fim)` | Faturas pagas no período (agrupadas por data de pagamento). |
| `getSaidasPrevistas($inicio, $fim)` | Pedidos de compra pendentes no período (agrupados por data do pedido). |
| `getSaidasRealizadas($inicio, $fim)` | Pedidos de compra recebidos no período (agrupados por data de atualização). |

---

### Módulo: CRM & Integrações

#### Serviço: `RdStationService`

| Método | Descrição |
|---|---|
| `sincronizarEmpresa(Cliente $cliente)` | Cria ou atualiza a Organização no RD Station CRM. Salva `rdstation_id` no cliente. |
| `registrarVenda(Orcamento $orc)` | Registra a venda como um **Deal Ganho** no CRM. Chamado automaticamente no fechamento de orçamentos (via `OrcamentoController`). |

> **Configuração**: Defina `RDSTATION_TOKEN` e `RDSTATION_USER_ID` no `.env`.

#### Serviço: `WooCommerceService`

| Método | Descrição |
|---|---|
| `atualizarEstoqueNoSite(Produto $produto)` | Busca o produto pelo SKU na loja WooCommerce e atualiza o `stock_quantity`. |
| `importarPedidos()` | Busca pedidos com status `processing` das últimas 24h e os importa para o ERP. |

> **Configuração**: Defina `WOOCOMMERCE_URL`, `WOOCOMMERCE_KEY` e `WOOCOMMERCE_SECRET` no `.env`.

#### Integrações de NF-e e Boletos (Mock)

Localizadas em `app/Integrations/Financial/`:

| Interface | Implementação | Descrição |
|---|---|---|
| `NfeIntegrationInterface` | `Mock/MockNfeService` | Emite, consulta e cancela NF-e (ambiente de homologação). |
| `BoletoIntegrationInterface` | `Mock/MockBoletoService` | Gera, consulta e cancela boletos bancários (simulação). |

Para usar as implementações reais de produção, substitua os Mocks no `AppServiceProvider`.

---

### Módulo: Descontos

| Rota | Função |
|---|---|
| `descontos.index` | Solicitações de desconto aguardando aprovação |
| `descontos.aprovados` | Descontos aprovados e histórico |

**Tipos de desconto:** `percentual` (sobre o total) · `produto` (por item) · `fixo` (valor em R$)

---

### Módulo: Administração

| Rota | Função |
|---|---|
| `usuarios.index` / `create` | Gerenciamento de usuários do sistema |
| `vendedores.index` | Cadastro de vendedores internos, externos e assistentes |
| `filament.admin.pages.dashboard` | Painel de permissões e papéis (Filament + Spatie) |
| `cores.index` | Cores e acabamentos dos produtos |
| `categorias.index` | Categorias de produtos |
| `subcategorias.index` | Subcategorias |
| `ncm.index` | Tabela de NCMs para uso fiscal |
| `rdstation.listar-empresas` | Lista organizações sincronizadas com o CRM |
| `rdstation.listar-negociacoes` | Lista deals do RD Station |
| `rdstation.checar-token` | Valida o token de configuração do RD Station |

---

## 🔒 Papéis e Permissões (Spatie Permissions)

| Role | Acesso Principal |
|---|---|
| `admin` | Acesso completo, recebe alertas de estoque |
| `compras` | Compras, fornecedores, pedidos, recebe alertas |
| `vendedor` | Orçamentos, clientes, balcão |
| `estoque` | Movimentações, separação, conferência |
| `financeiro` | Faturas, contas a receber/pagar, relatórios |

---

## ⚙️ Comandos Artisan

| Comando | Frequência | Descrição |
|---|---|---|
| `app:process-critical-stock` | Diário (via Scheduler) | Verifica produtos com estoque crítico e gera requisições automáticas |
| `php artisan migrate` | Manual | Executa migrations pendentes |
| `php artisan db:seed` | Manual | Popula tabelas com dados iniciais |

---

## 🧪 Testes

```bash
# Todos os testes
php artisan test

# Testes do módulo fiscal
php artisan test --filter BlocokTest

# Testes do fluxo de caixa
php artisan test --filter FluxoCaixaTest
```

---

## 📁 Estrutura de Serviços

```
app/Services/
├── BlocokService.php         ← Geração SPED Fiscal Bloco K
├── EstoqueService.php        ← Reservas, baixas e alertas de estoque
├── FaturaService.php         ← Faturas e controle de inadimplência
├── FluxoCaixaService.php     ← Consolidação financeira (previsto x realizado)
├── OrcamentoPdfService.php   ← Geração de PDF dos orçamentos
├── ReposicaoService.php      ← Movimentações e ordens de reposição HUB
├── RdStationService.php      ← Integração CRM RD Station
└── WooCommerceService.php    ← Integração E-commerce WooCommerce

app/Integrations/Financial/
├── NfeIntegrationInterface.php
├── BoletoIntegrationInterface.php
└── Mock/
    ├── MockNfeService.php
    └── MockBoletoService.php
```
