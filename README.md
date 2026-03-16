# Sistema de Gestão de Estoque, Indústria e Logística

Este é um ecossistema completo para gestão empresarial, integrando desde o primeiro contato com o cliente (orçamento) até a expedição logística e o controle industrial (Bloco K).

---

## 🏛 Módulos do Sistema

### 1. CRM e Vendas (Orçamentos)
O coração do sistema, onde o ciclo de venda se inicia.
- **Orçamentos & Pedidos**: Criação dinâmica de orçamentos com suporte a múltiplos itens, cálculo automático de impostos e prazos.
- **Kanban de Vendas**: Visualização em funil para acompanhar o status de cada negociação.
- **Ciclo de Aprovação de Descontos**: Sistema de alçada onde vendedores solicitam descontos que devem ser aprovados por gestores.
- **Visualização Pública**: Link externo para que o cliente visualize o orçamento sem necessidade de login.
- **Duplicação e Cópia**: Agilidade na criação de novos pedidos com base em históricos existentes.

### 2. Gestão de Clientes e Crédito
Controle total sobre a base de parceiros comerciais.
- **Cadastro Completo**: Gestão de endereços múltiplos (entrega, cobrança), contatos e documentos.
- **Análise de Crédito**: Definição de limites de crédito e análise de risco para liberação de pedidos.
- **Bloqueios Automáticos**: Restrição de vendas para clientes inadimplentes ou com restrições manuais.
- **Movimentações de Crédito**: Histórico de uso de crédito e adiantamentos.

### 3. Compras e Suprimentos
Gestão da cadeia de suprimentos e entrada de materiais.
- **Requisições de Compra**: Solicitações internas que passam por fluxo de aprovação antes de virarem pedidos.
- **Pedidos de Compra**: Emissão de ordens de compra para fornecedores com controle de itens e valores.
- **Cotações de Preço**: Comparativo entre fornecedores para garantir a melhor compra.
- **Recebimento de Encomendas**: Fluxo de conferência de entrada para validar o que foi comprado vs. o que chegou.

### 4. Estoque e Almoxarifado
Controle físico e lógico de mercadorias.
- **Movimentações**: Registro de entradas, saídas e transferências com fluxo de aprovação para ajustes manuais.
- **Endereçamento Logístico**: Organização por Armazéns, Corredores e Posições (Gaiolas/Prateleiras).
- **Inconsistências de Recebimento**: Auditoria de erros detectados no momento da descararga.
- **Estoque Crítico**: Alertas automáticos para produtos abaixo do nível de segurança.

### 5. Logística e Expedição
Garantia de que o produto certo chegue ao cliente certo.
- **Separação (Picking)**: Fluxo parcial e acumulado, permitindo múltiplas saídas para um mesmo pedido.
- **Conferência (Packing)**: Validação física via bipagem, com suporte a conferência virtual.
- **Romaneios e Roteirização**: Agrupamento de entregas por roteiros e geração de mapas de carga.
- **Etiquetagem**: Emissão de etiquetas de volume personalizadas.

### 6. Financeiro e Contas a Receber
Gestão do fluxo de caixa e obrigações.
- **Contas a Receber**: Geração automática de faturas após a conferência concluída.
- **Fluxo de Pagamento**: Registro de pagamentos no balcão ou via banco, com gestão de comprovantes em PDF.
- **Contas a Pagar**: Solicitações de pagamento para despesas operacionais e fornecedores.
- **Notas Fiscais**: Integração para emissão e consulta de documentos fiscais.

### 7. Indústria (Bloco K)
Controle de produção e transformação de insumos.
- **Ordens de Produção**: Gestão de fabricação de itens compostos.
- **Consumo de Insumos**: Baixa automática de matéria-prima baseada na ficha técnica.
- **Descartes e Perdas**: Registro de desperdícios durante o processo produtivo para conformidade fiscal.

### 8. Inteligência e Relatórios (BI)
Dashboards e relatórios detalhados para suporte à decisão.
- **Dashboard Central**: Rota unificada para acesso a todos os relatórios do sistema.
- **Gestão de Validade**: Relatórios de vencimento por lote e alertas de proximidade.
- **Análise de Movimentação**: Histórico detalhado de reposições e fluxos internos.
- **Recebimento e N.C**: Auditoria de recebimento com vínculo a NF/Romaneio e registros de não conformidade.
- **Vendas e Margem**: Análise de lucratividade por produto, descontos concedidos e volume de vendas no período.
- **Estoque Crítico**: Visualização de itens abaixo do estoque mínimo.

---

## 🛠 Comandos Técnicos e Manutenção

### Atualização de Dados
```bash
# Atualiza dados de empresas via API de CNPJ
php artisan clientes:atualizar-cnpj --limit=100
```

### Limpeza e Reset
O sistema possui rotinas para resetar fluxos logísticos quando um orçamento é editado, garantindo que as reservas de estoque e os lotes de separação reflitam sempre a última versão do pedido.

---

## 💻 Stack Tecnológica
- **Backend**: Laravel 12 (PHP 8.4)
- **Frontend**: Livewire 3 + TailwindCSS + Flux UI
- **Banco de Dados**: MySQL 8
- **Relatórios**: DomPDF para geração de documentos fiscais e etiquetas.