<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Account;



class Financial_Statment_Controller extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->middleware('CurrentDatabase');

    }

    /**
     * Display the specified resource.
     */
    
     public function TradeStatment( Request $request)
    {
         $validator = Validator::make($request->all() ,[
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
         $accounts = Account::where('statment_id',3)->where('father_account_id',null)->get();
         $accounts = $accounts->map(function($account) use($data){
            return Account::balances($account->id,$data['StartDate'],$data['EndDate']);
         });
         return back()->with('Trade_Statment.'.$data['winbox_id'],$accounts);
 
                            
    }

    
}
