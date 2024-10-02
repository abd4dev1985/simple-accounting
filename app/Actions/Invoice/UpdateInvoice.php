<?php

namespace App\Actions\Invoice;

use App\Models\Document;
use App\Models\Account ;
use App\Actions\Inventory\Inventory;
use Illuminate\Support\Facades\DB;
use App\Actions\AccountingEnrty;
use App\Actions\Invoice\HasAccountingEntry;

class UpdateInvoice 
{
    use HasAccountingEntry ;

    public function __construct( public  $invoice , public $document)
    {

    }
    
    public $Invoice_Currency ;
    public $total_ammount ;
    public $Credit_Or_Debit_Account ;
    public $date ;
    public $description ;
    public $invoice_type ;

    /**
     * updat invoice lines.
     *
     * @param  Document  $document
     *  @param  array  $input 
     */
    public function Update(array $input )
    {
        $this->invoice_type = $this->document->document_catagory->type  ;
        $data =[];
        $total_ammount=0;

        $lines = $input['lines'];
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
                'date'=>$input['date'],
                'cost_center_id'=> $line['cost_center']['id']?? null, 
                'customfields' => json_encode($line['customfields']),
            ];
        }
        $this->document->date = $input['date'] ; 
        $this->document->save();
        $this->invoice->date = $input['date'] ;
       $this->invoice->save();

        $this->invoice->products()->detach();
        DB::table('invoices')->insert($data);

        $this->Credit_Or_Debit_Account = $input['Client_Or_Vendor_Account'] ;
        $this->total_ammount = $total_ammount ;
        $this->date = $input['date'];
        $entry = $this->document->entry ;
        $this->Invoice_Currency= $input['Invoice_Currency']  ;
        $this->description = $entry->accounts->first()->pivot->description ;

        $this->UpdateEntry($entry);
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
        $data['document_number']=$this->document->number;
        $data['document_catagory_id']=$this->document->document_catagory->id;
        $entry = app(AccountingEnrty::class)->UpdatLines($entry,$data);
        return  $entry ;
    }

    
   
}
