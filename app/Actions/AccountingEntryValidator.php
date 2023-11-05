<?php

namespace App\Actions;

use App\Models\Entry;
use App\Models\User;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent ;
use Illuminate\Validation\Rule;

class AccountingEntryValidator 
{
    public $receipt_id;
    public $lines;
    public $validation_is_failed;
    



    /**
     * Create a new entry for transaction.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input )
    {
         $this->validate( $input);
         
        dd([$this->receipt_id,$this->lines]);
       
        $entry = Entry::create([
           // 'entry_orgin_type'=> 'IncomePaymentReceipts',
           // 'entry_orgin_id'=>$this->receipt_id,
            //'user_id'=> $user->id,
        ]);

        $entry->accounts()->sync($this->lines);

        return [$entry->accounts];

       
    }
    
    public function validate(array $input)
    {
         return redirect('/dashboard');
        $validator = Validator::make($input, [
            'receipt_id' => ['required', 'numeric','gt:0'],
            'lines.*.account' => 'required' ,
            'lines.*' => Rule::forEach(function ( $line, string $attribute) {
                return [
                    Rule::excludeIf($line['account']['id'] ==null && ( $line['debit_amount']==null && $line['credit_amount']==null))
                ];
            }),
            'lines.*.debit_amount' => 'nullable|numeric|prohibits:lines.*.credit_amount|gt:0',
            'lines.*.credit_amount' => 'nullable|numeric|prohibits:lines.*.debit_amount|gt:0',
            'lines.*.account.id' => 'nullable|required_with:lines.*.credit_amount,lines.*.debit_amount',
        ]);
        
        //(check the balance of entry)
        // get total debit amount and total credit amount and check they are equal
        $validator->after(function ($validator) use($input)  {
            $lines = $input['lines'] ;
            $total_debit_amount =0 ;
            $total_credit_amount =0 ;

            foreach ($lines as $key=> $line) {
                $total_debit_amount += $line['debit_amount'];
                $total_credit_amount += $line['credit_amount'];
                // check every line dose not have present account without amount against it (debit or credit)
                if ($line['account']['id']!=null && ($line['debit_amount']==null && $line['credit_amount']==null) ) {
                    $validator->errors()->add(
                        'lines.'.$key.'.account.id', 'there is no debit or crdit amount against account in line '.$key+1
                    );
                }
            }
            //check the balance
            if ($total_credit_amount!=$total_debit_amount ) {
                $validator->errors()->add(
                    'entry_balance', 'the entry not balanced  total credit '.$total_credit_amount. ' total debit '.$total_debit_amount
                );
            }
        });
    
        if ($validator->fails()) {
          //  $this->validation_is_failed=true;
            return back()->withErrors($validator)->withInput();
         }
        
        
        $validated_data = $validator->validated();

    
         //get validated receipt id and lines
        $this->receipt_id=$validated_data['receipt_id'];

        $this->lines=collect($validated_data['lines']);
        //$this->lines =$this->lines->keyBy('account_id');
        return $this->lines ;
        
    
        
      
        
    }

    
   
}
