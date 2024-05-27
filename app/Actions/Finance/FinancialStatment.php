<?php

namespace App\Actions\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Account;
use App\Actions\Inventory\Inventory;



class FinancialStatment
{

    /**
     * Display the TradeStatment.
     */
    
     public function TradeStatment($StartDate,$EndDate)
    {
     
        $Net_Purchases = Account::balances(5 ,$StartDate ,$EndDate )[0];
        $Net_Sales = Account::balances(6,$StartDate,$EndDate)[0];
        $Beginning_Inventory = Account::balances(15,$StartDate,$EndDate)[0];

        $End_Inventory_Valuation = app(Inventory::class)
        ->Valuate(['StartDate'=>$StartDate ,'EndDate'=>$EndDate]);
        // get total cost of ending inventory
        $Ending_Iventory_cost = $End_Inventory_Valuation->reduce(function($carry,$product){
            return $carry + $product->ending_inventory_cost;
        });
        $Cost_Of_Goods_Sold =  $Beginning_Inventory->balance + $Net_Purchases->balance - $Ending_Iventory_cost; 
        $Net_Trade_Statment  = abs ($Net_Sales->balance ) -  $Cost_Of_Goods_Sold ;

        $Trade_Statment = [
            'Net_Purchases'=>   $Net_Purchases,
            'Net_Sales'=>   $Net_Sales,
            'Ending_Iventory_cost'=>  $Ending_Iventory_cost,
            'Beginning_Inventory' => $Beginning_Inventory,
            'Cost_Of_Goods_Sold' =>$Cost_Of_Goods_Sold,
            'Net_Trade_Statment' => $Net_Trade_Statment,
        ];
        return $Trade_Statment;
                    
    }
    
  

    public function IncomeStatment( $StartDate,$EndDate)
    {
        $Revenues  = Account::balances(4 ,$StartDate ,$EndDate )[0];
        $Expenses  = Account::balances(3 ,$StartDate ,$EndDate )[0];
        $Income_Statment = $this->TradeStatment($StartDate,$EndDate);
        $Income_Statment['Revenues']= $Revenues ;
        $Income_Statment['Expenses']= $Expenses ;
        $Income_Statment['NetIncome']= $Income_Statment['Net_Trade_Statment']+ abs($Revenues->balance ) -$Expenses->balance  ;

        return $Income_Statment;






    }

    
}

