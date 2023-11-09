<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Entry extends Model
{
    use HasFactory;
    
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tentant';

    /**
     * accounts belong to entry.
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)
        ->withPivot('id','debit_amount', 'credit_amount','description','currency_id','currency_rate','customfields');
    }
     /**
     * get cost center related entry to 
     */
    public function cost_centers(): BelongsToMany
    {
        return $this->belongsToMany(CostCenter::class,'account_entry','entry_id','cost_center_id')
        ->withPivot('debit_amount', 'credit_amount','description','account_id','currency_id','currency_rate');
    }
    

    /**
     * Get the orgin document of entry
     */
    public function document(): HasOne
    {
        return $this->hasOne(Document::class);
    }


}
