<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document_catagory extends Model
{
    use HasFactory;
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tentant';

    protected $fillable = ['name', 'type'];
    
    /**
     * Get the documets belong  for catagory 
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
