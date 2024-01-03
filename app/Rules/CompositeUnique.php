<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use App\Models\Document;

class CompositeUnique implements DataAwareRule,  ValidationRule
{
    
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

        /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
 
        return $this;
    }
    
    
    
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * 
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $document= Document::where('number',$value)
        ->where('document_catagory_id',$this->data['document_catagory_id'])->get();
        if ( $document->count()>0 and $this->data['operation']=='create' ) {
            $fail('The :attribute already exist.'); 
        }
    }
}
