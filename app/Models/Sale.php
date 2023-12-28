<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Sale extends Model
{
    use HasFactory;
    
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
    protected $fillable = ['document_id','date',];

     /**
     * Get product of sale.
     */
    public function products(): MorphToMany
    {
        return $this->morphToMany(Product::class, 'invoiceable','invoices')
        ->withPivot('id','quantity','price','sale','description',
        'currency_id','currency_rate','customfields','date'); 
    }

    /**
     * Get the document that owns the purchse.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

}
