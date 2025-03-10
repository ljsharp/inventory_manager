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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('stock_in')->default(0);
            $table->integer('stock_out')->default(0);
            $table->foreignId('stock_transfer_id')->nullable()->constrained('stock_transfers')->cascadeOnDelete();
            $table->integer('previous_balance')->default(0);
            $table->integer('current_balance')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
