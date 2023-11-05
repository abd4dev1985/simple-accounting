<?php

namespace App\Http\Controllers;

use App\Models\Income_payment_receipt;
use App\Http\Requests\StoreIncome_payment_receiptRequest;
use App\Http\Requests\UpdateIncome_payment_receiptRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Fluent ;
use App\Actions\AccountingEnrty;


class IncomePaymentReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      return "nnnnn";
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request )
    {
        return Inertia::render('invoice', [
            'testprops'=>["name"=>'sozan',"age"=>5],
            'suggested_accounts'=>Inertia::lazy(
                fn()=>Account::where('name','like','%'.$request->searchForAccount .'%')->get()
            ),
            
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) 
    {
        //$validated = $request->validated();
       // $entry = app(AccountingEnrty::class)->create($request->user(),$request->all());
       $entry = app(AccountingEnrty::class)->create($request->all());
        return  $entry ;
       
       //return  redirect('/IncomePaymentReceipts');
        
        $income_payment_receipt = Income_payment_receipt::create([
            'amount' => 4500,
        ]);
        
        
        $income_payment_receipt->save();
        $income_payment_receipt->accounts()->sync([
            2 => ['debit_amount' => $request->debit_amount, 'credit_amount'=>$request->credit_amount, 'discription'=>'payment'],
         ]);

        return $income_payment_receipt->with('accounts')->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(Income_payment_receipt $income_payment_receipt)
    {
        return Inertia::render('invoice', []);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Income_payment_receipt $income_payment_receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIncome_payment_receiptRequest $request, Income_payment_receipt $income_payment_receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income_payment_receipt $income_payment_receipt)
    {
        //
    }
}
