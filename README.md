# Sistema de Gestão — WT System

> **Stack**: Laravel 12 · Livewire 3 · MySQL · Tailwind CSS · FluxUI Pro · Docker

---

## Instalação e Configuração

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
| `orcamentos.rota_concluidos` | GET | Lista de pedidos de rota (Conferidos/Finalizados) |
| `orcamentos.rota_pagamento` | GET | Tela de faturamento e aprovação de Rota (Financeiro) |

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
| `logistica.carregamento` | **Carregamento de Rota** — Pedidos aprovados por dia |
| `romaneios.index` | Gerenciamento de romaneios de entrega |
| `relatorios.separacao_por_roteiro` | Fila de carga agrupada por endereço |
| `relatorios.divergencias` | Relatório de divergências de conferência |

---

### Módulo: Gestão de Estoque e HUB

| Rota | Função |
|---|---|
| `movimentacao.index` | Histórico de entradas e saídas de estoque |
| `movimentacao.create` | Lançamento de nova movimentação com **multi-alocação** |

---

### Módulo: Curva de Vendas (ABC)

| Rota | Método | Função |
|---|---|---|
| `curva_vendas.index` | GET | Tela de configuração e processamento da curva |
| `curva_vendas.processar` | POST | Executa a classificação automatizada baseada em parâmetros |
| `curva_vendas.reclassificar` | PATCH | Reclassificação manual de um produto com justificativa |
| `curva_vendas.auditoria` | GET | Histórico de alterações manuais realizadas |

**Funcionalidades:**
- Classificação automatizada (A, B, C, D) baseada em Valor Total ou Quantidade Vendida.
- Configuração de até 3 parâmetros simultâneos com faixas de valores customizáveis.
- Interface para ajuste manual da classificação com obrigatoriedade de justificativa.
- Auditoria completa de todas as alterações manuais para rastreabilidade.
- Integração direta com o cadastro de produtos.
| `estoque.reposicao.index` | Painel de controle do HUB e solicitações de reposição |
| `estoque.reposicao.manual` | **Reposição Manual** — Transferência direta para o HUB |
| `estoque.logs` | **Logs de Movimentação** — Auditoria completa por item/posição |

#### Regras de Negócio de Estoque
- **Baixa de Venda**: Ocorre exclusivamente no armazém **HUB** (ID 1). Se o saldo do HUB for insuficiente, a venda é bloqueada com status "Sem estoque", mesmo que haja saldo global.
- **Perecíveis**: Produtos marcados como perecíveis exigem obrigatoriamente a data de vencimento no momento da entrada.
- **Multi-alocação**: Uma única linha de entrada de nota fiscal pode ser distribuída em múltiplas posições físicas diferentes.
- **Auditoria (Logs)**: Toda movimentação física (entrada, saída por venda, transferência, reposição) gera um registro no `stock_movement_logs` com data, quantidade, posição e o colaborador responsável.

---

### Módulo de Devoluções e Saldo de Clientes

Este módulo gerencia o ciclo completo de retorno de mercadorias, desde a solicitação vinculada a um orçamento até a utilização do crédito financeiro gerado em futuras compras.

