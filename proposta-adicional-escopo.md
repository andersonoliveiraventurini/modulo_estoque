# Proposta de Readequação Financeira e E-commerce WooCommerce

## 1. Resumo Executivo

Este documento apresenta a análise financeira detalhada do projeto de software sob medida em fase de conclusão. Durante o desenvolvimento, o sistema evoluiu acompanhando o amadurecimento das regras de negócio do cliente, resultando na entrega de funcionalidades significativamente mais robustas do que as previstas no contrato original de 5 módulos básicos.

**Destaques:**
- **Valor Agregado:** O projeto entregou funcionalidades críticas que viabilizaram a operação real, como a carteira digital de créditos, compliance de rota e automação de HUB, que não estavam no escopo original.
- **Transparência:** Mais de 35% das funcionalidades atuais são acréscimos ao escopo inicial contratado.
- **Próximo Passo:** O módulo de e-commerce WooCommerce está pronto para ser iniciado sob uma nova modelagem técnica que garante consistência total de dados e automação de processos.
- **Equilíbrio:** A proposta visa o encerramento harmonioso do projeto, reconhecendo o esforço investido e garantindo a sustentabilidade da parceria comercial.

---

## 2. Situação Financeira Atual

| Item | Valor | Status |
| :--- | ---: | :--- |
| Contrato Original (Referencial 13 meses) | R$ 136.080,00 | Base 208h/módulo avg |
| Valor Negociado e Assinado | R$ 120.000,00 | ~11,8% de desconto original |
| Total Já Pago (8 parcelas de R$ 10k) | R$ 80.000,00 | Recebido |
| **Parcelas a vencer (Contrato atual)** | **R$ 40.000,00** | **Manter Intacto (4 x R$ 10k)** |
| Valor Total dos Extras Entregues | R$ 89.812,80 | Cálculo Conservador (Base R$ 90/h) |
| Orçamento E-commerce WooCommerce | R$ 10.800,00 | Escopo Refinado (Valor Máx) |
| **Total Justo Real do Projeto** | **R$ 236.692,80** | **Valor de Mercado Entregue** |

---

## 3. Evidências dos Extras (Variável 3)

Os valores foram calculados utilizando o referencial de **R$ 27.216,00** por módulo base (R$ 136.080 / 5) e os percentuais de complexidade definidos no contrato original.

| Item Extra-Escopo | Módulo | Complexidade | Horas Est. | Valor Cheio | Evidência (Tab/File) |
| :--- | :--- | :---: | :---: | ---: | :--- |
| **Módulo de Devoluções Completo** | Devolução | **ALTA** | 90,7h | R$ 8.164,80 | Tab: `order_returns` |
| **Automação Reposição HUB** | Estoque | **ALTA** | 90,7h | R$ 8.164,80 | Tab: `ordens_reposicao` |
| **Fluxo Compliance de Rota** | Logística | **ALTA** | 90,7h | R$ 8.164,80 | Tab: `route_billing_approvals` |
| **Mesa de Aprovação de Descontos** | Vendas | **ALTA** | 90,7h | R$ 8.164,80 | Tab: `descontos` |
| **Engine Cotação Preços Avançada** | Compras | **ALTA** | 90,7h | R$ 8.164,80 | Tab: `consulta_preco_grupos` |
| **Sistema Carteira Digital (Créditos)** | Financeiro | **ALTA** | 90,7h | R$ 8.164,80 | Tab: `cliente_creditos` |
| Gestão de Romaneios (Manifestos) | Logística | **MÉDIA** | 60,5h | R$ 5.443,20 | Tab: `romaneios` |
| Registro Fotográfico Conferência | Logística | **MÉDIA** | 60,5h | R$ 5.443,20 | Tab: `conferencia_item_fotos` |
| Inconsistência Recebimento | Estoque | **MÉDIA** | 60,5h | R$ 5.443,20 | Tab: `inconsistencia_recebs` |
| Kanban de Orçamentos e Status | Vendas | **MÉDIA** | 60,5h | R$ 5.443,20 | File: `KanbanOrcamentos.php` |
| Gestão de Encomendas (Kanban) | Compras | **MÉDIA** | 60,5h | R$ 5.443,20 | Tab: `entrada_encomendas` |
| Painéis de Inadimplência Avançados | Financeiro | **MÉDIA** | 60,5h | R$ 5.443,20 | File: `RelatorioInadimplencia.php` |
| Sistema de Auditoria (Logs) | Geral | **MÉDIA** | 60,5h | R$ 5.443,20 | Tab: `acao_deletas` |
| Duplicação Inteligente de Orçamentos | Vendas | **BAIXA** | 30,2h | R$ 2.721,60 | File: `OrcamentoController.php` |
| **TOTAIS** | | | **997,9h** | **R$ 89.812,80** | **Percentual: 66,0%** |

---

