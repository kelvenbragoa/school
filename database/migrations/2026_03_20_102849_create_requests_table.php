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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 20)->unique();
            $table->foreignId('item_id')->constrained()->cascadeOnUpdate();
            $table->string('reference', 100);
            $table->string('work_order', 100);
            $table->decimal('quantity', 10, 2);
            $table->string('equipment_id', 100);
            $table->enum('replacement_reason', ['Desgaste', 'Erro de Fabrico', 'Outro']);
            $table->enum('status', ['solicitado', 'preparando', 'enviando', 'recebido', 'entregue', 'cancelado'])->default('solicitado');
            $table->foreignId('requester_id')->constrained('users')->cascadeOnUpdate();
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'requester_id']);
            $table->index('request_number');
            $table->index('work_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