#### Fluxo de Dupla Aprovação
O processo é estruturado em etapas obrigatórias para garantir a integridade fiscal e física:
1.  **Solicitação (Devolução/Vendas)**: Iniciada no [ProductReturnForm](file:///c:/Users/and7_/Documents/GitHub/modulo_estoque/app/Livewire/Devolucao/ProductReturnForm.php) ao buscar um orçamento pago. O usuário seleciona os itens e a quantidade (limitada à venda original).
2.  **Etapa 1: Supervisor de Vendas**: O supervisor acessa a solicitação no painel e realiza a aprovação comercial. Caso negue, o processo é encerrado imediatamente.
3.  **Etapa 2: Chefe de Estoque (Inspeção)**: Após o "OK" comercial, o estoque realiza a inspeção física. O Chefe de Estoque emite o laudo de devolução e decide se o item retorna ao saldo físico. A aprovação final nesta etapa é o gatilho para a geração automática do crédito.

#### Regra de Cálculo do Crédito
Para evitar discrepâncias financeiras, o sistema utiliza a seguinte lógica matemática no [ProductReturnService](file:///c:/Users/and7_/Documents/GitHub/modulo_estoque/app/Services/ProductReturnService.php):
- **Base de Valor**: É utilizado o `valor_unitario_com_desconto` do item no orçamento original.
- **Cálculo**: `Crédito Gerado = (Preço Unitário Pago) x (Quantidade Devolvida)`.
- **Persistência**: O valor é somado à coluna `saldo_credito` na tabela `clientes` e registrado em um log de transações FIFO por validade.

#### Abatimento Automático no Checkout (Balcão e Rota)
Nas telas de [Pagamento Balcão](file:///c:/Users/and7_/Documents/GitHub/modulo_estoque/app/Livewire/PagamentoBalcao.php) e [Pagamento Rota](file:///c:/Users/and7_/Documents/GitHub/modulo_estoque/app/Livewire/PagamentoRota.php), o operador possui a opção **"Abater Crédito"**:
- **Se Crédito > Valor do Pedido**: O sistema abate o valor total da venda e o saldo restante do cliente é preservado (Ex: Crédito R$500 - Pedido R$100 = R$400 de saldo restante).
- **Se Crédito < Valor do Pedido**: O sistema consome todo o crédito disponível e exige que o operador adicione outras formas de pagamento para o saldo devedor restante (Ex: Crédito R$100 - Pedido R$500 = R$400 a pagar em dinheiro/cartão/pix).

> [!NOTE]
> As travas de segurança são aplicadas via `ProductReturnPolicy` e verificadas em tempo real nos componentes Livewire e na interface (UI).

### **Ação Necessária** 
Para que estas permissões sejam criadas no seu banco de dados local ou de produção, você precisa rodar o comando do seeder no terminal: 
 
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder 
``` 
Este comando irá registrar as novas permissões e associá-las aos cargos existentes sem apagar os dados atuais.

---

### Módulo: Faturamento de Rota (Compliance)

Fluxo obrigatório para pedidos do tipo **ROTA** (transportes 1, 2, 3, 6, 7). Garante que a mercadoria só saia do estoque após validação financeira.

#### Fluxo de Operação:
1. **Anexo (Vendedor)**: O vendedor anexa comprovantes de pagamento no detalhe do orçamento via componente `RouteBillingAttach`.
2. **Aprovação (Financeiro)**: O financeiro acessa a tela de faturamento (`orcamentos.rota_pagamento`), valida os anexos e seleciona a decisão:
   - **Aprovar**: Registra o pagamento e libera para logística.
   - **Aprovar com Restrição**: Registra o pagamento e gera PDF com a marca d'água **"RECEBER PAGAMENTO NA ENTREGA"**.
   - **Negar**: Cancela o faturamento e dispara `RouteBillingDeniedNotification` para Vendedor, Supervisor, Separação e Conferência.
3. **Trava de Logística**: O pedido **não aparece** na fila de separação até que possua uma aprovação e o `loading_day` definido.
4. **Carregamento**: Pedidos conferidos aparecem no cronograma semanal de carregamento.

---

### Módulo: Compras

| Rota | Função |
|---|---|
| `fornecedores.index` / `create` | Listagem e cadastro de fornecedores |
| `consulta_preco.index` | Cotações e grupos de consulta de preço |
| `pedido_compras.index` | Pedidos de compra pendentes/recebidos |
| `pedido_compras.consulta_prazo` | Consulta de prazos e follow-ups |
| `pedido_compras.relatorio` | Relatório tabular de pedidos de compra |
| `requisicao_compras.index` | Requisições de compra (manuais ou automáticas) |
| `entrada_encomendas.index` | Recebimento de encomendas |
| `entrada_encomendas.kanban` | Kanban de encomendas por estágio |
| `pedido_compra_followups.store` | Registro de interações e cobrança (AJAX) |
| `relatorios.historico_compras` | Histórico de pedidos de compra |
| `relatorios.fornecedores_frequentes` | Fornecedores mais utilizados |
| `relatorios.comparativo_precos` | Comparativo de preços com gráfico |
| `relatorios.estoque_critico` | Produtos abaixo do estoque mínimo |

#### Automação: Estoque Crítico
Quando o estoque cai abaixo do mínimo (via `EstoqueService::verificarAlertaEstoqueBaixo()`):
- Envia e-mail para roles `admin` e `compras`
- Cria uma `RequisicaoCompra` automática com 2x o estoque mínimo

#### Novo: Follow-up de Entrega
Ações de cobrança registradas na tela de **Consulta de Prazos**. Atualizam a previsão de entrega do pedido automaticamente quando o tipo é "Atualização de Prazo".

---

### Módulo: Faltas sem Pedido

| Rota | Função |
|---|---|
| `faltas.index` | Listagem de faltas com filtros de cliente/vendedor |
| `faltas.create` | Registro de demanda não atendida (Gera `FAL-XXXXX`) |
| `faltas.relatorio` | Relatório detalhado para análise de reposição |
| `faltas.pendentes` | API JSON para importação em Pedidos de Compra |

**Funcionalidade**: Permite que o vendedor registre o que o cliente queria mas não havia em estoque. Esses itens podem ser "puxados" para um Pedido de Compra real posteriormente.

---

### Módulo: Estoque

| Rota | Função |
|---|---|
| `movimentacao.index` | Lista de movimentações de entrada/saída |
| `movimentacao.create` | Nova movimentação de estoque (Multi-alocação) |
| `armazens.index` | Cadastro de armazéns físicos |
| `corredores.index` | Cadastro de corredores nos armazéns |
| `posicoes.index` | Cadastro de posições (endereçamento) |
| `inconsistencias.index` | Inconsistências de recebimento detectadas |
| `estoque.logs` | **Audit Log** — Histórico físico detalhado |
| `relatorios.index` | Central de relatórios de estoque |
| `relatorios.vendas_estoque_sugerido` | **Vendas e Estoque Sugerido** — Análise de performance e meta de estoque |
| `relatorios.projecao_compra` | **Projeção de Compra** — Cálculo inteligente de volume de compra |
| `relatorios.estoque_minimo` | **Estoque Mínimo (Vendas)** — Identifica produtos abaixo da meta calculada |
| `relatorios.estoque_minimo.historico` | Histórico e rastreabilidade de relatórios gerados |
| `relatorios.vencimento_produtos` | Relatório de validade por lote |
| `relatorios.reposicao_estoque` | Histórico de reposição |
| `estoque.reposicao.index` | **HUB Reposição** — Gestão de saldo e ordens |
| `estoque.reposicao.manual` | **Transferência Manual** — Envio direto para o HUB |
| `estoque.reposicao.pdf` | Formulário de retirada para reposição |

---

### Módulo: Validação de CNPJ (Compliance)

O sistema realiza a validação automática da situação cadastral de Clientes e Fornecedores junto à Receita Federal para garantir a conformidade fiscal antes de operações críticas (venda, faturamento e compras).

#### Lógica de Funcionamento:
- **Variável Principal**: `$isCnpjAtivo` (Livewire) ou `$ativo` (Controllers/Blade).
- **Fonte de Dados**: Consulta externa via **BrasilAPI** (`https://brasilapi.com.br/api/cnpj/v2/`).
- **Serviço Responsável**: `App\Services\CnpjService`.
- **Cache**: Os resultados das consultas são armazenados em cache por **24 horas**.
- **Regra de Ativação (Compliance Fiscal)**: O sistema considera o status fiscal válido somente se:
    1. A situação cadastral na Receita Federal for **"ATIVA"**.
    2. Existir pelo menos uma **Inscrição Estadual (IE)** com situação **"ATIVA"** (validação via BrasilAPI V2).

#### Impactos no Sistema:
1. **Orçamentos**: Se o cliente possuir pendência fiscal (CNPJ inativo ou sem IE ativa), um banner de "PENDÊNCIA FISCAL DETECTADA" e um alerta JS são exibidos.
2. **Pagamento de Rota**: Impede a finalização fluida e exibe banner crítico caso o cliente esteja irregular.
3. **Entrada de Encomendas**: Valida cada fornecedor da cotação. Caso um fornecedor não possua IE ativa, exibe o alerta específico: **"Favor verificar - Fornecedor sem inscrição estadual"**.


#### Relatórios e Inteligência de Estoque

O sistema possui um motor de cálculo dinâmico para sugestão de níveis de estoque, baseado no histórico real de saídas.

##### Relatório de Vendas e Estoque Sugerido
Localizado em `Estoque > Relatórios Estoque > Vendas e Estoque Sugerido`, este módulo automatiza o planejamento de compras:
- **Cálculo da Média Mensal**: O sistema analisa o total vendido no período selecionado (ex: últimos 6 meses) e divide pelo número exato de meses (dias / 30).
- **Meta de Estoque**: A meta de estoque sugerida é equivalente à média mensal de consumo calculada.
- **Gráficos Comparativos**: Visualização em barras do Top 10 produtos mais vendidos e comparação entre Média de Consumo vs. Estoque Atual.
- **Filtros**: Categoria, Fornecedor e Local de Estoque (Armazém).
- **Exportação**: Suporte nativo para PDF e Excel (CSV).

##### Relatório de Estoque Mínimo (Vendas)
Identifica rapidamente quais itens estão com saldo atual abaixo da média mensal calculada:
- **ID Sequencial**: Cada relatório gerado recebe uma identificação amigável (ex: `RELATÓRIO 12`) para fácil rastreabilidade.
- **Histórico**: Armazena quem gerou o relatório, quando, e quais parâmetros de data foram utilizados.

##### Relatório de Projeção de Compra
Módulo avançado para planejamento de compras e reposição de estoque:
- **Lógica de Cálculo**: `Projeção = (Consumo Mensal * Meses de Compra) - Estoque Atual - Consumo Previsto até Recebimento`.
- **Análise de Curva**: O sistema analisa automaticamente os últimos 6 meses de vendas para definir o consumo médio.
- **Previsão de Recebimento**: Considera o tempo de lead-time (entrega do fornecedor) para abater o que será vendido antes da mercadoria chegar.
- **Histórico e Exportação**: Permite salvar simulações e exportar para PDF ou Excel com estimativa de valor financeiro total.

#### Regras de Negócio — Relatórios Gerenciais

| Relatório | Lógica de Cálculo / Filtros |
|---|---|
| **Vendas e Estoque Sugerido** | **Média Mensal**: `Total Vendido / (Dias do Período / 30)`. **Meta de Estoque**: Equivalente à média mensal. |
| **Estoque Mínimo (Vendas)** | Identifica produtos onde `estoque_atual < média mensal`. Cada geração recebe um ID sequencial (Ex: `RELATÓRIO 1`). |
| **Estoque Crítico** | Query: `estoque_atual <= estoque_minimo`. Compara o saldo com o valor estático definido no cadastro do produto. |
| **Vencimento** | Filtra por lote de entrada aprovado. Permite segmentar por `tipo_produto_sped`. |
| **Não Conformidade** | Lista divergências entre `quantidade_esperada` e `quantidade_recebida` no recebimento. |

#### Serviço: `EstoqueService`

| Método | Descrição |
|---|---|
| `reservarParaOrcamento(Orcamento $orcamento)` | Reserva estoque dos itens do orçamento. Lança exceção se insuficiente. |
| `liberarReservaDoOrcamento(Orcamento $orcamento)` | Cancela todas as reservas ativas de um orçamento (usado em cancelamentos). |
| `liberarReservas(Orcamento $orcamento, array $consumos)` | Marca reservas como `consumida` após conferência. |
| `baixarSaida(Conferencia $conf)` | Debita o `estoque_atual` dos produtos conferidos. Protegido por `DB::transaction`. |
| `checarEstoqueMinimo(Produto $produto, float $qtd)` | Retorna `true` se houver estoque disponível após reserva + quantidade solicitada. |
| `verificarAlertaEstoqueBaixo(Produto $produto)` | Dispara e-mail e cria requisição automática se estoque ≤ mínimo. |
| `reservarParaOrcamento(Orcamento $orc)` | Reserva itens (tabela `estoque_reservas`) com proteção de idempotência. |
| `liberarReservaDoOrcamento(Orcamento $orc)`| Cancela reservas ativas. |

#### Automação de Fluxo (Events & Observers)
O sistema utiliza o `OrcamentoObserver` e eventos personalizados para disparar ações automáticas:
- **Orcamento Aprovado**: Dispara `OrcamentoAprovado` → `ReservarEstoqueAoAprovar` & `GerarFaturaAoAprovar`.
- **Orcamento Cancelado**: Dispara `OrcamentoCancelado` → `LiberarReservaAoCancelar`.
- **Orcamento Finalizado**: Dispara `OrcamentoFinalizado` → `LiberarReservaAoFinalizar`.
- **Movimentação Física**: Dispara `StockMovementRegistered` → `LogStockMovement` (Auditoria detalhada).

> [!IMPORTANT]
> A reserva de estoque possui proteção de idempotência via coluna `estoque_reservado_em` no model `Orcamento`, garantindo que múltiplas aprovações não dupliquem a reserva física.

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
| `historico.financeiro` | **Histórico Financeiro Completo** (Créditos, Pagamentos e Descontos) |

#### Gestão de Créditos:
- **Geração**: Via devoluções aprovadas ou ajustes manuais.
- **Abatimento**: No checkout (Pagamento Balcão), é possível abater o saldo disponível diretamente no valor total da venda.

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

### Cobrança Residual em Encomendas

#### O que é
Permite registrar um pagamento adicional em orçamentos do tipo **encomenda**
que já possuem ao menos um pagamento ativo. Utilizado para cobrar acréscimos
de valor após o pagamento inicial — como reajuste de preço ou itens adicionados
após a confirmação da encomenda.

#### Pré-requisitos
- O orçamento deve ser do tipo encomenda (`isEncomenda()` retorna `true`)
- O orçamento deve ter ao menos 1 pagamento com `estornado = false`

#### Como usar
1. Acesse **Orçamentos → [id do orçamento]** (`/orcamentos/{id}`)
2. O bloco **"Cobrança Residual"** aparece automaticamente abaixo da
   seção de Totais e Descontos quando os pré-requisitos são atendidos
3. Informe o valor do acréscimo e, opcionalmente, uma observação
4. Clique em **"Registrar Cobrança Residual"**
5. O pagamento é registrado e a tela é atualizada automaticamente

#### Impacto no sistema
- O pagamento residual é salvo na tabela `pagamentos` com `tipo = 'residual'`
- É **somado normalmente** ao total pago via `pagamentoFinalizado()`,
  pois o `scopeAtivos()` filtra apenas por `estornado = false`
- **Nenhuma lógica de pagamento existente foi alterada**

#### Arquivos envolvidos

| Tipo | Arquivo |
|------|---------|
| Migration | `database/migrations/2026_03_31_203111_add_tipo_to_pagamentos_table.php` |
| Model | `app/Models/Pagamento.php` |
| Model | `app/Models/Orcamento.php` |
| Livewire Class | `app/Livewire/OrcamentoPagamentoResidual.php` |
| Livewire View | `resources/views/livewire/orcamento-pagamento-residual.blade.php` |
| View modificada | `resources/views/paginas/orcamentos/show.blade.php` |
| Componente alterado | `app/Livewire/OrcamentoShow.php` |

#### Decisões técnicas
- Criado componente Livewire **isolado** para não interferir nos componentes
  existentes (`PagamentoBalcao`, `PagamentoRota`, `OrcamentoShow`)
- A coluna `tipo` na tabela `pagamentos` é **nullable com default null**,
  garantindo compatibilidade total com todos os registros existentes
- O campo `condicao_pagamento_id` do pagamento residual herda o valor de
  `condicao_id` do orçamento pai

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

#### Histórico de Descontos:
O sistema mantém um log detalhado (`customer_discount_history`) de todas as alterações em valores de desconto concedidos, permitindo auditoria de datas, usuários e valores anteriores vs. atuais.

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

### Módulo: Baixa de Estoque e Reservas

Este módulo implementa o controle rigoroso de reservas e baixas físicas, priorizando o armazém **HUB (ID 1)** e garantindo a rastreabilidade total de todas as movimentações.

#### 1. Reserva de Itens no Orçamento
Quando um orçamento é aprovado, o sistema executa automaticamente a reserva dos itens:
- **Prioridade HUB**: O sistema tenta reservar primeiro do saldo disponível no HUB.
- **Reserva de Outras Posições**: Caso o HUB seja insuficiente, o restante é reservado do "Estoque Principal" (outros armazéns).
- **Notificação Automática**: Se o item estiver zerado ou insuficiente no HUB, um alerta é gerado para o setor de estoque: *"Item [código] zerado no HUB - reserva efetuada do estoque principal"*.

#### 2. Baixa por Venda
No momento do faturamento (conferência finalizada), a baixa definitiva é executada:
- Segue a sequência: **HUB primeiro**, depois demais posições.
- Gera log de auditoria detalhado em `stock_movement_logs`.

#### 3. Baixa por Problemas (RNC)
Funcionalidade integrada ao módulo de Qualidade:
- Exige obrigatoriamente a descrição detalhada do motivo.
- Permite selecionar se a baixa deve ser realizada fisicamente.
- Segue a mesma lógica de priorização do HUB.

#### 4. Reabastecimento para Pedidos
Rotina de movimentação interna que permite transferir produtos reservados para o HUB:
- Garante que o material esteja acessível ao separador físico.
- Mantém o vínculo com o orçamento original.
- Move automaticamente a reserva de "Estoque Principal" para "HUB" após a transferência.

#### 5. Auditoria e Logs
Toda operação gera registros completos com:
- Tipo de movimento (Venda, RNC, Reposição).
- Quantidade, Origem e Destino.
- Usuário responsável e Timestamp.
- Número do orçamento vinculado e Motivo (quando aplicável).

#### Interface de Notificação
O setor de estoque possui um painel de alertas em tempo real (`estoque.notifications`) que exibe:
- Itens com estoque zerado no HUB.
- Alertas de reabastecimento necessário.
- Movimentações pendentes.

---

## 🔒 Papéis e Permissões (Spatie Permissions)

| Role | Acesso Principal |
|---|---|
| `admin` | Acesso completo, recebe alertas de estoque |
| `compras` | Compras, fornecedores, pedidos, recebe alertas |
| `vendedor` | Orçamentos, clientes, balcão, **anexos de rota** |
| `estoque` | Movimentações, separação, conferência, **carregamento** |
| `financeiro` | Faturas, contas a receber/pagar, **aprovação de rota** |

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

# Testes de Reserva de Estoque e Eventos
php artisan test tests/Feature/OrcamentoReservaEstoqueTest.php
```

---

## 📁 Estrutura de Serviços

```
app/Services/
├── BlocokService.php         ← Geração SPED Fiscal Bloco K
├── EstoqueService.php        ← Reservas, baixas e alertas de estoque
├── FaturaService.php         ← Faturas e controle de inadimplência
├── FluxoCaixaService.php     ← Consolidação financeira (previsto x realizado)
├── FinancialService.php      ← Lógica de abatimentos e geração de créditos
├── CreditoService.php         ← Gestão de saldo e movimentações de crédito do cliente
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
