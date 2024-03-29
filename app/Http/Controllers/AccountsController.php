<?php

namespace App\Http\Controllers;

use App\Models\Document_catagory;
use App\Models\Document;

use App\Models\Currency;
use App\Models\Account;

use App\Actions\AccountingEnrty;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\Cache;
use App\Models\EntryLines ;
use Illuminate\Database\Query\JoinClause ;

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
use Dflydev\DotAccessData\Data;

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
    
    public function ledgerBook( Request $request)
    {
        $validator = Validator::make($request->all() ,[
            //'account'=>'required',
            'StartDate'=>'required','date' ,
            'EndDate'=>'required','date',
            'winbox_id'=>'required',

            ],$masge=[

            ],
        );
        if ($validator->fails()) {
             return back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        return  EntryLines::where('account_id',$data['account']['id'])
                ->whereBetween('date', [ $data['StartDate'] , $data['EndDate']  ])
                ->where('date','<=',$data['EndDate'])
                ->get();  
                           
    }
     /**
     * Display the Trial_Balance
     */
    public function TrialBalance( Request $request)
    {
        $validator = Validator::make($request->all() ,[
            'account'=>['nullable','array'],
            'account.id'=>['required_with:account','numeric'],
            'StartDate'=>'required','date' ,
            'EndDate'=>'required','date',
            'winbox_id'=>'required',

            ],$masge=[

            ],
        );
        if ($validator->fails()) {
             return back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        $id = (array_key_exists("account",$data))? $data['account']['id']:null;

        $balances  =Account::balances($id,$data['StartDate'],$data['EndDate']);
        
       
           //return  Account::Descendants_accounts($id,$balances);
            $accounts=Account::Descendants_accounts($id,$balances);
            return back()->with('tial_balance.'.$data['winbox_id'],$accounts);

            
                                  
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
