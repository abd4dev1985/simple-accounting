<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Account;
use App\Actions\Inventory\Inventory;



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
         $StartDate = $data['StartDate'];
         $EndDate = $data['EndDate'];

       //  $accounts = Account::where('statment_id',3)->where('father_account_id',null)->get();
        // $accounts = $accounts->map(function($account) use($StartDate,$EndDate ){
        //    return Account::balances($account->id,$StartDate,$EndDate);
       //  });

        $Net_Purchases = Account::balances(5 ,$StartDate ,$EndDate )[0];
        $Net_Sales = Account::balances(6,$StartDate,$EndDate)[0];
        $Beginning_Inventory = Account::balances(15,$StartDate,$EndDate)[0];


        $End_Inventory_Valuation = app(Inventory::class)
        ->Valuate(['StartDate'=>$StartDate ,'EndDate'=>$EndDate]);

        // get total cost of ending inventory
        $Ending_Iventory_cost = $End_Inventory_Valuation->reduce(function($carry,$product){
            return $carry + $product->ending_inventory_cost;
        });

        $Trade_Statment = [
            'Net_Purchases'=>   $Net_Purchases,
            'Net_Sales'=>   $Net_Sales,
            'Ending_Iventory_cost'=>  $Ending_Iventory_cost,
            'Beginning_Inventory' => $Beginning_Inventory,
        ];

        return back()->with('Trade_Statment.'.$data['winbox_id'],$Trade_Statment);
 
                            
    }

    
}
