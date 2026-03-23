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
        Schema::create('request_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->cascadeOnDelete();
            $table->enum('from_status', ['solicitado', 'preparando', 'enviando', 'recebido', 'entregue', 'cancelado'])->nullable();
            $table->enum('to_status', ['solicitado', 'preparando', 'enviando', 'recebido', 'entregue', 'cancelado']);
            $table->foreignId('changed_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamp('changed_at')->useCurrent();

            $table->index('request_id');
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_status_history');
    }
};
