<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\Product;
use App\Models\Account ;
use App\Models\Invoice as Invoice_line;
use App\Actions\Inventory\Inventory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\CompositeUnique;
use App\Actions\AccountingEnrty;
use Illuminate\Support\Facades\DB;

class Invoice 
{
    public function __construct( public  $invoice , public $document)
    {

    }

    public $validation_is_failed = true;
    public $validator ;
    public $entry ;
    public $total_ammount ;
    public $Credit_Or_Debit_Account ;
    public $date ;
    public $description ;
    public $Invoice_Currency ;
    public $invoice_type ;

    /**
     * Create a new invoice for transaction.
     *   @param  array  $input
     */
    public function create( array $input )
    {   
        $validated_input =($this->validation_is_failed)? $this->validate($input):$input;
        $this->invoice_type=$this->document->document_catagory->type;
        
        $data =[];
        $total_ammount=0;
        $lines = $validated_input['lines'];
        foreach ($lines as $index=> $line) {
            $total_ammount += $line['ammount'] ;
            $data[$index] =[
                'invoiceable_id'=>$this->invoice->id,
                'invoiceable_type' => $this->document->document_catagory->type,
                'product_id'=> $line['product']['id'],
                'quantity'=>$line['quantity'],
                'price'=>$line['price'],
                'ammount'=>$line['ammount'],
                'description'=> $line['description'],
                'currency_id'=>$line['currency']['id']??null,
                'currency_rate'=> $line['currency_rate'],
                'date'=>$validated_input['date'],
                'cost_center_id'=> $line['cost_center']['id']?? null, 
                'customfields' => json_encode($line['customfields']),
            ];
        }
        Invoice_line::upsert($data,['invoiceable_id']);
        // create accounting entry 
        $this->Credit_Or_Debit_Account = $validated_input['Client_Or_Vendor_Account'] ;
        $this->total_ammount = $total_ammount ;
        $this->description = $this->document->document_catagory->name.' number'. $validated_input['document_number'] ;
        $this->Invoice_Currency= $validated_input['Invoice_Currency']  ;
        $entry = $this->CreateEntry();
        $this->document->entry_id = $entry?->id ;
        $this->document->save();
        
    }
    /**
     * validate invoice input.
     *
     */
    public function validate(array $input)
    {
        $validator = Validator::make($input ,
        [
            'document_number' => ['bail','required', 'numeric','gt:0',
                new CompositeUnique,
            ],
            'default_account'=>'required',
            'PaymentMethod'=>'required',
            'Invoice_Currency' => 'required' ,
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
 
        $validator->validate();
        $validated_data = $validator->validated();
        $this->validation_is_failed=false;
        return  $validated_data;
    }
    /**
     *Setup_Entry_Lines
     *  @param    $entry 
     */
    public function Setup_Entry_Lines ($entry=null)
    {
        $cash_account =Account::find(12);
        $Purchases_account =Account::find(18);
        $sales_account =Account::find(22);
        $Credit_Or_Debit_Account =  ($this->Credit_Or_Debit_Account)? $this->Credit_Or_Debit_Account: $cash_account  ;
        $total_ammount =$this->total_ammount;
        $currency_id = $this->Invoice_Currency['id'];
        $currency_rate = $this->Invoice_Currency['rate'];

        if ($this->invoice_type=='sale') {
            $entry_lines = [
                ['account'=> $sales_account,'credit_amount'=> $total_ammount,'debit_amount'=>null,'description'=> $this->description, 'currency_id'=>$currency_id,'currency_rate'=> $currency_rate ],
                ['account'=> $Credit_Or_Debit_Account,'credit_amount'=>null,'debit_amount'=> $total_ammount ,'description'=> $this->description, 'currency_id'=>$currency_id ,'currency_rate'=>$currency_rate ],
            ];
        }
        if ($this->invoice_type=='purchase') {
            $entry_lines = [
                ['account'=> $Purchases_account ,'credit_amount'=>null,'debit_amount'=>$total_ammount, 'description'=> $this->description, 'currency_id'=>$currency_id,'currency_rate'=>$currency_rate  ],
                ['account'=> $Credit_Or_Debit_Account, 'credit_amount'=> $total_ammount,'debit_amount'=> null, 'description'=> $this->description, 'currency_id'=>$currency_id ,'currency_rate'=>$currency_rate ],
            ];
        }
        if ($entry) {
            foreach ($entry_lines as $line) {
                $line['entry_id'] = $entry->id;
                unset($line['description'] );
            }
        }
        return  $entry_lines ;
    }
     /**
     * create entery for invoice .
     */
    public function CreateEntry()
    {
        $data=[];
        $data['entry_lines']=$this->Setup_Entry_Lines();
        // dd($data['entry_lines']);
        $data['date']=$this->date;
        $entry = app(AccountingEnrty::class)->create($data);
        return  $entry ;
    }

    /**
     * update entery for invoice .
     **  @param    $entry 
     */
    public function UpdateEntry($entry)
    {
        $data=[];
        $data['entry_lines']=$this->Setup_Entry_Lines($entry);
        $data['date']=$this->date;
        $entry = app(AccountingEnrty::class)->UpdatLines($entry,$data);
        return  $entry ;
    }

    /**
     * updat invoice lines.
     *
     * @param  Document  $document
     *  @param  array  $input 
     */
    public function UpdatLines(array $input )
    {
        $validated_input = $this->validate($input);
        $this->invoice_type=$this->document->document_catagory->type;

        $data =[];
        $total_ammount=0;
        $lines = $validated_input['lines'];
        foreach ($lines as $index=> $line) {
            $total_ammount+=$line['ammount'];
            $data[$index] =[
                'invoiceable_id'=>$this->invoice->id,
                'invoiceable_type' =>  $this->invoice_type ,
                'product_id'=> $line['product']['id'],
                'quantity'=>$line['quantity'],
                'price'=>$line['price'],
                'ammount'=>$line['ammount'],
                'description'=> $line['description'],
                'currency_id'=>$line['currency']['id']??null,
                'currency_rate'=> $line['currency_rate'],
                'date'=>$validated_input['date'],
                'cost_center_id'=> $line['cost_center']['id']?? null, 
                'customfields' => json_encode($line['customfields']),
            ];
        }
        $this->document->date = $validated_input['date'];
        $this->document->save();
        $this->invoice->date = $validated_input['date'] ;
        $this->invoice->save();

        $this->invoice->products()->detach();
        DB::table('invoices')->insert($data);

        $this->Credit_Or_Debit_Account = $validated_input['Client_Or_Vendor_Account'] ;
        $this->Invoice_Currency= $validated_input['Invoice_Currency']  ;
        $this->total_ammount = $total_ammount ;
        $entry = $this->document->entry ;
        $this->description = $entry->accounts->first()->pivot->description ;
        $this->UpdateEntry($entry);
        
    }
    
   
}
