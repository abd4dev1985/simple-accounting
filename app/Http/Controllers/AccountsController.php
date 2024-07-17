<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\Account;
use App\Models\Statment;

use App\Actions\AccountingEnrty;


use Illuminate\Support\Facades\Cache;
use App\Models\EntryLines ;
use Illuminate\Database\Query\JoinClause ;

use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Models\Entry;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        return Inertia::render('Account/Index', [
            'accounts' => Account::Descendants_accounts(),
            'statments' => Statment::all(),
            'test_account'=> Account::find(4),
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * store new resource.
     */
    public function store(Request $request)
    {
        //dd($request->ParentAccount);
        $validator = Validator::make($request->all() ,[
            'name'=>'required|unique:accounts,name', 
            'number'=>'required',
            'ParentAccount'=>'required|array' ,
            'has_sons_accounts'=>'required|boolean',
            ],$masge=[

            ],
        );
        if ($validator->fails()) {
             return back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        $data['father_account_id'] = $data['ParentAccount']['id'];
        $data['statment_id']= Account::find($data['father_account_id'])->statment_id ;

        $account = Account::create($data);
        return  redirect()->route('accounts.index');
        
    }

   
    /**
     * Display the specified resource.
     */
    
    public function ledgerBook( Request $request)
    {
        $validator = Validator::make($request->all() ,[
            'account'=>'required',
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
        $account = Account::find($data['account']['id']);

        $Account_with_SubAccounts =$account->Sub_Accounts();
        $Accounts_Ids_Array = Account::find($data['account']['id'])->Sub_Accounts_Ids();

        //$accounts = Account::with('entries')->whereIn('id', $Accounts_Ids_Array)->get();

        $entries = EntryLines::whereIn('account_id',$Accounts_Ids_Array )
        ->whereBetween('date', [ $data['StartDate'] , $data['EndDate']  ])
        ->where('date','<=',$data['EndDate'])->get()->groupBy('account_id');

        $accounts = $Account_with_SubAccounts->map( function( $account ) use( $entries){
        if ($entries->has($account->id)) {
                $account->entries = $entries[$account->id];
            }
            return $account;
        });
         if (count( $accounts)<2) {
            return back()->with('Account_Ledger_Book.'.$data['winbox_id'],$accounts->first());
        }

        $Account_GroupedBy_Parent = $accounts->groupBy('father_account_id');
        function Tree_Account($account,$Account_GroupedBy_Parent){
            if ($Account_GroupedBy_Parent->has($account->id)) {
                $account->children = $Account_GroupedBy_Parent[$account->id];
                foreach ($account->children as $child) {
                    Tree_Account($child,$Account_GroupedBy_Parent);
                }
            }
            return $account;
        }
        $account_tree = Tree_Account($account,$Account_GroupedBy_Parent) ;
        return back()->with('Account_Ledger_Book.'.$data['winbox_id'],$account_tree);
                           
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
        $array =[];

        $id = (array_key_exists("account",$data))? $data['account']['id']:null;

        $accounts_with_balances  =Account::balances($id,$data['StartDate'],$data['EndDate']);
        return back()->with('tial_balance.'.$data['winbox_id'],$accounts_with_balances);
                           
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
    public function destroy($id)
    {
        $this->authorize('delete', Account::class);
        $validator = Validator::make(['id'=> $id] ,['id'=>'required',]);
        $validator->after(function ($validator) use($id) {
            $account = Account::find($id) ;
            // check if account have some entries
            if ( $account->entries()->first() ) {
                $validator->errors()->add('Have_Entries','account can not deleted becuase Entries');
            }
            // check if account has Sub Accounts
            if (Account::where('father_account_id',$id)->first() ) {
                $validator->errors()->add('Have_Sub_Accounts','account can not deleted becuase it has sub accounts');
            }
        });
        // if validation  failed return errors
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Account::destroy($id);
        return  redirect()->route('accounts.index');



    }
}
