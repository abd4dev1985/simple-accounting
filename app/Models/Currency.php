<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tentant';

    
}
