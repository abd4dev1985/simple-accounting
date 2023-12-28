<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Actions\DatabaseManager ;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tentant';
    //protected $connection = 'current_connection';

    //set current connection (Database)

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','catagory_id','count','serial',];




   // public function __construct(  array $attributes = array())
    //{
        // parent::__construct($attributes);

        // $request =app(Request::class);
        // $currentDatabase= app(DatabaseManager::class)->currentDatabase($request);
       // $this->setConnection($currentDatabase);
    
    //}
    /**
     * Get sales of product.
     */
    public function sales(): MorphToMany
    {
        return $this->morphedByMany(Sale::class, 'invoiceable','invoices')
        ->withPivot('id','quantity','price','ammount','description',
        'currency_id','currency_rate','customfields','date');
        
    }

    /**
     * Get purchases of product.
     */
    public function purchases(): MorphToMany
    {
        return $this->morphedByMany(Purchase::class, 'invoiceable','invoices')
        ->withPivot('id','quantity','price','ammount','description',
        'currency_id','currency_rate','customfields','date');
    }




}
