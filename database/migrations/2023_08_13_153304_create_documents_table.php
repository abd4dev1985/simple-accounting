<?php

use Illuminate\Http\Request;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Actions\DatabaseManager ;
use Illuminate\Database\Eloquent\Factories\HasFactory;


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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->foreignId('document_catagory_id');
            $table->foreignId('entry_id');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
