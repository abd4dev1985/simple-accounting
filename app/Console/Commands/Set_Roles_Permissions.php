<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Hash;


class Set_Roles_Permissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-roles-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $admin = Role::create(['name' => 'admin']);
        $subscriber = Role::create(['name' => 'subscriber']);
        $trail_subscriber = Role::create(['name' => 'trail_subscriber']);
        $follower = Role::create(['name' => 'follower']);
        $create_database = Permission::create(['name' => 'create_database']);
        $use_database = Permission::create(['name' => 'use_database']);
        $delete_database = Permission::create(['name' => 'delete_database']);

        $admin=Role::find(1);
        $admin->syncPermissions([$create_database,$use_database,$delete_database]);

        $user= new  User ;
        $user->name = 'admin';
        $user->email = 'abdyia@yahoo.com';
        $user->password = Hash::make(env('ADMIN_PASSWORD'));
        $user->save();

        $user->assignRole('admin');

        $team= new  Team ;
        $team->name = 'admin team';
        $team->user_id = 1 ;
        $team->personal_team = true ;
        $team->save();
    
    }
}
