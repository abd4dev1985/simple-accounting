<?php

namespace App\Actions\Invoice;

use App\Models\Invoice as Invoice_line;
use App\Actions\AccountingEnrty;
use App\Actions\Invoice\HasAccountingEntry;

class CreateInvoice 
{
    use HasAccountingEntry ;
 
    public $total_ammount ;
    public $Credit_Or_Debit_Account ;
    public $date ;
    public $description ;
    public $Invoice_Currency ;
    public $invoice_type ;

    public function __construct( public  $invoice , public $document)
    {

    }
    /**
     * Create a new invoice for transaction.
     *   @param  array  $input
     */
    public function create( array $input )
    {
        $this->invoice_type = $this->document->document_catagory->type  ;
        $data =[];
        $total_ammount=0;

        $lines = $input['lines'];
        foreach ($lines as $index=> $line) {
            $total_ammount +=$line['ammount'] ;
            $data[$index] =[
                'invoiceable_id'=>$this->invoice->id,
                'invoiceable_type' => $this->invoice_type ,
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
        $this->Invoice_Currency= $input['Invoice_Currency']  ;

        $entry = $this->CreateEntry();
        $this->document->entry_id = $entry?->id ;
        $this->document->save();
    }
     /**
     * create entery for invoice .
     */
    public function CreateEntry()
    {
        $data=[];
        $data['entry_lines']=$this->Setup_Entry_Lines();
        $data['date']=$this->date;
        $data['document_number']=$this->document->number;
        $data['document_catagory_id']=$this->document->document_catagory->id;
        $entry = app(AccountingEnrty::class)->create($data);
        return  $entry ;
    }

   
}
