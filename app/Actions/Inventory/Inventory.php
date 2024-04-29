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








    
   
}
