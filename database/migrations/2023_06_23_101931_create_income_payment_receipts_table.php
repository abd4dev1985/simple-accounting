<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Actions\DatabaseManager ;
use Illuminate\Http\Request;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       // $request =app(Request::class);
       // $currentDatabase= app(DatabaseManager::class)->currentDatabase($request);

        Schema::create('income_payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->float('amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_payment_receipts');
    }
};
