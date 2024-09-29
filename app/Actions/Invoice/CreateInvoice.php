<?php

namespace App\Actions\Invoice;


use App\Models\Account ;
use App\Models\Invoice as Invoice_line;
use Illuminate\Support\Facades\DB;
use App\Actions\AccountingEnrty;

class CreateInvoice 
{
    public $entry ;
    public $total_ammount ;
    public $Credit_Or_Debit_Account ;
    public $date ;
    public $description ;

    public function __construct( public  $invoice , public $document)
    {

    }
    /**
     * Create a new invoice for transaction.
     *   @param  array  $input
     */
    public function create( array $input )
    {
        $data =[];
        $total_ammount=0;

        $lines = $input['lines'];
        foreach ($lines as $index=> $line) {
            $total_ammount +=$line['ammount'] ;
            $data[$index] =[
                'invoiceable_id'=>$this->invoice->id,
                'invoiceable_type' => $this->document->document_catagory->type ,
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

        // create accounting entry 
        $this->Credit_Or_Debit_Account = $input['Client_Or_Vendor_Account'] ;
        $this->total_ammount = $total_ammount ;
        $this->date = $input['date'];
        $this->description = $this->document->document_catagory->name.' number'. $input['document_number'] ;
        $entry = $this->CreateEntry();
        $this->document->entry_id = $entry?->id ;
        $this->document->save();
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

        if ($this->document->document_catagory->type=='sale') {
            $entry_lines = [
                ['account'=> $sales_account,'credit_amount'=> $total_ammount,'debit_amount'=>null,'description'=> $this->description, 'date'=>$this->date   ],
                ['account'=> $Credit_Or_Debit_Account,'credit_amount'=>null,'debit_amount'=> $total_ammount ,'description'=> $this->description,'date'=>$this->date  ],
            ];
        }
        if ($this->document->document_catagory->type=='purchase') {
            $entry_lines = [
                ['account'=> $Purchases_account ,'credit_amount'=>null,'debit_amount'=>$total_ammount, 'description'=> $this->description,'date'=>$this->date  ],
                ['account'=> $Credit_Or_Debit_Account, 'credit_amount'=> $total_ammount,'debit_amount'=> null, 'description'=> $this->description, 'date'=>$this->date ],
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

   
    
   
}
