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
        Schema::create('account_entry', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id');
            $table->integer('entry_id');
            $table->decimal('debit_amount')->nullable();
            $table->decimal('credit_amount')->nullable();
            $table->text('description')->nullable();
            $table->integer('currency_id')->nullable();
            $table->decimal('currency_rate')->nullable();
            //$table->decimal('equivalent_debit_amount')->nullable();
            //$table->decimal('equivalent_credit_amount')->nullable();
            $table->integer('cost_center_id')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_entry');
    }
};
