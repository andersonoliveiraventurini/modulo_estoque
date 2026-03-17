<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('pedidos');
            $table->foreignId('customer_id')->constrained('clientes');
            $table->enum('status', ['pending', 'sales_approved', 'stock_approved', 'refused', 'credited'])->default('pending');
            $table->foreignId('sales_supervisor_id')->nullable()->constrained('users');
            $table->timestamp('sales_approved_at')->nullable();
            $table->foreignId('stock_supervisor_id')->nullable()->constrained('users');
            $table->timestamp('stock_approved_at')->nullable();
            $table->text('refusal_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};
