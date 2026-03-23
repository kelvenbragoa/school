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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnUpdate();
            $table->enum('movement_type', ['entrada', 'saida', 'ajuste']);
            $table->enum('movement_reason', ['compra', 'devolucao', 'pedido', 'consumo_interno', 'inventario', 'outro']);
            $table->decimal('quantity', 10, 2);
            $table->decimal('previous_stock', 10, 2);
            $table->decimal('new_stock', 10, 2);
            $table->foreignId('request_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('supplier')->nullable();
            $table->string('destination')->nullable();
            $table->string('location', 100)->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate();
            $table->text('notes')->nullable();
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamps();

            $table->index(['item_id', 'movement_date']);
            $table->index('movement_type');
            $table->index('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
