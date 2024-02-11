<?php

namespace App\Actions;

use App\Models\Invoice as Invoice_line;
use App\Models\Account;

use App\Actions\AccountingEnrty;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Fluent ;
use Illuminate\Validation\Rule;
use App\Rules\CompositeUnique;

class TreeAccounts 
{


 
    public $accounts=[
        ['id'=>1,'account_no'=>1,'name'=>'Assets','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>2,'account_no'=>2,'name'=>'Liabilities','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>3,'account_no'=>3,'name'=>'Expenses','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
        ['id'=>4,'account_no'=>4,'name'=>'Revenues','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
        ['id'=>5,'account_no'=>5,'name'=>'Net Purchases','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
        ['id'=>6,'account_no'=>6,'name'=>'Net Sales','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
        // Assets subaccounts
        ['id'=>7,'account_no'=>11,'name'=>'Fixed Assets','father_account_id'=>'1', 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>8,'account_no'=>12,'name'=>'Current Assets','father_account_id'=>'1', 'has_sons_accounts'=>true,'statment_id'=>1 ],
        // fixed Asset  subaccounts
        [ 'id'=>9, 'account_no'=>111,'name'=>'Lands','father_account_id'=>'7', 'has_sons_accounts'=>false,'statment_id'=>1 ],
        ['id'=>10,'account_no'=>112,'name'=>'Buldings','father_account_id'=>'7', 'has_sons_accounts'=>false,'statment_id'=>1 ],
        ['id'=>11,'account_no'=>113,'name'=>'Cars','father_account_id'=>'7', 'has_sons_accounts'=>false,'statment_id'=>1 ],
        ['id'=>12,'account_no'=>113,'name'=>'Furniture ','father_account_id'=>'7', 'has_sons_accounts'=>false,'statment_id'=>1 ],






    ];
    public $items=[];
   

    
    // has_sons_accounts has_sons_accounts

    /**
     * Create a new invoice for transaction.
    
     */
    public function create()
    {
        $this->items=[
            ['id'=>1,'account_no'=>1,'name'=>'Assets','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>1 ],
            ['id'=>2,'account_no'=>2,'name'=>'Liabilities','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>1 ],
            ['id'=>3,'account_no'=>3,'name'=>'Expenses','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
            ['id'=>4,'account_no'=>4,'name'=>'Revenues','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
            ['id'=>5,'account_no'=>5,'name'=>'Net Purchases','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
            ['id'=>6,'account_no'=>6,'name'=>'Net Sales','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],


            //['account_no'=>11,'name'=>'Fixed Assets','father_account_id'=>'1', 'has_sons_accounts'=>true,'statment_id'=>1 ],
            //['account_no'=>12,'name'=>'Current Assets','father_account_id'=>'1', 'has_sons_accounts'=>true,'statment_id'=>1 ],



          //  ['account_no'=>111,'name'=>'Lands','father_account_id'=>'2', 'has_sons_accounts'=>false,'statment_id'=>1 ],
           // ['account_no'=>112,'name'=>'Buldings','father_account_id'=>'2', 'has_sons_accounts'=>false,'statment_id'=>1 ],
           // ['account_no'=>113,'name'=>'Cars','father_account_id'=>'2', 'has_sons_accounts'=>false,'statment_id'=>1 ],
           // ['account_no'=>113,'name'=>'Furniture ','father_account_id'=>'2', 'has_sons_accounts'=>false,'statment_id'=>1 ],

           
          //  ['account_no'=>121,'name'=>'Customers','father_account_id'=>6, 'has_sons_accounts'=>true,'statment_id'=>1 ],
           // ['account_no'=>122,'name'=> 'Other debitors','father_account_id'=>6, 'has_sons_accounts'=>true,'statment_id'=>1 ],
           // ['account_no'=>123,'name'=> 'Stock','father_account_id'=>6, 'has_sons_accounts'=>true,'statment_id'=>1 ],
           // ['account_no'=>124,'name'=> 'Prepaid Expenses','father_account_id'=>6, 'has_sons_accounts'=>true,'statment_id'=>1 ],


           // ['account_no'=>51,'name'=> 'Purchases ','father_account_id'=>6, 'has_sons_accounts'=>true,'statment_id'=>1 ],
           // ['account_no'=>61,'name'=> 'salse','father_account_id'=>6, 'has_sons_accounts'=>true,'statment_id'=>1 ],


        ];
        Account::upsert($this->accounts,['id'],['account_no','name','father_account_id','has_sons_accounts','statment_id'  ]);


    }
    
    
   
   
   
}
