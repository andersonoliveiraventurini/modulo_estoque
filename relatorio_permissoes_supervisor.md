# 🔐 Relatório de Permissões — Role `supervisor`

## 1. Onde as permissões são definidas

> [!IMPORTANT]
> **Única fonte de definição:** `database/seeders/RolesAndPermissionsSeeder.php`

Não há permissões inseridas diretamente em migrations nem em enums/configs. Todo o gerenciamento de roles e permissões é centralizado no seeder `RolesAndPermissionsSeeder`. As migrations relacionadas ao "supervisor" referem-se apenas a campos de FK (`supervisor_id`) e status de fluxo (`pendente_supervisor`), **não** a permissões Spatie.

**Usuários seedados com role `supervisor`** (em `DatabaseSeeder.php`):
- Alline — `alline@acav.com`
- Jaqueline — `jaqueline@acav.com`
- Ana Claudia — `ana.claudia@acav.com`
- Juliane — `juliane@acav.com`
- Emily — `emily@acav.com`

---

## 2. Lista completa de permissões do role `supervisor`

Definidas em `RolesAndPermissionsSeeder.php` — linhas 73–87:

| # | Permissão | Categoria |
|---|-----------|-----------|
| 1 | `visualizar_movimentacao` | Estoque |
| 2 | `aprovar_movimentacao` | Estoque |
| 3 | `rejeitar_movimentacao` | Estoque |
| 4 | `visualizar_requisicao_compra` | Compras |
| 5 | `aprovar_requisicao_nivel_1` | Compras |
| 6 | `aprovar_requisicao_nivel_2` | Compras |
| 7 | `faturamento_rota_ver_faturamento` | Faturamento Rota |
| 8 | `criar_orcamento` | Orçamento |
| 9 | `editar_orcamento` | Orçamento |
| 10 | `devolucao_visualizar_dashboard` | Devoluções / Qualidade |
| 11 | `devolucao_criar_rnc` | Devoluções / Qualidade |
| 12 | `devolucao_solicitar_devolucao` | Devoluções / Qualidade |
| 13 | `devolucao_aprovar_supervisor` | Devoluções / Qualidade |

> [!NOTE]
> O role `admin` herda **todas** as permissões via `$roleAdmin->syncPermissions(Permission::all())`. Por design, o supervisor **não** possui as permissões `aprovar_requisicao_nivel_3`, `faturamento_rota_aprovar/rejeitar/validar_anexo`, `acessar_painel_admin`, nem as de criação de usuários.

---

## 3. Onde cada permissão é usada

### `visualizar_movimentacao`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `MovimentacaoPolicy.php` | `viewAny()` — L16, `view()` — L24 |
| **View** | `paginas/movimentacao/show.blade.php` | L25 — `@can('aprovar movimentacao')` (nota: pode estar desatualizado, veja observação abaixo) |

---

### `aprovar_movimentacao`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `MovimentacaoPolicy.php` | ⚠️ Não mapeado em nenhum método da policy atual (a policy cobre `visualizar` e `criar`, mas não tem método `aprovar`) |
| **View** | `paginas/movimentacao/show.blade.php` | L25 — `@can('aprovar movimentacao')` — usado diretamente como string de permissão Spatie |

> [!WARNING]
> A permissão `aprovar_movimentacao` está no seeder e referenciada direto na blade (`@can('aprovar movimentacao')` — note o espaço em vez de underscore), mas **não há método dedicado na `MovimentacaoPolicy`** para isso. Pode haver uma inconsistência no nome (`aprovar movimentacao` vs `aprovar_movimentacao`).

---

### `rejeitar_movimentacao`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `MovimentacaoPolicy.php` | ❌ Sem método na policy |
| **Uso no código** | — | Sem referência encontrada nas views/controllers além do seeder |

> [!WARNING]
> `rejeitar_movimentacao` está seedada mas **não foi encontrada em uso** em nenhum controller, Livewire ou view. Permissão pode ser órfã ou ainda não implementada.

---

### `visualizar_requisicao_compra`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `RequisicaoCompraPolicy.php` | `viewAny()` — L13, `view()` — L18 |
| **View** | `paginas/requisicao_compras/index.blade.php` | L8 — `@can('criar requisicao')` |
| **View** | `paginas/requisicao_compras/show.blade.php` | L20 — `@can('aprovar requisicao')` |

---

### `aprovar_requisicao_nivel_1`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `RequisicaoCompraPolicy.php` | `aprovarNivel1()` — L38 |
| **View** | `paginas/requisicao_compras/show.blade.php` | L20 — `@can('aprovar requisicao')` (via policy method) |

---

### `aprovar_requisicao_nivel_2`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `RequisicaoCompraPolicy.php` | `aprovarNivel2()` — L43 |

---

### `faturamento_rota_ver_faturamento`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `OrcamentoPolicy.php` | `viewBilling()` — L93 |
| **Livewire** | `PagamentoRota.php` | `authorize('viewBilling', ...)` — L93 |
| **View** | `livewire/lista-orcamento-rota-concluidos.blade.php` | L189 — `@can('viewBilling', Orcamento::class)` |
| **Sidebar** | `layouts/app/sidebar.blade.php` | L39 — `@can('viewBilling', Orcamento::class)` |

---

### `criar_orcamento`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `OrcamentoPolicy.php` | `create()` — L39 |
| **Controller** | `OrcamentoController.php` | `authorize('create', Orcamento::class)` — L264 |

