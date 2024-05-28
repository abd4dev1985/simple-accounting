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

        $Net_Purchases = Account::balances(5 ,$StartDate ,$EndDate )[0];
        $Revenues  = Account::balances(4 ,$StartDate ,$EndDate )[0];
        $Expenses  = Account::balances(3 ,$StartDate ,$EndDate )[0];

        $Net_Sales = Account::balances(6,$StartDate,$EndDate)[0];
        $Beginning_Inventory = Account::balances(15,$StartDate,$EndDate)[0];

        $End_Inventory_Valuation = app(Inventory::class)
        ->Valuate(['StartDate'=>$StartDate ,'EndDate'=>$EndDate]);
        // get total cost of ending inventory
        $Ending_Iventory_cost = $End_Inventory_Valuation->reduce(function($carry,$product){
            return $carry + $product->ending_inventory_cost;
        });

        $Income_Statment = [
            'Net_Purchases'=>   $Net_Purchases,
            'Net_Sales'=>   $Net_Sales,
            'Revenues'=> $Revenues,
            'Expenses'=> $Expenses,
            'Ending_Iventory_cost'=>  $Ending_Iventory_cost,
            'Beginning_Inventory' => $Beginning_Inventory,
        ];

        return back()->with('Income_Statment.'.$data['winbox_id'],$Income_Statment);






    }

    
}

