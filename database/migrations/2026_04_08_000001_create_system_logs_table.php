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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level')->index();
            $table->text('message');
            $table->json('context')->nullable();
            $table->json('extra')->nullable();
            $table->string('remote_addr')->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('url')->nullable();
            $table->string('method')->nullable();
            $table->string('exception_class')->nullable();
            $table->text('file')->nullable();
            $table->integer('line')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
