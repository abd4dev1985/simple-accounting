<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SebastianBergmann\Type\NullType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause ;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\Type\FalseType;

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','account_no','has_sons_acoounts','statment_id',];

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'tentant';

    public static $ancestors=[];

    /**
     * entries related to account.
     */
    public function entries(): BelongsToMany
    {
        
        return $this->belongsToMany(Entry::class)
        ->withPivot('debit_amount', 'credit_amount','description','currency_id','currency_rate','customfields','date',);
    }

    /**
    * Get all outcome_receipts for spesfic account .
     */
    public function JournalEntries(): MorphToMany
    {
        return $this->morphedByMany(JournalEntry::class,'transactions')
        ->withPivot('debit_amount', 'credit_amount','description');
    }
    /**
    * Get all outcome_receipts for spesfic account .
     */
    public function outcome_payment_receipts(): MorphToMany
    {
        return $this->morphedByMany(Outcome_payment_receipt::class,'transactions')
        ->withPivot('debit_amount', 'credit_amount','description');
    }

    /**
     * Get all Income_receipts for spesfic account .
     */
    public function income_payment_receipts(): MorphToMany
    {
        return $this->morphedByMany(Income_payment_receipt::class,'transactions')
        ->withPivot('debit_amount', 'credit_amount','description');
    }

    /**
     *  return account with balance or sub_accounts with balance
     */
    public static function balances($id,$start_date,$end_date )
    {
        if ( !self::find($id) ) {
            return null;
        }

        $accounts_with_balances =EntryLines::selectRaw('
        accounts.name,SUM( IFNULL(debit_amount,0) - IFNULL(credit_amount,0) )  as balance ,
        account_id ,father_account_id ,accounts.has_sons_accounts') 
        ->join('accounts', 'account_entry.account_id', '=', 'accounts.id')
        ->where(function(Builder $query ) use($id){
           if ( isset($id) ) {
                $ID_list =self::Get_Children_ids($id) ;
                $query->whereIn('account_id',$ID_list);
            }
        })
        ->whereBetween('date', [ $start_date,$end_date ])
        ->groupBy('account_id','accounts.name','accounts.has_sons_accounts','father_account_id');
        if ( $accounts_with_balances->get()->count()==0 ) {
            $account =self::find($id);
            $account->balance=0;
            return $account;
        }

        if ( !self::find($id)?->has_sons_accounts ) {
          return $accounts_with_balances->get();
        }
        
        return self:: Descendants_accounts( $id,$accounts_with_balances ) ;

    }


    /**
     *  return account with sub_accounts 
     */
    public function sub_accounts()
    {
        $accounts= DB::connection('tentant')->select('
            WITH RECURSIVE account_tree AS (
                SELECT id, name, father_account_id, 1 AS level
                FROM accounts 
                WHERE (father_account_id IS NULL AND id = ?) OR id = ?
                UNION ALL
                SELECT a.id, a.name, a.father_account_id, at.level + 1 AS level
                FROM accounts a
                JOIN account_tree at   ON a.father_account_id = at.id
            )
            SELECT * FROM account_tree;'
        ,[ $this->id,$this->id]);
        return  collect($accounts);
        //return $this::where('father_account_id',$this->id)->get();
    }

    public function Sub_Accounts_Ids()
    {
        $Sub_Accounts_Ids = $this->sub_accounts()->map(function($account){
            return $account->id;
        });
        return $Sub_Accounts_Ids ;
    }

    public static function Get_Children_ids($account_id){

        $grouped_accounts = self::all()->groupBy('father_account_id');
        $ids=[$account_id];
        if ($grouped_accounts->has($account_id)) {
            $account_children= $grouped_accounts[$account_id];
            foreach ($account_children as $child) {
                $GLOBALS['ids'][]=$child->id ;
                self::Get_Children_ids($child->id);
            }
            return $GLOBALS['ids']   ;
        }else{
             return  $ids  ;
        }
    
    }

    public static function Get_Children($account,$groupedaAcounts,$level=0){
        $account['level'] = $level ; 
        if ($groupedaAcounts->has($account->id)) {   
            $account['balance'] =0 ;    
            $level++;
            $account['children'] = $groupedaAcounts[$account->id]; 
            foreach ($account['children'] as $child) {
               // $GLOBALS['ids'][]=$child->id ;
                self::Get_Children($child,$groupedaAcounts,$level);
            } 
            $account['balance'] =  $account['children']->reduce(function( $carry, $child){
                $child['balance'] = ($child['balance'])? $child['balance']:0 ;
                return $carry+$child['balance'] ;
            });
        }

        return $account;
    }
    
    public static function Descendants_accounts( $id,$balances=null)
    {
        //dd($balances->get());
        $accounts_with_balances = self::selectRaw('id,balances.balance,accounts.name,accounts.father_account_id')
        ->leftJoinSub($balances, 'balances', function (JoinClause $join) {
            $join->on('accounts.id', '=', 'balances.account_id');
        })->get();  
        $accounts_grouped_by_parent =  $accounts_with_balances->groupBy('father_account_id');

        if (isset($id)) {
            return [self::Get_Children(self::find($id),$accounts_grouped_by_parent)];
        }else{
            $accounts= self::whereIn('id',[1,2,3,4,5])->get();
            return $accounts->map( function($account) use($accounts_grouped_by_parent){
                return self::Get_Children(self::find($account->id),$accounts_grouped_by_parent);
            });
        }  


        //dd( $accounts_grouped_by_parent);
        //$grouped_accounts = $grouped_accounts?->groupBy('father_account_id') ?? self::all()->groupBy('father_account_id');
      //  $grouped_accounts= self::all()   ;
      ////  if (isset($id)) {
      ///      return self::Get_Children(self::find($id),$grouped_accounts);
      //  }else{
        //    $accounts= self::whereIn('id',[1,2,3,4,5])->get();
        //    return $accounts->map( function($account) use($grouped_accounts){
         //       return self::Get_Children(self::find($account->id),$grouped_accounts);
         //   });
       // }  
    }

    public function father_account()
    {
        return $this::where('id',$this->father_account_id)->first();
    }

    public   function get_ancestors()
    {
       $account =$this->father_account();
       if (! is_null($account)) {
        $this::$ancestors[]=$account;
        $account->get_ancestors();
       }else{
        return $this::$ancestors  ;
       } 
    }
    public  function DescendantsAccounts( )
    {
    
    }





}
