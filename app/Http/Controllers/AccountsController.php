<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Document_catagory;
use App\Models\Document;
use App\Models\Currency;
use App\Models\Account;

use App\Actions\AccountingEnrty;


use Illuminate\Support\Facades\Cache;
use App\Models\EntrLines ;

use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Models\Entry;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Fluent ;
use App\Actions\DatabaseManager;
use App\Models\CustomField;

class AccountsController extends Controller
{


    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->middleware('CurrentDatabase');

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
   
    /**
     * Display the specified resource.
     */
    public function ledgerBook(Account $account, Request $request)
    {
       // return  $account ;
       
        return EntrLines::where('account_id',$account->id)->
        where('created_at','>',	"2023-11-28T12:53:23.000000Z")->
        get();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }
}
