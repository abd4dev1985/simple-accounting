<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account_entry extends Model
{
    use HasFactory; /**
    * The table associated with the model.
    *
    * @var string
    */
   protected $table = 'account_entry';
}
