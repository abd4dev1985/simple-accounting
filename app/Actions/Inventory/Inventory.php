<?php

namespace App\Actions\Inventory;

use App\Models\Document;
use App\Models\Purchase;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent ;
use Illuminate\Validation\Rule;
use App\Rules\CompositeUnique;
use Illuminate\Support\Facades\Cache;



class Inventory 
{


    //public function __construct( public  $products){
        //

   // }

    public $document_number;
    public $lines;
    public $validation_is_failed;
    public $validator ;
    public $Errors;
    public $document_catagory_id ;

    



    
    /**
     * Valuate ending inventory
     *
     */
    public function Valuate(array $input )
    {
        $products =Invoice::selectRaw('
            products.name,
            product_id,
            SUM(IF(invoiceable_type="purchase", quantity*1,quantity*-1 ))  as in_stock,
            SUM(IF(invoiceable_type="purchase", quantity*price,0))  as cost_of_goods_available_for_sale,
            SUM(IF(invoiceable_type="purchase", quantity*1,0 )) as units_available_for_sale
            ') 
            ->join('products', 'invoices.product_id', '=', 'products.id')
            ->where(function(Builder $query ) use($input){
                if (array_key_exists("product",$input)) {
                    $query->where('product_id',$input['product']['id']);
                }
        })
        ->whereBetween('date', [ $input['StartDate'] , $input['EndDate']])
        ->groupBy('product_id','products.name')
        ->get();

        $products  = $products->map(function ($product){
            $product->unit_cost = $product->cost_of_goods_available_for_sale/$product->units_available_for_sale;
            $product->ending_inventory_cost = $product->unit_cost * $product->in_stock ;
            return $product;
        });
       // dd( $products) ;   
        return $products ;
        //return back()->with('inventory_ledger.'.$input['winbox_id'],$product_invoices);

   
      
    }
    /**
     * validate input for inventory
     *
     */
    public function validate(array $input)
    {
        $validator = Validator::make($input ,[
            'product'=>['nullable','array'],
            'product.id'=>['required_with:product','numeric'],
            'StartDate'=>['required','date' ],
            'EndDate'=>['required','date'],
        ],$masge=[      ],
        );
        if ($validator->fails()) {
            $this->validation_is_failed=true;
            return back()->withErrors($validator)->withInput();
        }
        
        $validated_data = $validator->validated();
        return  $validated_data;
    }

    /**
     * count ending inventory all product or specific product
     *
     */
    public function Count(array $input )
    {
        $data = $input;
        $products =Invoice::selectRaw('
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
        return   $products ;
                         
    }

    /**
     * count ending inventory for group of products
     *
     */
    public function CountProducts( $products,$date )
    {
        //$array = collect($products)->map(fn($product)=>$product->id);
        $array = collect($products)->map(function($product){
            return $product->id ;
        });
        $counted_products =Invoice::selectRaw('
            SUM(IF(invoiceable_type="purchase", quantity*1,quantity*-1 ))  as in_stock , products.name, product_id ') 
           ->join('products', 'invoices.product_id', '=', 'products.id')
           ->where(function(Builder $query ) use($array,$date){
                $query->whereIn('product_id',$array);
           })
           ->whereBetween('date', [ 'StartDate'=>Cache::store('tentant')->get('StartPeriod') , $date])
            ->groupBy('product_id','products.name')
            ->get();
        return   $counted_products ;
                         
    }








    
   
}
