<?php

namespace App\Actions;

use App\Models\Account;
use App\Models\Entry;
use App\Models\EntryLines;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent ;
use Illuminate\Validation\Rule;
use App\Rules\Balanced;
use Closure;

class AccountingEnrty 
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
        //dd($input );
        $entry = Entry::create([]);

         $data = collect($input['entry_lines']);

         $data= $data->map(function  (array $line ) use( $entry,$input)  {
            $line['entry_id'] = $entry->id;
            $line['account_id'] = $line['account']['id'];
            $line['cost_center_id'] =  $line['cost_center']['id']?? null;
            $line['date'] = $input['date'];
            $line['customfields'] =  json_encode($line['customfields']) ;
            //$line['equivalent_credit_amount'] = ($line['credit_amount'])? $line['credit_amount*'] * $line['currency_rate']: null;
            unset($line['account']);
            //unset($line['currency_rate']);
            $line['currency_id'] = $line['currency']['id'];
            unset($line['currency']);
            unset($line['cost_center']);
            return $line;
        });
        
        $data = $data->toArray();
        EntryLines::upsert($data,['entry_id']);
        /*
         $data=$data->mapWithKeys(function(array $line,int  $key){
           return [ $line['account']['id']=>[
                'debit_amount'=> $line['debit_amount'],
                'credit_amount' => $line['credit_amount'],
                "discreption" => $line['discreption'],
                'cost_center_id'=> $line['cost_center']['id']?? null,
           ] ];
         });
        $entry->accounts()->sync($data);
        */
       
        return $entry ;
    }
    
    public function validate(array $input)
    {
        //dd($input) ;
        $validator = Validator::make($input ,
        [
            'document_number' => ['bail','required', 'numeric','gt:0'],
            'date' => ['required', 'date'],
            'document_catagory_id' => ['required', 'numeric'],
            'entry_lines.*.account' => 'nullable|array' ,
            'entry_lines.*.cost_center' => 'nullable|array' ,
            'entry_lines.*.currency_rate' => 'required' ,
            'entry_lines'=>[new Balanced] ,
           'entry_lines.*' => Rule::forEach(function ( $line, string $attribute) {
               return [
                    Rule::excludeIf($line['account'] ==null && ( $line['debit_amount']==null && $line['credit_amount']==null)) ,
                    'array',
             ];
           }),

            'entry_lines.*.account.id' => 'nullable|required_with:entry_lines.*.credit_amount,entry_lines.*.debit_amount',
           
            'entry_lines.*.account.id' => [
                'nullable',
                'required_with:entry_lines.*.credit_amount,entry_lines.*.debit_amount',
                function (string $attribute, mixed $value, Closure $fail){
                    $account = Account::find($value);
                    if ( $account->has_sons_accounts) {
                        $fail("The account of {$account->name} can not be used in entry because it has Sub accounts.");
                    }
                },
            ],

            'entry_lines.*.debit_amount' => 'nullable|numeric|prohibits:entry_lines.*.credit_amount|gt:0',
            'entry_lines.*.credit_amount' => 'nullable|numeric|prohibits:entry_lines.*.debit_amount|gt:0',
        ],
        $masge=[
            'entry_lines.*.account.id.required_with' => 'the account field in line '. intval(':index') .' is required',
        ],
        [
          //  'entry_lines.*.account.id' => str_split(':attribute')[12] ,
        ]
       
        );
        
        //(check the balance of entry)
        // get total debit amount and total credit amount and check they are equal
        $validator->after(function ($validator) use($input)  {
            $lines = $input['entry_lines'] ;
            $total_debit_amount =0 ;
            $total_credit_amount =0 ;

            foreach ($lines as $key=> $line) {
                $total_debit_amount += $line['debit_amount']*$line['currency_rate'];
                $total_credit_amount += $line['credit_amount']*$line['currency_rate'] ;
                // check every line dose not have present account without amount against it (debit or credit)
                if ($line['account'] !=null && ($line['debit_amount']==null && $line['credit_amount']==null) ) {
                    $validator->errors()->add(
                        'entry_lines.'.$key.'.account.id', 'there is no debit or crdit amount against account in line '.$key+1
                    );
                }  
            }
        });
    
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
         $data = collect($input['entry_lines']);
         //dd($input['entry_lines']);
         $data= $data->map(function  (array $line ) use( $entry,$input )  {
            $line['entry_id'] = $entry->id;
            $line['account_id'] = $line['account']['id'];
            $line['cost_center_id'] =  $line['cost_center']['id']?? null;
            $line['currency_id'] = $line['currency']['id']?? null;
            $line['date'] = $input['date'];
            $line['customfields'] =  json_encode($line['customfields']) ;
            unset($line['account'] );
            unset($line['currency']);
            unset($line['cost_center']);
            unset($line['id']);
            return $line;
        });
       
        $data = $data->toArray();
        //dd($data);
        $entry->accounts()->detach();
        DB::table('account_entry')->insert($data);

       // EntrLines::upsert($data,['id'],
        //['account_id','debit_amount','credit_amount','description','currency_id','currency_rate',
        //'cost_center_id','customfields','date']);
        $entry->refresh();
        return $entry ;
    }
    
   
}
