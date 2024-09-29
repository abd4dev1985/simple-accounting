<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\Document_catagory;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Account ;
use App\Models\Invoice as Invoice_line;
use App\Models\Sale;
use App\Actions\Inventory\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\CompositeUnique;
use App\Actions\AccountingEnrty;

class Invoice 
{
    public function __construct( public  $invoice_type , public $document_catagory_id)
    {

    }

    public $document;
    public $validation_is_failed;
    public $validator ;
    public $entry ;
    public $total_ammount ;
    public $Credit_Or_Debit_Account ;
    public $date ;
    public $description ;

    /**
     * Create a new invoice for transaction.
     *   @param  array  $input
     */
    public function create( array $input )
    {
        $document = Document::create([ 
            'number'=> $input['document_number'] ,
            'document_catagory_id' => $this->document_catagory_id ,
            'date'=>$input['date'],
        ]);
        $catagory_name = Document_catagory::find($this->document_catagory_id )->name ;

        switch ($this->invoice_type) {
            case 'purchase':
                $invoice = Purchase::create(['document_id'=>$document->id]);
            break;
            case 'sale':
                $invoice = Sale::create(['document_id'=>$document->id]);
            break;     
        }
        $data =[];
        $total_ammount=0;

        $lines = $input['lines'];
        foreach ($lines as $index=> $line) {
            $total_ammount +=$line['ammount'] ;
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
        $this->Credit_Or_Debit_Account = $input['Client_Or_Vendor_Account'] ;
        $this->total_ammount = $total_ammount ;
        $this->description = $catagory_name.' number'. $input['document_number'] ;

        Invoice_line::upsert($data,['invoiceable_id']);
        $entry = $this->CreateEntry();
        $document->entry_id = $entry?->id ;
        $document->save();
        $this->document  = $document;
        return $invoice ;
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

        if ($this->invoice_type=='sale') {
            $entry_lines = [
                ['account'=> $sales_account,'credit_amount'=> $total_ammount,'debit_amount'=>null,'description'=> $this->description,   ],
                ['account'=> $Credit_Or_Debit_Account,'credit_amount'=>null,'debit_amount'=> $total_ammount ,'description'=> $this->description,  ],
            ];
        }
        if ($this->invoice_type=='purchase') {
            $entry_lines = [
                ['account'=> $Purchases_account ,'credit_amount'=>null,'debit_amount'=>$total_ammount, 'description'=> $this->description,   ],
                ['account'=> $Credit_Or_Debit_Account, 'credit_amount'=> $total_ammount,'debit_amount'=> null, 'description'=> $this->description,  ],
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
    public function UpdatLines(Document $document ,array $input )
    {
        $invoice_type=$this->invoice_type ;
        $invoice= $document->$invoice_type ;
        $data =[];
        $total_ammount=0;

        $lines = $input['lines'];
        foreach ($lines as $index=> $line) {
            $total_ammount+=$line['ammount'];
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
        $document->date = $input['date']    ; $document->save();
        $invoice->date = $input['date']     ; $invoice->save();

        $invoice->products()->detach();
        DB::table('invoices')->insert($data);

        $this->Credit_Or_Debit_Account = $input['Client_Or_Vendor_Account'] ;
        $this->total_ammount = $total_ammount ;
        $entry = $document->entry ;
        $this->description = $entry->accounts->first()->pivot->description ;



        $this->UpdateEntry($entry);
        
       
    }
    
   
}
