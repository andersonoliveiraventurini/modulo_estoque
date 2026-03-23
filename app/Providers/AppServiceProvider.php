<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Orcamento;
use App\Policies\OrcamentoPolicy;
use App\Observers\OrcamentoObserver;
use App\Events\OrcamentoAprovado;
use App\Events\OrcamentoCancelado;
use App\Events\OrcamentoFinalizado;
use App\Listeners\ReservarEstoqueAoAprovar;
use App\Listeners\GerarFaturaAoAprovar;
use App\Listeners\LiberarReservaAoCancelar;
use App\Listeners\LiberarReservaAoFinalizar;

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
        // Injeta automaticamente o CreditoService como dependência
        $this->app->singleton(PagamentoService::class, function ($app) {
            return new PagamentoService(
                $app->make(CreditoService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn($user, $ability) => true);
        Gate::policy(Orcamento::class, OrcamentoPolicy::class);
        Orcamento::observe(OrcamentoObserver::class);

        // ─── Eventos de Orçamento ─────────────────────────────────────────────
        Event::listen(OrcamentoAprovado::class, ReservarEstoqueAoAprovar::class);
        Event::listen(OrcamentoAprovado::class, GerarFaturaAoAprovar::class);
        Event::listen(OrcamentoCancelado::class, LiberarReservaAoCancelar::class);
        Event::listen(OrcamentoFinalizado::class, LiberarReservaAoFinalizar::class);
    }
}
