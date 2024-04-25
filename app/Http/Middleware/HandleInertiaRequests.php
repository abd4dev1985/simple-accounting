<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Currency;
use App\Actions\DatabaseManager;
use Illuminate\Support\Str;



class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {

        // set current Database
        if ($request->user()) {
          app(DatabaseManager::class)->currentDatabase($request);
        }  

        if (Str::contains($request->url(),'team')) {
            return array_merge(parent::share($request), [

            ]);
        }   
        
        return array_merge(parent::share($request), [
            'currencies' => ($request->user())? Currency::all():null,
            'year_start'=> '1/1/2024',
            'inventory_ledger' => $request->session()->get('inventory_ledger'),
            'tial_balance' => $request->session()->get('tial_balance'),
            'Account_Ledger_Book' => $request->session()->get('Account_Ledger_Book'), 
            'default_currency'=> ($request->user())? Currency::where('id',1)->first():null ,
        ]);
    }
}
