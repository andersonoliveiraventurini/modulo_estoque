<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        //
    }
}
