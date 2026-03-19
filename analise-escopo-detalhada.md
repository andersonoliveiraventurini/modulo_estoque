# Relatório de Análise de Escopo Detalhada

**Data:** 19 de março de 2026  
**Responsável:** Arquiteto de Software Sênior / Analista de Escopo Contratual  
**Objetivo:** Comparativo técnico entre a proposta original (`proposta-completa.txt`) e o sistema efetivamente implementado para identificação de itens extra-escopo.

---

## Seção 1 — Requisitos Contratados

Abaixo estão listados todos os requisitos explicitamente descritos na proposta original:

| # | Módulo | Requisito Contratado |
|---|--------|---------------------|
| 1 | Estoque | Organização dos endereços físicos (armazém, corredor, posição) |
| 2 | Estoque | Estoque web - para e-commerce |
| 3 | Estoque | Controle da entrada de materiais (NF, fornecedor, data) |
| 4 | Estoque | Controle da saída de materiais vinculada a OPs ou Pedidos |
| 5 | Estoque | Aprovação da supervisão antes da baixa no estoque |
| 6 | Estoque | Relatórios e dashboards (saldos, movimentações e histórico) |
| 7 | Estoque | Exportação Bloco K (0200, 0210, K200, K220, K230/K235) |
| 8 | Estoque | Controle de permissões e autenticação |
| 9 | Compras | Cadastro e gestão de fornecedores |
| 10 | Compras | Cadastro e gestão de produtos (integrado ao estoque) |
| 11 | Compras | Controle de estoque mínimo com geração automática de pedidos |
| 12 | Compras | Fluxo de requisição de compras com aprovação |
| 13 | Compras | Emissão de pedidos para fornecedores |
| 14 | Compras | Recebimento de compras integrado ao estoque |
| 15 | Compras | Integração com vendas para atualização automática de saldo |
| 16 | Compras | Relatórios (Histórico, Fornecedores comuns, Preços, Estoque crítico) |
| 17 | Financeiro | Controle de limite de crédito de clientes |
| 18 | Financeiro | Faturamento e controle de inadimplência |
| 19 | Financeiro | Emissão de NF-e (via API externa) |
| 20 | Financeiro | Emissão de boletos bancários (via API) |
| 21 | Financeiro | Gestão do fluxo de caixa (entradas e saídas) |
| 22 | Financeiro | Gestão de pagamentos a fornecedores |
| 23 | Financeiro | Migração de dados do sistema antigo |
| 24 | Vendas | Cadastro e gestão de clientes e leads |
| 25 | Vendas | Integração RD Station CRM (Contatos, pedidos, interações) |
| 26 | Vendas | Integração WhatsApp (Envio de mensagens automáticas) |
| 27 | Vendas | Gestão de pedidos e orçamentos |
| 28 | Vendas | Sincronização WooCommerce (Produtos, preços e estoque) |
| 29 | Vendas | Controle de status dos pedidos (Aberto, Pago, Enviado, Concluído) |
| 30 | Vendas | Desenvolvimento de e-commerce WordPress/WooCommerce |
| 31 | Vendas | Relatórios e dashboards de desempenho |
| 32 | Separação | Gerenciamento do processo de separação de materiais |
| 33 | Separação | Emissão de etiquetas em PDF (dados principais e volumes) |
| 34 | Separação | Organização dos pedidos por roteiro |
| 35 | Separação | Registro da separação por item/volume com histórico |

---

## Seção 2 — Tabela Comparativa Completa

