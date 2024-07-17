<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Account;
use App\Actions\Inventory\Inventory;
use App\Actions\Finance\FinancialStatment;




class Financial_Statment_Controller extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->middleware('CurrentDatabase');

    }

    /**
     * Display the TradeStatment.
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
         $StartDate = $data['StartDate'];
         $EndDate = $data['EndDate'];
         $Trade_Statment =app(FinancialStatment::class)->TradeStatment($StartDate,$EndDate);
        //dd([app(FinancialStatment::class)->NetTradeStatment($StartDate,$EndDate),$Trade_Statment]) ;
        return back()->with('Trade_Statment.'.$data['winbox_id'],$Trade_Statment);
                    
    }

    public function IncomeStatment( Request $request){
       
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
        $StartDate = $data['StartDate'];
        $EndDate = $data['EndDate'];
        $Income_Statment =app(FinancialStatment::class)->IncomeStatment($StartDate,$EndDate);
       //dd([app(FinancialStatment::class)->NetTradeStatment($StartDate,$EndDate),$Trade_Statment]) ;
       return back()->with('Income_Statment.'.$data['winbox_id'],$Income_Statment);
    }
    //BalanceSheet
    public function BalanceSheet( Request $request){
       
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
        $StartDate = $data['StartDate'];
        $EndDate = $data['EndDate'];
        $Balance_Sheet = app(FinancialStatment::class)->BalanceSheet($StartDate,$EndDate);
        //return  $Balance_Sheet ;
       return back()->with('Balance_Sheet.'.$data['winbox_id'],$Balance_Sheet);
    }



    
}

