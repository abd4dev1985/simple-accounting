<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Document_catagory;
use App\Models\Document;
use App\Models\Currency;
use App\Actions\AccountingEnrty;
use Illuminate\Support\Facades\Cache;

use App\Models\Account_entry ;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Models\Entry;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Fluent ;
use App\Actions\DatabaseManager;
use App\Models\Account;
use App\Models\CustomField;


class JournalEntryController extends Controller
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
        return "index"  ;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create( Document_catagory $document_catagory )
    {
        $last_document_number=Cache::store('tentant')->get('last '.$document_catagory->name);
        $columns=[
            'debit_amount'=>[1,'number','Debite'] ,
            'credit_amount'=>[2,'number','Credite'] ,
        ];

        return Inertia::render('Entry', [
            'document_catagory'=> $document_catagory ,
            'new_document_number' =>$last_document_number + 1,
            'operation'=>'create',
            'columns'=> $columns ,
            'columns_count'=>8,
            'customfields'=>CustomField::all('name')->map(function($Field){return $Field->name;})->toArray(),
            'default_account'=>[],
            'pervious_document_url' => route('entry.show',[
                'document_catagory'=>$document_catagory->name,
                'document'=>$last_document_number,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Document_catagory $document_catagory, Request $request)
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
       // return  redirect()->route('entry.create',['document_catagory'=>$document_catagory->name ])  ;

    }
    /**
     * test store.
     */
    public function teststore( )
    {
        return "hiiii";
    }

    /**
     * Display the specified resource.
     */
    public function show( Document_catagory $document_catagory ,Document $document,Request $request  )
    { 
        $currencies=Currency::all();
        $entry = $document->entry ;
        $entry_lines = $entry->accounts->map(function($item){
            $item['pivot']['account'] =  ['id'=>$item['id']  ,'name'=>$item['name'] ];
            return $item['pivot'];
        }) ;
        $pervious_document=Document::where('number','<',$document->number)->orderBy('number','desc')->first();
        $next_document=Document::where('number','>',$document->number)->orderBy('number','asc')->first();

        return Inertia::render('Entry', [
            'currencies' =>$currencies,
            'document_catagory'=> $document_catagory ,
            'document' => $document ,
            'entry_lines' =>$entry_lines  ,
            'operation'=>'update',
            'columns_count'=>8,
            'customfields'=>CustomField::all('name')->map(function($Field){return $Field->name;})->toArray(),
            'delete_url'=>route('entry.delete',[
                'document_catagory'=> $document_catagory->name,
                'document'=>$document->number ,
            ]),
            'update_url'=>route('entry.update',[
                'document_catagory'=> $document_catagory->name,
                'document'=>$document->number ,
            ]),
            'pervious_document_url' =>( $pervious_document)? route('entry.show',[
                'document_catagory'=>$document_catagory->name,
                'document'=>$pervious_document?->number ,
            ]) :  null ,
            'next_document_url' =>( $next_document)? route('entry.show',[
                'document_catagory'=>$document_catagory->name,
                'document'=>$next_document?->number ,
            ]) :  null ,
            
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JournalEntry $journalEntry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( Document_catagory $document_catagory ,Document $document,Request $request)
    {   
        //dd($request->all());
        $AccountingEnrty= app(AccountingEnrty::class);
        $validated_data =  $AccountingEnrty->validate($request->all());
        if (  $AccountingEnrty->validation_is_failed) {  
            return back()->withErrors($AccountingEnrty->validator)->withInput();
        }
        $entry = $AccountingEnrty->UpdatLines($document->entry,$validated_data);
        return back()->with('success','ok');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document_catagory $document_catagory ,Document $document)
    {
        $entry=Entry::find($document->entry_id);
        $last_document_number = Cache::store('tentant')->get('last '.$document_catagory->name);
        if ( $last_document_number ==$document->number) {
            $last_document_number=Document::where('number','<',$document->number)->orderBy('number','desc')->first();
            Cache::store('tentant')->put('last '.$$document_catagory->name, $last_document_number);
        }
        $entry->accounts()->detach(); 
        $entry->delete();
        Document::destroy($document->id);
        return redirect()->route('entry.create',['document_catagory'=>$document_catagory->name]) ;
    }
}
