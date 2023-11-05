<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
//use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator;
class StoreIncome_payment_receiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'receipt_id' => ['required', 'numeric','gt:0'],
            'lines.*.debit_amount' => 'nullable|numeric|prohibits:lines.*.credit_amount|gt:0',
            'lines.*.credit_amount' => 'nullable|numeric|prohibits:lines.*.debit_amount|gt:0',
            'lines.*.account_id' => 'required_with:lines.*.credit_amount,lines.*.debit_amount',
        ];
    }

    /**
     * check every line dose not have present account without amount against it (debit or credit)
     * get total debit amount and total credit amount
     * check the balance of entry,make sure the total of debit amount equal the total of credit amount
     */
    public function after(): array
    {
        return [

            function (Validator $validator) {
                $lines = $this->lines ;
                $total_debit_amount =0 ;
                $total_credit_amount =0 ;

                foreach ($lines as $key=> $line) {
                    $total_debit_amount += $line['debit_amount'];
                    $total_credit_amount += $line['credit_amount'];

                    if ($line['account_id']!=null && ($line['debit_amount']==null && $line['credit_amount']==null) ) {
                        $validator->errors()->add(
                            'lines.'.$key.'.account_id', 'there is no debit or crdit amount against account in line '.$key+1
                        );
                    }
                }
                //check the balance of entry
                if ($total_credit_amount!=$total_debit_amount ) {
                    $validator->errors()->add(
                        'receipt_id', 'the entry not balanced  total credit '.$total_credit_amount. ' total debit '.$total_debit_amount
                    );
                }
            },
        ];
    }


}
