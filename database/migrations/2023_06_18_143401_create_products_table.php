<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    // protected $connection = 'tentant';


   // protected $connection ;
    /*
    public function __construct()
    {
        $request= app(Request::class);
        $Connections= new DatabaseConnection;
        $this->connection=$Connections->currentDatabase($request);
    }
    */

    /**
     * Run the migrations.
     */
    public function up(): void
    {
       // $request =app(Request::class);
       // $currentDatabase= app(DatabaseManager::class)->currentDatabase($request);

        Schema ::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
    
};
