<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use App\Actions\Finance\FinancialStatment;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

use App\Models\Invoice;
use App\Models\EntryLines;
use App\Models\Account;
// use App\Actions\Inventory;

use App\Actions\ImportExcelFile;

use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Financial_Statment_Controller;
use App\Http\Controllers\DocumentController;




use App\Http\Controllers\AccountsController;
use App\Http\Controllers\ProductController;
use App\Actions\TreeAccounts;

use App\Http\Controllers\IncomePaymentReceiptController;
use App\Models\Document_catagory;
use App\Models\Document;

use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;

use App\Actions\DatabaseManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\SearchController;
use App\Models\Entry;
use App\Models\Product;
use League\CommonMark\Extension\InlinesOnly\ChildRenderer;
use PhpParser\Node\Stmt\Foreach_;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::get('/testaccount', function () {
    // $a=Account::find(1);
    // return Account::balances(1,'01/01/2024',today());
    $BalanceSheet= Account::balancesOf([1,15],'01/01/2024',today());
   // $BalanceSheet = app(FinancialStatment::class)->BalanceSheet('01/01/2024',today());
   return $BalanceSheet ;

 });

 Route::get('/string', function(Request $request) {
    dd(url()->previous());
    
  return    str_split('entry_lines.1.account.id')[12];

 })->middleware('CurrentDatabase');


 Route::get('/testcache', function () {
    Cache::store('tentant')->put('start period', '1/1/2024');
    return    Cache::store('tentant')->get('start period');
})->middleware('CurrentDatabase');




Route::get('/invoice', function () {
    
    return Inertia::render('invoice', []);
})->middleware('auth');

Route::get('/get_document/{entry_id}',[DocumentController::class, 'Get_Document_By_Entry'])->name('document.show_by_entry');

Route::controller(JournalEntryController::class)->group(function () {
   // Route::get('/testentry/documents/{document:number}', 'testt')->name('entry.show');
    Route::get('/next_entry/{document_catagory:name}/documents/{document:number}', 'next')->name('entry.next');
    Route::get('/pervious_entry/{document_catagory:name}/documents/{document:number}', 'pervious')->name('entry.pervious');

    Route::get('/entry/{document_catagory:name}/documents/{document:number}', 'show')->name('entry.show');
    Route::put('/entry/{document_catagory:name}/documents/{document:number}', 'update')->name('entry.update');
    Route::delete('/entry/{document_catagory:name}/documents/{document:number}', 'destroy')->name('entry.delete');
    Route::get('/entry/document_catagories/{document_catagory:name}/documents', 'index')->name('entries.index');
    Route::get('/create_entry/{document_catagory:name}/documents', 'create')->name('entry.create');
    Route::post('/entry/document_catagories/{document_catagory}', 'store')->name('entry.store');
});

Route::controller(PurchaseController::class)->group(function () {
    Route::get('/purchase/{document_catagory:name}/documents/{document:number}', 'show')->name('purchase.show');
    Route::get('/next_purchase/{document_catagory:name}/documents/{document:number}', 'next')->name('purchase.next');
    Route::get('/pervious_purchase/{document_catagory:name}/documents/{document:number}', 'pervious')->name('purchase.pervious');
    Route::put('/purchase/{document_catagory:name}/documents/{document:number}', 'update')->name('purchase.update');
    Route::delete('/purchase/{document_catagory:name}/documents/{document:number}', 'destroy')->name('purchase.delete');
    Route::get('/purchase/document_catagories/{document_catagory:name}/documents', 'index')->name('purchase.index');
    Route::get('/create_purchase/{document_catagory:name}/documents', 'create')->name('purchase.create');
    Route::post('/purchase/document_catagories/{document_catagory}', 'store')->name('purchase.store');
});

Route::controller(SaleController::class)->group(function () {
    Route::get('/sale/{document_catagory:name}/documents/{document:number}', 'show')->name('sale.show');
    Route::get('/next_sale/{document_catagory:name}/documents/{document:number}', 'next')->name('sale.next');
    Route::get('/pervious_sale/{document_catagory:name}/documents/{document:number}', 'pervious')->name('sale.pervious');
    Route::put('/sale/{document_catagory:name}/documents/{document:number}', 'update')->name('sale.update');
    Route::delete('/sale/{document_catagory:name}/documents/{document:number}', 'destroy')->name('sale.delete');
    Route::get('/sale/document_catagories/{document_catagory:name}/documents', 'index')->name('sale.index');
    Route::get('/create_sale/{document_catagory:name}/documents', 'create')->name('sale.create');
    Route::post('/sale/document_catagories/{document_catagory}', 'store')->name('sale.store');
});

