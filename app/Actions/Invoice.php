<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\Entry;
use App\Models\Purchase;
use App\Models\Invoice as Invoice_line;
use App\Models\Sale;
use App\Models\EntrLines;

use App\Actions\AccountingEnrty;
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


    public function __construct( public  $invoice_type){


    }

    public $document_number;
    public $lines;
    public $validation_is_failed;
    public $validator ;
    public $Errors;
    public $document_catagory_id ;

    



    /**
     * Create a new entry for transaction.
     *
     * @param  array  $input
     */
    public function create(array $input )
    {
        $entry = Entry::create([]);
        $purchase=Purchase::create([ ]);
      
         $data1 =[];
         $tota_ammount=0;
         $data2=[];
         $lines = $input['lines'];
         //dd( $lines);
        foreach ($lines as $index=> $line) {
            $tota_ammount+=$line['ammount'];
            $data1[$index] =[
                'invoiceable_id'=>$purchase->id,
                'invoiceable_type' => 'App\Models\Purchase',
                'product_id'=> $line['product']['id'],
                'quantity'=>$line['quantity'],
                'price'=>$line['price'],
                'ammount'=>$line['ammount'],
                'description'=> $line['description'],
                'currencey_id'=>$line['currencey']['id']??null,
                'currency_rate'=> $line['currency_rate'],
                'cost_center_id'=> $line['cost_center']['id']?? null, 
                'customfields' => json_encode($line['customfields']),
            ];
            $data2[$index] =[
                'account_id'=>$input['default_account']['id'],
                'debit_amount'=>($this->invoice_type=='purchse')? $line['ammount']:null,
                'credit_amount'=>($this->invoice_type=='sale')? $line['ammount']:null,
                'quantity'=>$line['quantity'],
                'description'=> $line['product']['name']. $line['description'],
                'currencey_id'=>$line['currencey']['id']??null,
                'currency_rate'=> $line['currency_rate'],
                'cost_center_id'=> $line['cost_center']['id']?? null,  
            ];
        }

        $data2[count($lines)] =[
            //cache
            'account_id'=>$input['default_account']['id'],
            'debit_amount'=>($this->invoice_type=='sale')? $tota_ammount:null,
            'credit_amount'=>($this->invoice_type=='purchse')? $tota_ammount:null,
            'description'=> $line['product']['name']. $line['description'],
            'currencey_id'=>$line['currencey']['id']??null,
            'currency_rate'=> $line['currency_rate'],
            'cost_center_id'=> $line['cost_center']['id']?? null,
        ];
        dd($data2 );
        dd( $data1 );


         

         $data= $data->map(function  (array $line ) use( $purchase,$input)  {
            $line['invoiceable_id'] = $purchase->id;
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
        
        $data1 = $data->toArray();
        Invoice_line::upsert($data1,['purchase_id']); 

        $AccountingEnrty= app(AccountingEnrty::class);
        //$accounting_entry_data=
      
       
        return [$purchase,$purchase->products ];
    }
    
    public function validate(array $input)
    {
        $validator = Validator::make($input ,
        [
            'document_number' => ['bail','required', 'numeric','gt:0'],
            'default_account'=>'required',
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
