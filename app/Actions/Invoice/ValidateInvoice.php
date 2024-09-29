<?php

namespace App\Actions\Invoice;


use App\Models\Product;

use App\Actions\Inventory\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\CompositeUnique;

class ValidateInvoice 
{

    public $validation_is_failed;
    public $validator ;
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
    
    

    
   
}
