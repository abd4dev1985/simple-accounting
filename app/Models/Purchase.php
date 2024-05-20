<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Actions\Inventory\Inventory;
use Illuminate\Support\Facades\Cache;
use function Illuminate\Events\queueable;



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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['document_id','date',];

    /**
     * Get product of Purchase.
     */
    public function products(): MorphToMany
    {
        return $this->morphToMany(Product::class, 'invoiceable','invoices')
        ->withPivot('id','quantity','price','ammount','description',
        'currency_id','currency_rate','customfields','date'); 
    }

    /**
     * Get the document that owns the purchse.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created( queueable (function (Purchase $purchase) {
            $inventory = app(Inventory::class);
            $data =['StartDate'=>Cache::store('tentant')->get('StartPeriod'),'EndDate'=>today()];
            Cache::store('tentant')->put('Inventory_Count',$inventory->count($data))     ;   
        }));
    }


}