## 4. Orçamento E-commerce WooCommerce (Variável 2)

O módulo de Vendas original foi majoritariamente entregue (CRM, WhatsApp, Orçamentos, Relatórios). Estimamos que **70% do valor do módulo original** (R$ 36.180,00) foi absorvido por essas entregas. Os 30% restantes correspondem à integração com o e-commerce, revisada abaixo conforme a nova plataforma definida.

| Componente | Horas Mín. | Horas Máx. | Valor Mín. | Valor Máx. |
| :--- | :---: | :---: | ---: | ---: |
| Configuração WP/Woo + Tema Flex | 16 | 24 | R$ 1.440,00 | R$ 2.160,00 |
| API de integração REST Laravel | 24 | 36 | R$ 2.160,00 | R$ 3.240,00 |
| Webhooks e Sincronização em Tempo Real | 20 | 32 | R$ 1.800,00 | R$ 2.880,00 |
| Testes de Integração Ponta a Ponta | 12 | 16 | R$ 1.080,00 | R$ 1.440,00 |
| Deploy e Configuração Produção/SSL | 8 | 12 | R$ 720,00 | R$ 1.080,00 |
| **Total Estimado** | **80** | **120** | **R$ 7.200,00** | **R$ 10.800,00** |

---

## 5. Cenários de Renegociação Completa

### Cenário A — Valor Cheio de Mercado
Apresenta o valor bruto dos extras e do e-commerce sem descontos adicionais.

| Componente | Valor |
| :--- | ---: |
| Parcelas restantes (4 x R$ 10k) | R$ 40.000,00 |
| E-commerce WooCommerce (valor máximo) | R$ 10.800,00 |
| Extras entregues (valor cheio) | R$ 89.812,80 |
| **Total adicional ao contrato** | **R$ 100.612,80** |
| **Total geral remanescente do projeto** | **R$ 140.612,80** |

---

### Cenário B — Desconto Comercial (~12%)
Aplica o mesmo percentual de desconto concedido na negociação inicial (~11,8%) sobre os extras e o e-commerce.

| Componente | Valor Cheio | Com Desconto |
| :--- | ---: | ---: |
| Parcelas restantes (intactas) | R$ 40.000,00 | R$ 40.000,00 |
| E-commerce WooCommerce | R$ 10.800,00 | R$ 9.504,00 |
| Extras entregues | R$ 89.812,80 | R$ 79.035,26 |
| **Total adicional** | **R$ 100.612,80** | **R$ 88.539,26** |
| **Total geral** | | **R$ 128.539,26** |

---

### Cenário C — Parceria e Sustentabilidade (RECOMENDADO)
Desconto de **20%** sobre os extras e o e-commerce em reconhecimento à parceria de longo prazo.

| Componente | Valor Bruto | Valor Final (20% off) |
| :--- | ---: | ---: |
| Parcelas restantes | R$ 40.000,00 | R$ 40.000,00 |
| E-commerce WooCommerce | R$ 10.800,00 | **R$ 8.640,00** |
| Extras entregues | R$ 89.812,80 | **R$ 71.850,24** |
| **Total adicional à renegociação** | R$ 100.612,80 | **R$ 80.490,24** |
| **Total geral remanescente** | | **R$ 120.490,24** |

**Sugestões de Pagamento do Adicional (R$ 80.490,24):**

- **Opção 1:** 8 novas parcelas de R$ 10.061,28 mensais, iniciando após a quitação das 4 parcelas atuais do contrato.
- **Opção 2:** Antecipação em 4 parcelas de R$ 20.122,56 (Aumento das parcelas atuais para quitação rápida).
- **Opção 3:** Pagamento à vista com **5% de desconto extra sobre o saldo** (Total quitação: R$ 76.465,73).

---

## 6. Recomendação e Argumentação Técnica

Recomendamos o **Cenário C** por ser o mais equilibrado para fechamento imediato sem atrito comercial.

**Pontos para fundamentar a negociação:**
1.  **Transparência Técnica:** As tabelas do banco de dados (especialmente `order_returns`, `descontos` e `hub_stocks`) são provas físicas de que o sistema é significativamente maior e mais complexo do que o contratado originalmente.
2.  **Referencial Hora:** O valor de R$ 90/hora está abaixo da média de mercado para sistemas complexos e foi mantido fixo durante todo o projeto.
3.  **Entrega Orientada ao Negócio:** Os extras não foram criados unilateralmente, mas sim para atender bloqueios e necessidades reais dos usuários durante a jornada de implantação.

---

## 7. Próximos Passos

1.  Apresentação do escopo detalhado de integração do E-commerce WooCommerce.
2.  Assinatura do aditivo financeiro conforme cenário escolhido.
3.  **Cronograma Estimado:** 4 a 6 semanas para entrega e deploy completo do E-commerce após acordo financeiro.
