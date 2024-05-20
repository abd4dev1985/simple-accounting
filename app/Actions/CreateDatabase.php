<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Artisan;

class CreateDatabase 
{
    /**
     * Create a new database for subscriber user.
     *
     * @param  array<string, string>  $input
     */
    public function create(User $user,array $input )
    {
        Gate::forUser($user)->authorize('create', Jetstream::newTeamModel());
        if ( $user->hasRole(['admin', 'subscriber'])) {
            
        }

        $Validated=Validator::make($input, [
            'name' => ['required', 'string', 'max:255', 'unique:teams'],
            'StartPeriod' =>['required'],
        ])->validateWithBag('createTeam');

       // $Validated =  $Validator->safe()->all();
        
        $db = $Validated['name'];
        $db = Str::slug($db,'-');
        $user_name = "user-".$db;
        $password = null;

        $pdo = DB::connection()->getPdo();
        try {
            $pdo->exec("CREATE DATABASE `$db` DEFAULT CHARACTER SET utf8 COLLATE 'utf8_general_ci';
                    CREATE USER '$user_name'@'localhost' IDENTIFIED BY '$password';
                    GRANT ALL ON `$db`.* TO '$user_name'@'localhost';
                    FLUSH PRIVILEGES; ")
                    
            or die(print_r($db->errorInfo(), true));   
        }
        catch (PDOException $e) {
            die("DB ERROR: " . $e->getMessage());
        }
        config(['database.connections.tentant.database'=>$db,]) ;
        Cache::store('tentant')->put('StartPeriod',$Validated['StartPeriod'] );

       
    }
    
   
}
