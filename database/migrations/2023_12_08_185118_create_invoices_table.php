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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->morphs('invoiceable');
            $table->decimal('quantity');
            $table->decimal('price');
            $table->decimal('ammount');
            $table->text('description')->nullable();
            $table->integer('currency_id')->nullable();
            $table->decimal('currency_rate')->nullable();
            $table->integer('cost_center_id')->nullable();
            $table->json('customfields')->nullable();
            $table->date('date');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
