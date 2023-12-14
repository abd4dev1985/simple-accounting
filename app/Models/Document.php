<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Document extends Model
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
    protected $fillable = ['number','document_catagory_id','entry_id','date',];

    public function document_catagory()
    {
        return $this->belongsTo(Document_catagory::class);
    }
 

    public function entry():BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

/**
 * Get the route key for the model.
 */
    public function getRouteKeyName(): string
{
    return 'number';
}

   
}
