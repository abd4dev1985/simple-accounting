<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\JournalEntry;
use App\Models\Document_catagory;
use App\Models\Document;
use App\Models\Currency;
use App\Actions\AccountingEnrty;
use App\Actions\Invoice;

use Illuminate\Support\Facades\Cache;

use App\Models\Account_entry ;
use App\Models\Entry;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Fluent ;
use App\Actions\DatabaseManager;
use App\Models\Account;
use App\Models\CustomField;

class PurchaseController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->middleware('CurrentDatabase');

    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Document_catagory $document_catagory)
    {
        {
           // dd($document_catagory->name);
            $last_document_number=Cache::store('tentant')->get('last '.$document_catagory->name);
           // dd($last_document_number);
            return Inertia::render('Invoice', [
                'document_catagory'=> $document_catagory ,
                'new_document_number' =>$last_document_number + 1,
                'operation'=>'create',
                'columns_count'=>8,
                'customfields'=>CustomField::all('name')->map(function($Field){return $Field->name;})->toArray(),
                'default_account'=>[],
                'pervious_document_url' => !($last_document_number) ? null: route('purchase.show',[
                    'document_catagory'=>$document_catagory->name,
                    'document'=>$last_document_number,
                ]),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Document_catagory $document_catagory, Request $request )
    {
        //return $request;
        $AccountingEnrty= app(AccountingEnrty::class);
        $validated_data =  $AccountingEnrty->validate($request->all());
       // dd( $validated_data );
        if (  $AccountingEnrty->validation_is_failed) {  
            return back()->withErrors($AccountingEnrty->validator)->withInput();
        }
        //return $validated_data;
        //dd($validated_data);
        $entry = $AccountingEnrty->create($validated_data);
        $document = Document::create([ 
            'number'=> $validated_data['document_number'] ,
            'document_catagory_id' => $document_catagory->id ,
            'entry_id'=> $entry->id,
            'date'=>$validated_data['date'],
        ]);

        $last_document_number=Cache::store('tentant')->get('last '.$document_catagory->name);
        if ( $document->number > $last_document_number) { 
            Cache::store('tentant')->put('last '.$document_catagory->name,  $document->number);  
        }
        return back()->with('success','ok');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}
