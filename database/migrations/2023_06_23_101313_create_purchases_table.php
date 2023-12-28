<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Actions\DatabaseManager ;
use Illuminate\Http\Request;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    // protected $connection = 'tentant';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
       // $request =app(Request::class);
       // $currentDatabase= app(DatabaseManager::class)->currentDatabase($request);
       
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->integer('document_id');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
