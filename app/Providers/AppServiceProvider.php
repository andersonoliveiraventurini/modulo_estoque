<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Orcamento;
use App\Models\Cliente;
use App\Observers\OrcamentoObserver;
use App\Policies\OrcamentoPolicy;
use App\Policies\ClientePolicy;
use App\Events\OrcamentoAprovado;
use App\Events\OrcamentoCancelado;
use App\Events\OrcamentoFinalizado;
use App\Listeners\ReservarEstoqueAoAprovar;
use App\Listeners\GerarFaturaAoAprovar;
use App\Listeners\LiberarReservaAoCancelar;
use App\Listeners\LiberarReservaAoFinalizar;
use App\Services\CreditoService;
use App\Services\PagamentoService;

use App\Models\ProductReturn;
use App\Models\NonConformity;
use App\Models\Estorno;
use App\Policies\ProductReturnPolicy;
use App\Policies\NonConformityPolicy;
use App\Policies\EstornoPolicy;

use App\Events\StockMovementRegistered;
use App\Listeners\LogStockMovement;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registra o CreditoService como singleton
        // Isso garante que a mesma instância seja usada durante toda a requisição
        $this->app->singleton(CreditoService::class, function ($app) {
            return new CreditoService();
        });

        // Registra o PagamentoService como singleton
        // O Laravel resolverá automaticamente as dependências do construtor
        $this->app->singleton(PagamentoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Orcamento::class, OrcamentoPolicy::class);
        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(ProductReturn::class, ProductReturnPolicy::class);
        Gate::policy(NonConformity::class, NonConformityPolicy::class);
        Gate::policy(Estorno::class, EstornoPolicy::class);
        Orcamento::observe(OrcamentoObserver::class);

        // ─── Eventos de Orçamento ─────────────────────────────────────────────
        Event::listen(OrcamentoAprovado::class, ReservarEstoqueAoAprovar::class);
        Event::listen(OrcamentoAprovado::class, GerarFaturaAoAprovar::class);
        Event::listen(OrcamentoCancelado::class, LiberarReservaAoCancelar::class);
        Event::listen(OrcamentoFinalizado::class, LiberarReservaAoFinalizar::class);
        Event::listen(StockMovementRegistered::class, LogStockMovement::class);
    }
}
