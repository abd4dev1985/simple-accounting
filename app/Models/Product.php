<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Actions\DatabaseManager ;
use Illuminate\Http\Request;

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
    public function __construct(  array $attributes = array())
    {
        parent::__construct($attributes);

        $request =app(Request::class);
        $currentDatabase= app(DatabaseManager::class)->currentDatabase($request);
        $this->setConnection($currentDatabase);
    
    }

}
