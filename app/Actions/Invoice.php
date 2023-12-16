<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\Entry;
use App\Models\Purchase;
use App\Models\Invoice as Invoice_line;

use App\Models\Sale;

use App\Models\EntrLines;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent ;
use Illuminate\Validation\Rule;

class Invoice 
{
    public $document_number;
    public $lines;
    public $validation_is_failed;
    public $validator ;
    public $Errors;
    public $document_catagory_id ;
    



    /**
     * Create a new entry for transaction.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input )
    {
        $entry = Entry::create([]);

         $data = collect($input['lines']);
         $purchase=Purchase::create([
           // 'document_catagory_id' => $document_catagory->id ,
            //'entry_id'=> $entry->id,
         ]);

         $data= $data->map(function  (array $line ) use( $purchase,$input)  {
            $line['invoiceable_id'] = $purchase->id;
            $line['invoiceable_type'] = 'purchase';

            $line['product_id'] = $line['product']['id'];
            $line['cost_center_id'] =  $line['cost_center']['id']?? null;
            $line['date'] = $input['date'];
            $line['invoiceable_type'] = 'App\Models\Purchase';

            $line['customfields'] =  json_encode($line['customfields']) ;
            unset($line['product']);
            //unset($line['currency_rate']);
            unset($line['currencey']);
            unset($line['cost_center']);
            return $line;
        });
        
        $data = $data->toArray();
        Invoice_line::upsert($data,['purchase_id']);
      
       
        return [$purchase,$purchase->products ];
    }
    
    public function validate(array $input)
    {
        $validator = Validator::make($input ,
        [
            'document_number' => ['bail','required', 'numeric','gt:0'],
            'date' => ['required', 'date'],
            'document_catagory_id' => ['required', 'numeric'],
            'lines.*.product' => 'nullable|array|required_with:lines.*.price,lines.*.quantity' ,
            'lines.*.quantity' => 'nullable|numeric|required_with:lines.*.price,lines.*.product|gt:0',
            'lines.*.price' => 'nullable|numeric|required_with:lines.*.quantity,lines.*.product|gt:0',
            'lines.*.cost_center' => 'nullable|array' ,
            'lines.*.currency_rate' => 'required' ,
            'lines.*' => Rule::forEach(function ( $line, string $attribute) {
                return [
                    Rule::excludeIf($line['product'] ==null && ( $line['quantity']==null && $line['price']==null))
                ];
            }),
        ],$masge=[

        ],
        );
        
        if ($validator->fails()) {
             $this->validation_is_failed=true;
             $this->validator=$validator;
              return back()->withErrors($validator)->withInput();
            // return $validator ;
         }
        
        $validated_data = $validator->validated();
        return  $validated_data;
    
    }
   
    /**
     * updat entery lines.
     *
     * @param  Entry  $entry
     *  @param  array  $entry
     */
    public function UpdatLines(Entry $entry ,array $input )
    {
         $data = collect($input['lines']);
         $data= $data->map(function  (array $line ) use( $entry,$input )  {
            $line['entry_id'] = $entry->id;
            $line['account_id'] = $line['account']['id'];
            $line['cost_center_id'] =  $line['cost_center']['id']?? null;
            $line['date'] = $input['date'];
            $line['customfields'] =  json_encode($line['customfields']) ;
            unset($line['account'] );
            unset($line['currency_rate'] );
            unset($line['currencey']);
            unset($line['cost_center']);
            return $line;
        });
       
        $data = $data->toArray();
        EntrLines::upsert($data,['id'],
        ['account_id','debit_amount','credit_amount','description','currency_id','currency_rate',
        'cost_center_id','customfields','date']);
        //dd("whate");
        $entry->refresh();
        return $entry ;
    }
    
   
}
