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
        Schema::create('customer_discount_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_discount_id')->constrained('descontos')->onDelete('cascade');
            $table->decimal('previous_value', 15, 2)->nullable();
            $table->decimal('new_value', 15, 2);
            $table->foreignId('changed_by')->constrained('users');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_discount_history');
    }
};