Route::controller(AccountsController::class)->group(function () {
    //Route::get('/account/ledgerBookForm/{account?}','ledgerBook_form')->name('accounts.ledgerBookForm');
    Route::post('/account/ledgerBook','ledgerBook')->name('accounts.ledgerBook');
    Route::post('/account/TrialBalance','TrialBalance')->name('accounts.TrialBalance');
    Route::get('/accounts','index')->name('accounts.index');
    Route::post('/accounts','store')->name('accounts.store');
    Route::put('/accounts','update')->name('accounts.update');
    Route::delete('/accounts/{account}/', 'destroy')->name('accounts.destroy');
});
// Financial_Statment_Controller
Route::controller(Financial_Statment_Controller::class)->group(function () {
    Route::post('/FinancialStatment/TradeStatment','TradeStatment')->name('TradeStatment');
    Route::post('/FinancialStatment/IncomeStatment','IncomeStatment')->name('IncomeStatment');
    Route::post('/FinancialStatment/BalanceSheet','BalanceSheet')->name('BalanceSheet');


});




Route::post('/products/ledgerBook', [ProductController::class, 'ledgerBook'])->name('products.ledgerBook');
Route::post('/products/InventoryValuation', [ProductController::class, 'InventoryValuation'])->name('InventoryValuation');


Route::resource('products', ProductController::class);

Route::get('/cleanjson', function () {

    Invoice::where('id','>',0)->update(['customfields'=>'{}']);
    EntryLines::where('id','>',0)->update(['customfields'=>'{}']);

   return "ok" ;
  
})->middleware('CurrentDatabase');


Route::get('/balance', function () {
   Account::where('id','>',0)->update(['balance'=>null]);
    return  Account::all();
 })->middleware('CurrentDatabase');


Route::get('/testexcel', function () {
    $pathToCsv =  storage_path('app/public/test.xlsx');
    // $pathToCsv =  storage_path('app/public/webdictionary.txt');
    // return readfile($pathToCsv);
 
    $rows = SimpleExcelReader::create($pathToCsv)->getRows();
   // return   $rows;

    $rows->each(function(array $rowProperties) {
         dd($rowProperties['name'])  ;
        // in the first pass $rowProperties will contain
        // ['email' => 'john@example.com', 'first_name' => 'john']
    });
    return   $rows;
});


Route::get('/writeexcel', function () {
    $pathToCsv =  storage_path('app/public/test3.xlsx');
    //$writer = SimpleExcelWriter::create($pathToCsv)
    $writer = SimpleExcelWriter::streamDownload('myexport.xlsx')
     ->addRow([
        'first_name' => 'John',
        'last_name' => 'Doe',
        //'info'=>['age'=>38 ,'gender'=> 'male' ],
    ])
    ->addRow([
        'first_name' => 'robi',
        'last_name' => 'Doe',
       // 'info'=>['age'=>28 ,'gender'=> 'female' ],
    ])->toBrowser();

   
});

Route::controller(SearchController::class)->group(function () {
    Route::post('/search/account', 'search_account');
    Route::post('/search/product', 'search_product');
    Route::post('/search/cost_center', 'search_cost_center');
    Route::post('/search/CustomeField', 'search_custom_fieled');

});




Route::resource('IncomePaymentReceipts', IncomePaymentReceiptController::class)->middleware('auth');


Route::get('/assignRole', function () {
    $user=Auth::user();
   $user->assignRole('admin');

    //$user->currentTeam();
    return  [$user,$user->getAllPermissions()  ]  ;
});

Route::get('/migrate_force', function (Request $request) {
    //Artisan::call('migrate --database=tentant --path=/database/migrations/2023_11_06_090053_create_custom_fields_table.php');
    return "ok";
})->middleware('CurrentDatabase');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
