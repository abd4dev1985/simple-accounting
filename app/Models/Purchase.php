<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Purchase extends Model
{
    use HasFactory;
    
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tentant';

    /**
     * Get product of Purchase.
     */
    public function products(): MorphToMany
    {
        return $this->morphToMany(Product::class, 'invoiceable')
        ->withPivot('id','quantity','price','description',
        'currency_id','currency_rate','customfields','date'); 
    }


}