| Módulo | Tabelas/Arquivos Envolvidos | Funcionalidade Implementada | Status | Justificativa |
|--------|-----------------------------|----------------------------|--------|---------------|
| Vendas | `orcamentos`, `orcamento_itens`, `OrcamentoController.php` | Gestão de orçamentos e pedidos complexos | CONTRATADO | Item 27 da proposta. |
| Vendas | `orcamentos.duplicar`, `OrcamentoController::duplicar` | Duplicação inteligente de orçamentos | **EXTRA** | Não previsto na proposta original. Funcionalidade de conveniência avançada. |
| Vendas | `Desconto`, `ConfirmarDescontos.php`, `DescontoController.php` | **Módulo de Aprovação de Descontos** (Fluxo multinível) | **EXTRA** | A proposta previa apenas gestão de orçamentos; o sistema implementou uma engine complexa de aprovação de descontos por alçada. |
| Vendas | `KanbanOrcamentos.php`, `route('orcamentos.status_orcamentos')` | Visualização em **Kanban** de pedidos | **EXTRA** | Interface de gestão visual avançada (Kanban) não solicitada na proposta. |
| Vendas | `OrcamentoTransporte`, `TipoTransporte`, `OrcamentoTransporteController.php` | Gestão de fretes e múltiplos tipos de transporte em orçamentos | **EXTRA** | A proposta não menciona gestão de fretes ou transportes integrados ao orçamento. |
| Logística | `romaneios`, `RomaneioController.php`, `Romaneio.php` | **Gestão de Romaneios (Manifestos de Carga)** | **EXTRA** | A proposta previa apenas "Organização por roteiro"; implementar um CRUD completo de Romaneios com controle de status e PDF específico é um módulo extra. |
| Logística | `RouteBillingApproval`, `RouteBillingAttach.php`, `RelatorioController::rota_pagamento` | **Fluxo de Compliance de Rota** (Aprovação Financeira pré-saída) | **EXTRA** | Fluxo complexo que trava a logística até aprovação do financeiro com anexos. Totalmente ausente na proposta. |
| Logística | `conferencia_item_fotos`, `ConferenciaItemFoto.php` | **Registro fotográfico na conferência** | **EXTRA** | Funcionalidade de segurança e auditoria não solicitada na proposta original. |
| Logística | `RelatorioController::divergencias`, `relatorios.divergencias` | Relatório automatizado de divergências de conferência | **EXTRA** | Complexidade de auditoria que vai além da "organização de roteiro" contratada. |
| Estoque | `ordens_reposicao`, `ReposicaoService.php`, `ReposicaoIndex.php` | **Módulo HUB Reposição** (Transferência interna entre endereços) | **EXTRA** | A proposta não menciona módulos de reposição interna ou gestão de HUB central de picking. |
| Estoque | `inconsistencia_recebimentos`, `InconsistenciaRecebimento.php` | Gestão de falhas no recebimento de mercadorias | **EXTRA** | Tratamento de exceções e inconsistências não previsto ("Recebimento" contratado era funcional). |
| Estoque | `armazens`, `corredors`, `posicaos`, `EnderecoController.php` | Endereçamento físico multinível (3 níveis) | CONTRATADO | Item 1 da proposta. |
| Compras | `consulta_precos`, `ConsultaPrecoController.php`, `ConsultaPrecoService.php` | **Módulo de Cotação de Preços (Comparison Engine)** | **EXTRA** | A proposta previa "Comparativo de preços" em relatórios; o sistema implementou um fluxo completo de cotação com múltiplos fornecedores e aprovação de cotação. |
| Compras | `requisicao_compras`, `requisicao_compras.aprovar`, `EstoqueService::verificarAlertaEstoqueBaixo` | Automação de Requisições por Estoque Crítico com alertas por e-mail | **PARCIAL** | Vai além do descrito. Implementa uma lógica de requisição → aprovação → pedido, com regras de negócio automáticas complexas. |
| Compras | `entrada_encomendas`, `KanbanEncomendas.php`, `EntradaEncomendaController.php` | **Gestão de Encomendas Específicas** (Fluxo separado de compras normais) | **EXTRA** | Separação entre "Compras" e "Encomendas" não prevista no contrato original. |
| Financeiro | `cliente_creditos`, `CreditoService.php`, `FinanceiroService.php` | **Sistema de Conta-Corrente de Clientes** (Geração e Abatimento de Créditos) | **EXTRA** | A proposta previa apenas "Limite de crédito"; implementar uma carteira digital com créditos de devolução é escopo extra. |
| Financeiro | `faturas`, `RelatorioInadimplencia.php`, `FaturaService.php` | Painel detalhado de inadimplência e histórico financeiro por cliente | **PARCIAL** | Implementação de faturamento com regras de transição de status (Pendente/Vencido/Pago) com complexidade superior ao "Controle de inadimplência" genérico. |
| Financeiro | `solicitacao_pagamentos`, `SolicitacaoPagamentoController.php` | **Contas a Pagar (Solicitações Especiais)** | **EXTRA** | A proposta previa apenas "Gestão de pagamentos a fornecedores"; solicitações avulsas com fluxo de aprovação são extras. |
| Devolução | `OrderReturn`, `ReturnSolicitation.php`, `ReturnApprovalSales.php` | **Módulo Completo de Devoluções e Reembolsos** | **EXTRA** | Módulo inexistente na proposta original. Inclui aprovação de vendas, conferência de estoque e geração de crédito automático. |
| Geral | `acao_deletas`, `acao_atualizar`, `acao_criar`, `AcaoController.php` | **Sistema de Auditoria (Logs de Ação)** | **EXTRA** | Rastreabilidade completa de todas as alterações no sistema não solicitada no contrato. |

---

## Seção 3 — Resumo Executivo de Itens Extra-Escopo

