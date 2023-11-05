<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntrLines extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'account_entry';
    
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tentant';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id','entry_id','debit_amount',
        'credit_amount','description','currency_id',
        'currency_rate','cost_center_id',
    ];

    
}
