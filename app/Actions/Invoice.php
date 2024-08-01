<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\Document_catagory;
use App\Models\Entry;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Account ;
use App\Models\EntryLines ;
use App\Models\Invoice as Invoice_line;
use App\Models\Sale;
use App\Actions\Inventory\Inventory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent ;
use Illuminate\Validation\Rule;
use App\Rules\CompositeUnique;
use Closure;

class Invoice 
{
    public function __construct( public  $invoice_type , public $document_catagory_id)
    {

    }
    public $document;
    public $lines;
    public $validation_is_failed;
    public $validator ;
    public $Errors;
    public $entry ;

    /**
     * Create a new invoice for transaction.
     *   @param  Document  $document
     *   @param  array  $input
     */
    public function create( array $input )
    {
        $document = Document::create([ 
            'number'=> $input['document_number'] ,
            'document_catagory_id' => $this->document_catagory_id ,
             // 'entry_id'=> $entry->id,
            'date'=>$input['date'],
        ]);
        $catagory_name = Document_catagory::find($this->document_catagory_id )->name ;
        $description = $catagory_name.' number'. $input['document_number'] ;

        switch ($this->invoice_type) {
            case 'purchase':
                $invoice = Purchase::create(['document_id'=>$document->id]);
            break;
            case 'sale':
                $invoice = Sale::create(['document_id'=>$document->id]);
            break;     
        }
        $data =[];
        $tota_ammount=0;

        $lines = $input['lines'];
        foreach ($lines as $index=> $line) {
            $tota_ammount+=$line['ammount'] ;
            $data[$index] =[
                'invoiceable_id'=>$invoice->id,
                'invoiceable_type' => $this->invoice_type,
                'product_id'=> $line['product']['id'],
                'quantity'=>$line['quantity'],
                'price'=>$line['price'],
                'ammount'=>$line['ammount'],
                'description'=> $line['description'],
                'currency_id'=>$line['currency']['id']??null,
                'currency_rate'=> $line['currency_rate'],
                'date'=>$input['date'],
                'cost_center_id'=> $line['cost_center']['id']?? null, 
                'customfields' => json_encode($line['customfields']),
            ];
        }

        Invoice_line::upsert($data,['invoiceable_id']); 
        $cash_account =Account::find(12);
        $Credit_Or_Debit_Account_id  = ($input['Client_Or_Vendor_Account'])? $input['Client_Or_Vendor_Account']['id']: $cash_account->id ;
        $entry = Entry::create([]);
        if ($this->invoice_type=='sale') {
            $entry_lines = [
                ['entry_id'=> $entry->id,'account_id'=> 22,'credit_amount'=> $tota_ammount,'debit_amount'=>null,'date'=> $input['date'] ,'description'=> $description,         ],
                ['entry_id'=> $entry->id,'account_id'=> $Credit_Or_Debit_Account_id,'credit_amount'=>null,'debit_amount'=> $tota_ammount ,'date'=> $input['date'],'description'=> $description,  ],
            ];
        }
        if ($this->invoice_type=='purchase') {
            $entry_lines = [
                ['entry_id'=> $entry->id,'account_id'=> 18 ,'credit_amount'=>null,'debit_amount'=> $tota_ammount, 'date'=> $input['date'],'description'=> $description,   ],
                ['entry_id'=> $entry->id,'account_id'=> $Credit_Or_Debit_Account_id, 'credit_amount'=>$tota_ammount,'debit_amount'=> null, 'date'=> $input['date'] ,'description'=> $description,  ],
            ];
        }
        // dd($entry_lines);
        EntryLines::upsert($entry_lines,['entry_id'],['account_id','credit_amount','debit_amount']);
        $document->entry_id = $entry?->id ;
        $document->save();
        $this->document  = $document;

        return $invoice ;
    }
    
    public function validate(array $input)
    {
        $validator = Validator::make($input ,
        [
            'document_number' => ['bail','required', 'numeric','gt:0',
                new CompositeUnique,
            ],
            'default_account'=>'required',
            'PaymentMethod'=>'required',
            'Client_Or_Vendor_Account'=>'required_if:PaymentMethod,credit',
            'date' => ['required', 'date'],
            'document_catagory_id' => ['required', 'numeric','exists:tentant.document_catagories,id'],
            'lines.*.product' => 'nullable|array|required_with:lines.*.price,lines.*.quantity' ,
            'lines.*.quantity' => ['nullable','numeric','required_with:lines.*.price,lines.*.product','gt:0',
            //function(string $attribute, mixed $valu, Closure $fail,array $data){}
            ],
           // 'lines.*.quantity' => 'nullable|numeric|required_with:lines.*.price,lines.*.product|gt:0',
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

        $validator->after(function ($validator)  {
            $products = Product::all()->random(10);
            $product_count = app(Inventory::class)->CountProducts( $products,today() );
        });

        if ($validator->fails()) {
             $this->validation_is_failed=true;
             $this->validator=$validator;
              return back()->withErrors($validator)->withInput();
         }
        
        $validated_data = $validator->validated();
        return  $validated_data;
    
    }
   
    /**
     * updat entery lines.
     *
     * @param  Document  $document
     *  @param  array  $input 
     */
    public function UpdatLines(Document $document ,array $input )
    {
        $invoice_type=$this->invoice_type ;
        $invoice= $document->$invoice_type ;
        $data =[];
        $tota_ammount=0;

        $lines = $input['lines'];
        foreach ($lines as $index=> $line) {
            $tota_ammount+=$line['ammount'];
            $data[$index] =[
                'invoiceable_id'=>$invoice->id,
                'invoiceable_type' => $this->invoice_type,
                'product_id'=> $line['product']['id'],
                'quantity'=>$line['quantity'],
                'price'=>$line['price'],
                'ammount'=>$line['ammount'],
                'description'=> $line['description'],
                'currency_id'=>$line['currency']['id']??null,
                'currency_rate'=> $line['currency_rate'],
                'date'=>$input['date'],
                'cost_center_id'=> $line['cost_center']['id']?? null, 
                'customfields' => json_encode($line['customfields']),
            ];
        }
       //dd($data);
        $document->date = $input['date']    ; $document->save();
        $invoice->date = $input['date']     ; $invoice->save();

        $invoice->products()->detach();
        DB::table('invoices')->insert($data);
       // Invoice_line::upsert($data,['invoiceable_id',],
        //['product_id','quantity','price','ammount','description','currency_id','currency_rate',
        //'cost_center_id','customfields','date']);
      
    }
    
   
}
