# Sistema de Módulo de Estoque e Logística

Este sistema gerencia o fluxo de orçamentos, separação de mercadorias, conferência e integração com o faturamento.

## 🚀 Fluxo de Logística

O sistema possui um fluxo robusto de logística dividido em etapas claras:

### 1. Separação de Mercadorias
- **Separação Parcial**: O sistema permite criar múltiplos lotes de separação para um mesmo orçamento.
- **Cálculo de Saldo**: Ao iniciar uma nova separação, o sistema sugere apenas a quantidade restante (Quantidade Pedida - Total já separado).
- **Inconsistências**: Caso um produto não seja encontrado, o separador registra o motivo, que alimenta o relatório de divergências.

### 2. Conferência
- **Conferência Acumulada**: Exibe o progresso total do orçamento (Solicitado vs Separado vs Conferido).
- **Conferência Direta (Virtual)**: Permite conferir itens que já constam no saldo do orçamento sem a necessidade de um novo lote de picking.
- **Gestão de Reservas**: O estoque permanece reservado até a conclusão total ou parcial, garantindo que os itens conferidos sejam baixados corretamente no faturamento.
- **Etiquetas**: Geração de etiquetas de volume (Simples) para identificação de caixas e pacotes.

### 3. Faturamento e Financeiro
- **Aprovação de Saída**: Após a conferência, o operador envia o orçamento para o financeiro.
- **Painel Financeiro**: Lista de orçamentos "Prontos para Faturar" com data e responsável pela conferência.
- **Geração de Faturas**: Integração automática que gera as contas a receber com base nos valores finais do orçamento.

### 4. Relatórios e Auditoria
- **Divergências Logísticas**: Painel unificado que aponta:
    - Faltas de estoque no momento da separação.
    - Divergências físicas (A mais ou A menos) detectadas na conferência.
    - Histórico de responsáveis e justificativas.

---

## 🛠 Comandos Técnicos

### Atualização de Dados de Clientes
Para atualizar os dados da empresa via API (executa em lotes para evitar bloqueios):
```bash
php artisan clientes:atualizar-cnpj --limit=100
```

### Manutenção de Registros
Caso precise resetar a logística de um orçamento (devido a edições no pedido), o sistema limpa automaticamente lotes e conferências vinculadas para permitir o reinício do processo.

---

## 💻 Tecnologias
- **Framework**: Laravel 12
- **Interface**: Livewire (Flux UI)
- **Banco de Dados**: MySQL