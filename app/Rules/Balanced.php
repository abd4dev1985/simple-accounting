<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Balanced implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $lines, Closure $fail): void
    {
        //dd($lines);
        $total_debit_amount =0 ;
        $total_credit_amount =0 ;

        foreach ($lines as $key=> $line) {
            $total_debit_amount += $line['debit_amount']*$line['currency_rate'];
            $total_credit_amount += $line['credit_amount']*$line['currency_rate'] ;
        }
        //check the balance
        if ($total_credit_amount!=$total_debit_amount ) {
            $fail('the entry not balanced  total credit'.$total_credit_amount. ' total debit '.$total_debit_amount);  
        }
        //check the entry line have debit or credit value
        if ($total_credit_amount==$total_debit_amount & $total_credit_amount==0 ) {
            $fail('validation.has_credit_debit_ammount')->translate();
        }


        







    }
}
