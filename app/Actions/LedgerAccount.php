<?php

namespace App\Actions;

use Illuminate\Support\Facades\Validator;
use App\Models\Account;
use App\Models\EntryLines ;



class LedgerAccount
{

    public function get(array $data)
    {
        $account = Account::find($data['account']['id']);
        $Account_with_SubAccounts =$account->Sub_Accounts();
        $Accounts_Ids_Array = Account::find($data['account']['id'])->Sub_Accounts_Ids();

        $entries = EntryLines::whereIn('account_id',$Accounts_Ids_Array )
        ->whereBetween('date', [ $data['StartDate'] , $data['EndDate']  ])
        ->where('date','<=',$data['EndDate'])->get()->groupBy('account_id');

        // get Collection of Previous balances of acounts 
        $Previous_Balances_Collection = EntryLines::selectRaw('
        SUM( IFNULL(debit_amount,0) - IFNULL(credit_amount,0) )  as balance ,
        account_id  ')
        ->whereIn('account_id',$Accounts_Ids_Array )
        ->where('date','<',$data['StartDate'])
        ->groupBy('account_id')->get()->groupBy('account_id');

        $Account_with_SubAccounts = $Account_with_SubAccounts->map( function( $account ) use( $entries,$Previous_Balances_Collection){
            if ($entries->has($account->id)) {
                $account->entries = $entries[$account->id];
            }
            if ($Previous_Balances_Collection->has($account->id)) {
                $account->PreviousBalance = $Previous_Balances_Collection[$account->id][0]->balance;
            }
            return $account;
        });
    
        if (count( $Account_with_SubAccounts)<2) {
            return $Account_with_SubAccounts->first();
        }
        $Account_GroupedBy_Parent = $Account_with_SubAccounts->groupBy('father_account_id');
        function Tree_Account($account,$Account_GroupedBy_Parent){
            if ($Account_GroupedBy_Parent->has($account->id)) {
                $account->children = $Account_GroupedBy_Parent[$account->id];
                foreach ($account->children as $child) {
                    Tree_Account($child,$Account_GroupedBy_Parent);
                }
            }
            return $account;
        }
            $Account_with_SubAccounts = Tree_Account($Account_with_SubAccounts->first(),$Account_GroupedBy_Parent) ;
            return $Account_with_SubAccounts;


    }

    
} 