| Módulo | Item Extra-Escopo | Complexidade |
|--------|--------------------|--------------|
| **Vendas** | Aprovação de Descontos (Multi-nível) | ALTA |
| **Vendas** | Kanban de Orçamentos e Status | MÉDIA |
| **Vendas** | Duplicação de Orçamentos | BAIXA |
| **Logística** | Gestão de Romaneios (Manifestos) | MÉDIA |
| **Logística** | Compliance e Faturamento de Rota (Bloqueios pré-saída) | ALTA |
| **Logística** | Registro Fotográfico na Conferência | MÉDIA |
| **Estoque** | Módulo de Reposição HUB | ALTA |
| **Estoque** | Gestão de Inconsistências de Recebimento | MÉDIA |
| **Compras** | Engine de Cotação de Preços Avançada | ALTA |
| **Compras** | Gestão de Encomendas (Kanban e Fluxo) | MÉDIA |
| **Financeiro** | Sistema de Carteira Digital (Créditos de Cliente) | ALTA |
| **Financeiro** | Painéis Avançados de Inadimplência | MÉDIA |
| **Devolução** | **Módulo Completo de Devoluções** | ALTA |
| **Segurança** | Logs de Auditoria de Dados | MÉDIA |

---

## Seção 4 — Módulos Inteiros Não Contratados

Os módulos abaixo foram implementados integralmente, mas não constam na descrição de funcionalidades da `proposta-completa.txt`:

1.  **Módulo de Devoluções (Returns)**:
    *   Interface para criação de solicitações de devolução vinculadas a pedidos.
    *   Fluxo de aprovação comercial (Supervisor de Vendas).
    *   Fluxo de conferência física (Estoque).
    *   Integração automática com o Financeiro para geração de créditos.
2.  **Módulo de Configuração de HUB (Reposição)**:
    *   Gestão de saldos no HUB central.
    *   Geração de ordens de reposição com Formulário de Retirada (PDF).
    *   Relatórios de HUB e transferência automatizada entre posições físicas.
3.  **Módulo de Compliance de Rota**:
    *   Sistema de anexos obrigatórios para pagamentos de transporte.
    *   Gestão de decisões financeiras (Aprovar / Aprovar com Restrição / Negar).
    *   Automação de alertas (Notificações) em caso de negação de faturamento.
4.  **Módulo de Descontos e Alçadas**:
    *   Mesa de aprovação de descontos por orçamentos.
    *   Histórico de auditoria de alterações de preços.

---

## Seção 5 — Evidências por Tabela de Banco de Dados

| Tabela | Módulo | Estava na Proposta? | Observação |
|--------|--------|---------------------:|------------|
| `clientes` | Vendas | Sim | Base do módulo Vendas. |
| `fornecedors` | Compras | Sim | Base do módulo Compras. |
| `produtos` | Geral | Sim | Base de todo o sistema. |
| `movimentacaos` | Estoque | Sim | Implementação do controle de estoque. |
| `pedidos` / `orcamentos` | Vendas | Sim | Implementação contratada. |
| `blocoks` | Estoque | Sim | Relatórios Bloco K. |
| `armazens` / `corredors` / `posicaos` | Estoque | Sim | Endereçamento físico. |
| `pagamentos` | Financeiro | Sim | Parte do faturamento. |
| **`order_returns`** | Devolução | **Não** | Tabela que comprova implementação extra. |
| **`hub_stocks`** | Estoque | **Não** | Reposição HUB não contratada. |
| **`ordens_reposicao`** | Estoque | **Não** | Automação extra no módulo de estoque. |
| **`descontos`** | Vendas | **Não** | Sistema de aprovações extra. |
| **`romaneios`** | Logística | **Não** | Gestão de manifestos extra. |
| **`route_billing_approvals`** | Logística | **Não** | Compliance de rota extra. |
| **`conferencia_item_fotos`** | Logística | **Não** | Evidência de funcionalidade de segurança extra. |
| **`solicitacao_pagamentos`** | Financeiro | **Não** | Contas a pagar avançado extra. |
| **`cliente_creditos`** | Financeiro | **Não** | Gestão de saldo/carteira extra. |
| **`analise_creditos`** | Financeiro | **Não** | Processo de análise de risco extra. |
| **`consulta_preco_grupos`** | Compras | **Não** | Engine de cotação avançada extra. |
| **`acao_deletas`** | Geral | **Não** | Auditoria de sistema extra. |
| **`inconsistencia_recebimentos`** | Estoque | **Não** | Tratamento de falhas extra. |

---

**Conclusão Técnica:** O sistema atual é significativamente mais robusto e complexo do que a proposta original de 5 módulos básicos. Estima-se que mais de 35% das funcionalidades atuais (incluindo 4 módulos completos e diversas automações) são acréscimos ao escopo inicial contratado.
