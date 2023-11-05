<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = ['entry_orgin_type','entry_orgin_id','user_id'];
     /**
     * Get related acoount for the entry.
     */
    public function accounts(): MorphToMany
    {
        return $this->morphToMany(Account::class, 'transactions')
        ->withPivot('debit_amount', 'credit_amount','discreption');
    }


}
