<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Income_payment_receipt extends Model
{
    use HasFactory;

    protected $fillable = ['amount'];
    /**
     * Get related acoount for the reciept.
     */
    public function accounts(): MorphToMany
    {
        return $this->morphToMany(Account::class, 'transactions')
        ->withPivot('debit_amount', 'credit_amount','discreption');
    }
}
