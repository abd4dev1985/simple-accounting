<?php

namespace App\Actions;

use Illuminate\Http\Request;
use App\Models\Document_catagory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;


class DatabaseManager 
{
   
    /**
     * set currentDatabase database  for a user.
     *
     * @param  
     */
  
    public function currentDatabase(Request $request )
    {
      $db_name= $request->user()->currentTeam->name ;
      $db_name = Str::slug($db_name,'_');
      $user_name = "user-".$db_name;
      $password=null ;
      config([
        //tentant
        'database.connections.tentant.database'=>$db_name,
      ]);
      return $db_name ;
      
    }
    /**
     * migrate  tables database  .
     *
     * @param  
     */
    public function migrateDatabase( )
    {
      
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_08_13_184415_create_account_entry_table.php');
      Artisan::call('migrate --database=tentant --path=/database/migrations/2021_07_12_120916_create_accounts_table.php');
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_06_18_143401_create_products_table.php');
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_06_23_100740_create_sales_table.php' );
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_06_23_101313_create_purchases_table.php' );
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_08_13_130346_create_entries_table.php' );
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_08_13_131648_create_document_catagories_table.php' );
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_08_13_153304_create_documents_table.php');
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_08_29_102805_create_currencies_table.php');
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_09_25_200333_create_cost_centers_table.php');
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_09_29_230956_create_cache_table.php');
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_11_06_090053_create_custom_fields_table.php');
      Artisan::call('migrate --database=tentant --path=/database/migrations/2023_12_08_185118_create_invoices_table.php');
      Artisan::call('migrate --database=tentant --path=/database/migrations/2024_02_03_090517_create_product_catagories_table.php');




      Document_catagory::upsert([
        ['name'=>'purchase_invoice','type' => 'purchase_invoice'],
        [ 'name'=>'sale_invoice','type' => 'sale_invoice' ],
        [ 'name'=>'general_income_receipts','type' => 'income_receipts' ],
        [ 'name'=>'general_outcome_receipts','type' => 'outcome_receipts' ],
        [ 'name'=>'general_entry','type' => 'entry' ],
      ],['id'],['name','type']);

    

    }
   
}
