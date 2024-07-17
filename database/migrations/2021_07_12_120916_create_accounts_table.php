<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Actions\DatabaseManager ;
use Illuminate\Http\Request;


class CreateAccountsTable extends Migration
{

    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'tentant';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //$request =app(Request::class);
        //$currentDatabase= app(DatabaseManager::class)->currentDatabase($request);

        Schema::create('accounts', function (Blueprint $table) {
  	      $table->id();
          $table->string('name');
          $table->integer('number');
          $table->integer('father_account_id')->nullable();
          $table->boolean('has_sons_accounts')->nullable();
          $table->integer('statment_id');
          $table->float('current_balance')->nullable();
          $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