---

### `editar_orcamento`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `OrcamentoPolicy.php` | `update()` — L51 |

---

### `devolucao_visualizar_dashboard`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `NonConformityPolicy.php` | `viewAny()` — L18, `view()` — L26 |
| **Policy** | `ProductReturnPolicy.php` | `viewAny()` — L18, `view()` — L26 |
| **Livewire** | `Devolucao/DevolucaoDashboard.php` | `authorize('viewAny', ProductReturn::class)` — L26 |
| **View** | `livewire/devolucao/devolucao-dashboard.blade.php` | L8 — `@can('create', NonConformity::class)`, L11 — `@can('create', ProductReturn::class)` |
| **Sidebar** | `layouts/app/sidebar.blade.php` | L192, L197 — acesso ao menu de devoluções |

---

### `devolucao_criar_rnc`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `NonConformityPolicy.php` | `create()` — L35, `update()` — L43 |
| **Livewire** | `Devolucao/NonConformityForm.php` | `authorize('create', NonConformity::class)` — L84, `authorize('update', ...)` — L82 |
| **View** | `livewire/devolucao/devolucao-dashboard.blade.php` | L141 — `@can('update', $rnc)` |

---

### `devolucao_solicitar_devolucao`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `ProductReturnPolicy.php` | `create()` — L34 |
| **Livewire** | `Devolucao/ProductReturnForm.php` | `authorize('create', ProductReturn::class)` — L73 |
| **View** | `livewire/devolucao/devolucao-dashboard.blade.php` | L11 — `@can('create', ProductReturn::class)` |
| **Sidebar** | `layouts/app/sidebar.blade.php` | L197 — item de menu de devoluções |

---

### `devolucao_aprovar_supervisor`

| Camada | Arquivo | Método / Local |
|--------|---------|----------------|
| **Policy** | `ProductReturnPolicy.php` | `approveSupervisor()` — L44, L52 (com e sem instância) |
| **Livewire** | `Devolucao/ProductReturnApproval.php` | `authorize('approveSupervisor', ...)` — L38, L66 |
| **Sidebar** | `layouts/app/sidebar.blade.php` | L204 — `@can('approveSupervisor', ProductReturn::class)` |

---

## 4. Resumo: Mapa Permissão × Policy × Consuming Code

```
visualizar_movimentacao
  └─ MovimentacaoPolicy::viewAny/view
        └─ movimentacao/show.blade.php

aprovar_movimentacao
  └─ ⚠️ Sem método na Policy — usado direto como string na blade
        └─ movimentacao/show.blade.php @can('aprovar movimentacao')

rejeitar_movimentacao
  └─ ⚠️ Sem uso encontrado — permissão órfã

visualizar_requisicao_compra
  └─ RequisicaoCompraPolicy::viewAny/view
        └─ requisicao_compras/index & show.blade.php

aprovar_requisicao_nivel_1
  └─ RequisicaoCompraPolicy::aprovarNivel1
        └─ requisicao_compras/show.blade.php

aprovar_requisicao_nivel_2
  └─ RequisicaoCompraPolicy::aprovarNivel2
        └─ (sem view explícita encontrada)

faturamento_rota_ver_faturamento
  └─ OrcamentoPolicy::viewBilling
        └─ PagamentoRota.php (Livewire)
        └─ lista-orcamento-rota-concluidos.blade.php
        └─ sidebar.blade.php

criar_orcamento
  └─ OrcamentoPolicy::create
        └─ OrcamentoController::store

editar_orcamento
  └─ OrcamentoPolicy::update
        └─ (autorização via policy genérica)

devolucao_visualizar_dashboard
  └─ NonConformityPolicy::viewAny/view
  └─ ProductReturnPolicy::viewAny/view
        └─ DevolucaoDashboard.php (Livewire)
        └─ devolucao-dashboard.blade.php
        └─ sidebar.blade.php

devolucao_criar_rnc
  └─ NonConformityPolicy::create/update
        └─ NonConformityForm.php (Livewire)
        └─ devolucao-dashboard.blade.php

devolucao_solicitar_devolucao
  └─ ProductReturnPolicy::create
        └─ ProductReturnForm.php (Livewire)
        └─ devolucao-dashboard.blade.php
        └─ sidebar.blade.php

devolucao_aprovar_supervisor
  └─ ProductReturnPolicy::approveSupervisor
        └─ ProductReturnApproval.php (Livewire)
        └─ sidebar.blade.php
```

---

## 5. Pontos de atenção ⚠️

| # | Problema | Localização |
|---|----------|-------------|
| 1 | `@can('aprovar movimentacao')` usa **espaço** em vez de underscore — pode não bater com a permissão `aprovar_movimentacao` | `movimentacao/show.blade.php` L25 |
| 2 | `rejeitar_movimentacao` está seedada mas **não tem uso** em nenhum controller, policy ou view | Seeder L76 |
| 3 | `aprovar_requisicao_nivel_2` está na policy mas **sem view ou controller** que a consuma diretamente | `RequisicaoCompraPolicy` L43 |
| 4 | A `MovimentacaoPolicy` não tem método `aprovar` nem `rejeitar` — as permissões de aprovação de movimentação são usadas via string Spatie diretamente, o que bypassa a policy | `movimentacao/show.blade.php` |
