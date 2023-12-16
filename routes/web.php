<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

use App\Models\Account;

use App\Actions\ImportExcelFile;

use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AccountsController;
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
use App\Models\EntrLines;

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




Route::get('/invoice', function () {
    
    return Inertia::render('invoice', []);
})->middleware('auth');

Route::controller(JournalEntryController::class)->group(function () {
    Route::get('/testentry/documents/{document:number}', 'testt')->name('entry.show');

    Route::get('/entry/{document_catagory:name}/documents/{document:number}', 'show')->name('entry.show');
    Route::put('/entry/{document_catagory:name}/documents/{document:number}', 'update')->name('entry.update');
    Route::delete('/entry/{document_catagory:name}/documents/{document:number}', 'destroy')->name('entry.delete');
    Route::get('/entry/document_catagories/{document_catagory:name}/documents', 'index')->name('entries.index');
    Route::get('/create_entry/{document_catagory:name}/documents', 'create')->name('entry.create');
    Route::post('/entry/document_catagories/{document_catagory}', 'store')->name('entry.store');
});

Route::controller(PurchaseController::class)->group(function () {
    Route::get('/purchase/{document_catagory:name}/documents/{document:number}', 'show')->name('purchase.show');
    Route::put('/purchase/{document_catagory:name}/documents/{document:number}', 'update')->name('purchase.update');
    Route::delete('/purchase/{document_catagory:name}/documents/{document:number}', 'destroy')->name('purchase.delete');
    Route::get('/purchase/document_catagories/{document_catagory:name}/documents', 'index')->name('purchase.index');
    Route::get('/create_purchase/{document_catagory:name}/documents', 'create')->name('purchase.create');
    Route::post('/purchase/document_catagories/{document_catagory}', 'store')->name('purchase.store');
});

Route::controller(AccountsController::class)->group(function () {
    Route::get('/account/ledgerBookForm/{account?}','ledgerBook_form')->name('accounts.ledgerBookForm');
    Route::post('/account/ledgerBook/{account}','ledgerBook')->name('accounts.ledgerBook.result');
    Route::delete('/account/{account}/', 'destroy')->name('account.delete');
});

Route::get('/testcache', function () {
    Cache::store('tentant')->put('last general_entry', 45);
   return Cache::store('tentant')->get('last general_entry');
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
});




Route::resource('IncomePaymentReceipts', IncomePaymentReceiptController::class)->middleware('auth');


Route::get('/assignRole', function () {
    $user=Auth::user();
   // $user->assignRole('subscriber');
    //$user->currentTeam();
    return  $user  ;
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
