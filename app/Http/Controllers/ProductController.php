<?php

namespace App\Http\Controllers;

use App\Models\Document_catagory;
use App\Models\Document;
use App\Models\Product;

use App\Models\Currency;
use App\Models\Account;

use App\Actions\AccountingEnrty;


use Illuminate\Support\Facades\Cache;
use App\Models\EntryLines ;
use App\Models\Invoice ;

use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Models\Entry;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Fluent ;
use App\Actions\DatabaseManager;
use App\Actions\Inventory\Inventory;
use App\Models\CustomField;
use Dflydev\DotAccessData\Data;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->middleware('CurrentDatabase');

    }

    
    
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    /**
     * return Ending Inventory Valuation
     */
    public function InventoryValuation(Request $request)
    {
        $validator = Validator::make($request->all(),['winbox_id'=>'required',]);
        if ($validator->fails()){
            return back()->withErrors($validator)->withInput();
        } 

        $inventory= app(Inventory::class);
        $data= $inventory->validate( $request->all());
        if (  $inventory->validation_is_failed) {  
            return back()->withErrors($inventory->validator)->withInput();
        }
        // Product of  Ending inventory 
        $products = $inventory->Valuate($data);
        return back()->with('inventory_Valuation.'.$request['winbox_id'],$products);


    }

    public function ledgerBook( Request $request)
    {
        $validator = Validator::make($request->all() ,[
            'product'=>['nullable','array'],
            'product.id'=>['required_with:product','numeric'],
            'StartDate'=>['required','date' ],
            'EndDate'=>['required','date'],
            'winbox_id'=>'required',
            ],$masge=[

            ],
        );
        if ($validator->fails()) {
             return back()->withErrors($validator)->withInput();
        } 
        $data = $validator->validated();

        $product_invoices =Invoice::selectRaw('
            SUM(IF(invoiceable_type="purchase", quantity*1,quantity*-1 ))  as in_stock , products.name, product_id ') 
           ->join('products', 'invoices.product_id', '=', 'products.id')
           ->where(function(Builder $query ) use($data){
                if (array_key_exists("product",$data)) {
                    $query->where('product_id',$data['product']['id']);
                }
           })
           ->whereBetween('date', [ $data['StartDate'] , $data['EndDate']])
            ->groupBy('product_id','products.name')
            ->get();

        return back()->with('inventory_ledger.'.$data['winbox_id'],$product_invoices);
                           
    }



}
