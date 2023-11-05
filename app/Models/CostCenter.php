<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CostCenter extends Model
{
    use HasFactory;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tentant';

    /**
     * entries related to cost center.
     */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class,'account_entry','cost_center_id','entry_id')
        ->withPivot('debit_amount', 'credit_amount','description','account_id','currency_id','currency_rate');
    }
    
}
