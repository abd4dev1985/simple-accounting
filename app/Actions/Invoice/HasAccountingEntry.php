<?php

namespace App\Actions\Invoice;


use App\Models\Account ;


trait HasAccountingEntry 
{
    /**
     *Setup_Entry_Lines
     *  @param    $entry 
     */
    public function Setup_Entry_Lines ($entry=null)
    {
        $cash_account =Account::find(12)->toArray();
        $Purchases_account =Account::find(18)->toArray();
        $sales_account =Account::find(22)->toArray();
        $Credit_Or_Debit_Account =  ($this->Credit_Or_Debit_Account)? $this->Credit_Or_Debit_Account: $cash_account  ;
        $total_ammount =$this->total_ammount;
        $currency_rate = $this->Invoice_Currency['rate'];
        $currency_id = $this->Invoice_Currency['id'];

        if ($this->invoice_type=='sale') {
            $entry_lines = [
                [   'account'=>$sales_account,'credit_amount'=> $total_ammount,'debit_amount'=>null,'description'=>$this->description,
                    'date'=>$this->date ,'currency_rate'=>$currency_rate ,'currency_id'=>$currency_id
                ],

                [
                    'account'=> $Credit_Or_Debit_Account,'credit_amount'=>null,'debit_amount'=>$total_ammount,'description'=>$this->description,
                    'date'=>$this->date ,'currency_rate'=>$currency_rate,'currency_id'=>$currency_id
                ],
            ];
        }
        if ($this->invoice_type=='purchase') {
            $entry_lines = [
                [
                    'account'=> $Purchases_account ,'credit_amount'=>null,'debit_amount'=>$total_ammount,'description'=> $this->description,
                    'date'=>$this->date ,'currency_rate'=>$currency_rate,'currency_id'=>$currency_id
                ],
                [
                    'account'=> $Credit_Or_Debit_Account,'credit_amount'=> $total_ammount,'debit_amount'=>null,'description'=> $this->description,
                    'date'=>$this->date, 'currency_rate'=>$currency_rate ,'currency_id'=>$currency_id
                ],
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
   
}
