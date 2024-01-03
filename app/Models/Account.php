<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','account_no','has_sons_acoounts','statment_id',];

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tentant';

    /**
     * entries related to account.
     */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class)
        ->withPivot('debit_amount', 'credit_amount','description','currency_id','currency_rate','customfields','date',);
    }

    /**
    * Get all outcome_receipts for spesfic account .
     */
    public function JournalEntries(): MorphToMany
    {
        return $this->morphedByMany(JournalEntry::class,'transactions')
        ->withPivot('debit_amount', 'credit_amount','description');
    }
    /**
    * Get all outcome_receipts for spesfic account .
     */
    public function outcome_payment_receipts(): MorphToMany
    {
        return $this->morphedByMany(Outcome_payment_receipt::class,'transactions')
        ->withPivot('debit_amount', 'credit_amount','description');
    }

    /**
     * Get all Income_receipts for spesfic account .
     */
    public function income_payment_receipts(): MorphToMany
    {
        return $this->morphedByMany(Income_payment_receipt::class,'transactions')
        ->withPivot('debit_amount', 'credit_amount','description');
    }

 

}
