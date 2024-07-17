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
        $balances =Account::balancesOf([5,6,15,],$StartDate,$EndDate);
        $Net_Purchases = $balances[5];
        $Net_Sales = $balances[6];
        $Beginning_Inventory =  $balances[15];

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
            'StartDate'=>$StartDate,
            'EndDate'=>$EndDate,
        ];
        return $Trade_Statment;
                    
    }
    
    public function IncomeStatment( $StartDate,$EndDate)
    {
        $balances =Account::balancesOf([5,6,15,4,3],$StartDate,$EndDate);
        $Net_Purchases = $balances[5];
        $Net_Sales = $balances[6];
        $Beginning_Inventory =  $balances[15];
        $Revenues =  $balances[4];
        $Expenses =  $balances[3];

        $End_Inventory_Valuation = app(Inventory::class)
        ->Valuate(['StartDate'=>$StartDate ,'EndDate'=>$EndDate]);
        // get total cost of ending inventory
        $Ending_Iventory_cost = $End_Inventory_Valuation->reduce(function($carry,$product){
            return $carry + $product->ending_inventory_cost;
        });

        $Cost_Of_Goods_Sold =  $Beginning_Inventory->balance + $Net_Purchases->balance - $Ending_Iventory_cost; 
        $Gross_profit  = abs ($Net_Sales->balance ) - $Cost_Of_Goods_Sold ;
        $Net_Profit =   $Gross_profit + abs($Revenues->balance)-  $Expenses->balance;

        $Income_Statment = [
            'Net_Purchases'=>   $Net_Purchases,
            'Net_Sales'=>   $Net_Sales,
            'Ending_Iventory_cost'=>  $Ending_Iventory_cost,
            'Beginning_Inventory' => $Beginning_Inventory,
            'Cost_Of_Goods_Sold' =>$Cost_Of_Goods_Sold,
            'Gross_profit' => $Gross_profit,
            'Revenues'=>$Revenues,
            'Expenses' => $Expenses ,
            'Net_Profit'=> $Net_Profit,
            'StartDate'=>$StartDate,
            'EndDate'=>$EndDate,
        ];


        return $Income_Statment;

    }

    public function BalanceSheet($StartDate,$EndDate)
    {
        $Income_Statment = $this->IncomeStatment($StartDate,$EndDate);
        $Beginning_Inventory =   $Income_Statment['Beginning_Inventory'] ;
        $Ending_Iventory_cost = $Income_Statment['Ending_Iventory_cost'];
        $Net_Profit = $Income_Statment['Net_Profit'];
        $balances =Account::balancesOf([1,2],$StartDate,$EndDate);
        $Assets =   $balances[1];
        $Liabilities = $balances[2];
        $current_year_income = Account::find(35);
        // in BalanceSheet statments ,
        // the balance of stock need to be adjudted to Ending Iventory cost
        // which also adjudte Assets balance
        $adjudte_Assets = $this->Update_Balance($Assets,$Beginning_Inventory,$Ending_Iventory_cost); 
        // we also need add net profit to equity
        $adjudte_Liabilities = $this->Update_Balance($Liabilities,$current_year_income,-$Net_Profit); 

        $Balance_Sheet = [
            'Net_Profit'=> $Net_Profit,
            'StartDate'=>$StartDate,
            'EndDate'=>$EndDate,
            'Assets'=>$adjudte_Assets,
            'Liabilities'=>$adjudte_Liabilities,
        ];

        return $Balance_Sheet;

    }




    public function Update_Balance($GrandParent,$child,$new_balance)
    {
        if ($GrandParent->children) {
            $GrandParent->balance =0 ;
            foreach ($GrandParent->children as $account) {
                if ($account->id==$child->id ) {
                    $account->balance=$new_balance;
                }
                $this->Update_Balance($account,$child,$new_balance);
            }
            $GrandParent->balance =  $GrandParent->children?->reduce(function( $carry, $child){
                $child->balance = ($child->balance)? $child->balance:0 ;
                return $carry+$child->balance ;
            });
            

        }
        return $GrandParent ;
       

    }

    
}

