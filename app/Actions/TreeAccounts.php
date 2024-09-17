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
        ['id'=>1,'number'=>1,'name'=>'Assets','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>2,'number'=>2,'name'=>'Liabilities and Equity','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>3,'number'=>3,'name'=>'Expenses','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
        ['id'=>4,'number'=>4,'name'=>'Revenues','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
        ['id'=>5,'number'=>5,'name'=>'Net Purchases','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
        ['id'=>6,'number'=>6,'name'=>'Net Sales','father_account_id'=>null, 'has_sons_accounts'=>true,'statment_id'=>2 ],
        // Assets subaccounts
        ['id'=>7,'number'=>11,'name'=>'Fixed Assets','father_account_id'=>1, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>8,'number'=>12,'name'=>'Current Assets','father_account_id'=>1, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        // fixed Asset  subaccounts
        ['id'=>9, 'number'=>111,'name'=>'Lands','father_account_id'=>7, 'has_sons_accounts'=>false,'statment_id'=>1 ],
        ['id'=>10,'number'=>112,'name'=>'Buldings','father_account_id'=>7, 'has_sons_accounts'=>false,'statment_id'=>1 ],
        ['id'=>11,'number'=>113,'name'=>'Cars','father_account_id'=>7, 'has_sons_accounts'=>false,'statment_id'=>1 ],
        //['id'=>12,'number'=>113,'name'=>'Furniture ','father_account_id'=>7, 'has_sons_accounts'=>false,'statment_id'=>1 ],

        // Current Asset  subaccounts
        ['id'=>13,'number'=>121,'name'=>'Customers','father_account_id'=>8, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>14,'number'=>122,'name'=> 'Other debitors','father_account_id'=>8, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>15,'number'=>123,'name'=> 'Stock','father_account_id'=>8, 'has_sons_accounts'=>false,'statment_id'=>1 ],
        ['id'=>16,'number'=>124,'name'=> 'Prepaid Expenses','father_account_id'=>8, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>12,'number'=>125,'name'=>'cash ','father_account_id'=>8, 'has_sons_accounts'=>false,'statment_id'=>1 ],

        // Customers  subaccounts
        ['id'=>17,'number'=>121001,'name'=> 'ahmad','father_account_id'=>13, 'has_sons_accounts'=>false,'statment_id'=>1 ],

        // NEt Purchases  subaccounts
        ['id'=>18,'number'=>51,'name'=> 'Purchases','father_account_id'=>5, 'has_sons_accounts'=>false,'statment_id'=>3 ],
        ['id'=>19,'number'=>52,'name'=> 'Purchases return','father_account_id'=>5, 'has_sons_accounts'=>false,'statment_id'=>3 ],
        ['id'=>20,'number'=>53,'name'=> 'Purchases transport expenses','father_account_id'=>5, 'has_sons_accounts'=>false,'statment_id'=>3 ],
        ['id'=>21,'number'=>54,'name'=> 'Purchases discounts','father_account_id'=>5, 'has_sons_accounts'=>false,'statment_id'=>3 ],

        //NEt Sales  subaccounts
        ['id'=>22,'number'=>61,'name'=> 'Salse','father_account_id'=>6, 'has_sons_accounts'=>false,'statment_id'=>3 ],
        ['id'=>23,'number'=>62,'name'=> 'Salse return','father_account_id'=>6, 'has_sons_accounts'=>false,'statment_id'=>3 ],
        ['id'=>24,'number'=>63,'name'=> 'Salse discounts','father_account_id'=>6, 'has_sons_accounts'=>false,'statment_id'=>3 ],

        //Liabilities and equity  subaccounts
        ['id'=>25,'number'=>21,'name'=> 'Equity','father_account_id'=>2, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>26,'number'=>22,'name'=> 'Liabilities','father_account_id'=>2, 'has_sons_accounts'=>true,'statment_id'=>1 ],

        // Liabilities  subaccounts
        ['id'=>27,'number'=>222,'name'=> 'Long term liabilities','father_account_id'=>26, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        ['id'=>28,'number'=>223,'name'=> 'Short term liabilities','father_account_id'=>26, 'has_sons_accounts'=>true,'statment_id'=>1 ],
        
        // Long term liabilities  subaccounts
        ['id'=>29,'number'=>2221,'name'=> 'bond','father_account_id'=>27, 'has_sons_accounts'=>false,'statment_id'=>1 ],

        // short term liabilities  subaccounts
        ['id'=>30,'number'=>2231,'name'=> 'Suplier & payable','father_account_id'=>28, 'has_sons_accounts'=>false,'statment_id'=>1 ],
        ['id'=>31,'number'=>2232,'name'=> 'short term bond','father_account_id'=>28, 'has_sons_accounts'=>false,'statment_id'=>1 ],
        ['id'=>32,'number'=>2233,'name'=> 'Accured expenses','father_account_id'=>28, 'has_sons_accounts'=>false,'statment_id'=>1 ],

        // equity subaccounts
        ['id'=>33,'number'=>211,'name'=> 'capital equity','father_account_id'=>25, 'has_sons_accounts'=>false,'statment_id'=>1 ],
        ['id'=>34,'number'=>212,'name'=> 'Retained Earnings','father_account_id'=>25, 'has_sons_accounts'=>false,'statment_id'=>1 ],





    ];
    public $items=[];
   

    
    // has_sons_accounts has_sons_accounts

    /**
     * Create a new invoice for transaction.
    
     */
    public function create()
    {
     
        Account::upsert($this->accounts,['id'],['number','name','father_account_id','has_sons_accounts','statment_id'  ]);

    }
    
    
   
   
   
}
