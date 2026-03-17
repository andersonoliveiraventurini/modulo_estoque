<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Orcamento;
use App\Observers\OrcamentoObserver;
use App\Policies\RouteBillingPolicy;
use Illuminate\Support\Facades\Gate;

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
        Orcamento::observe(OrcamentoObserver::class);

        // Registro da Policy de Faturamento de Rota
        // Como o faturamento de rota é uma extensão do Orçamento,
        // registramos para verificações de Gate específicas.
        Gate::policy(Orcamento::class, RouteBillingPolicy::class);
    }
}
