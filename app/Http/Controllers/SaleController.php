<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Document_catagory;
use App\Models\Document;
use App\Models\Invoice as Invoice_line  ;
use App\Models\Currency;
use App\Actions\Invoice;
use App\Actions\Invoice\CreateInvoice;
use App\Actions\Invoice\UpdateInvoice;
use App\Actions\Invoice\ValidateInvoice;

use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\CustomField;
use App\Models\Entry;
use Illuminate\Support\Facades\DB;



class SaleController extends Controller
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
        $last_document=Cache::store('tentant')->get('last '.$document_catagory->name);
        return Inertia::render('Invoice', [
            'document_catagory'=> $document_catagory ,
            'new_document_number' =>$last_document ?->number + 1,
            'last_document'=> $last_document ,
            'invoice_type'=>'sale',
            'operation'=>'create',
            'columns_count'=>8,
            'store_url'=>route('sale.store',[
                'document_catagory'=> $document_catagory->id,
            ]),
            'customfields'=>CustomField::all('name')->map(function($Field){return $Field->name;})->toArray(),
            'cash_account'=>Account::find(12),
            'default_account'=>Account::find(22),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Document_catagory $document_catagory, Request $request )
    {
        $request['operation']='create';
        dd($request->all());
        $valid_data = app(ValidateInvoice::class)->validate($request->all());
        $document = Document::create([ 
            'number'=> $valid_data['document_number'] ,
            'document_catagory_id' => $document_catagory->id ,
            'date'=>$valid_data['date'],
        ]);
        $sale = Sale::create(['document_id'=>$document->id]);

        app(CreateInvoice::class,['invoice'=>$sale ,'document' => $document])->create($valid_data);
       
        $last_document=Cache::store('tentant')->get('last '.$document_catagory->name);
        if ( $document->number > $last_document?->number) { 
            Cache::store('tentant')->put('last '.$document_catagory->name,  $document);  
        }
        return back()->with('success','ok'); 
    }
    /**
     * get next  invoice document.
     */
    public function next(Document_catagory $document_catagory, Document $document)
    {
        $next_document = Document::where('number','>',$document->number)
        ->where('document_catagory_id',$document_catagory->id)->orderBy('number','asc')->first();
        if ($next_document) {
            return  redirect()->route('sale.show',[
                'document_catagory'=>$document_catagory->name,'document'=>$next_document?->number
            ]) ;
        }else{
            return back();
        }
    }
    /**
     * get pervious invoice document.
     */
    public function pervious(Document_catagory $document_catagory, Document $document)
    {
        $pervious_document=Document::where('number','<',$document->number)
        ->where('document_catagory_id',$document_catagory->id)->orderBy('number','desc')->first();
        if ($pervious_document) {
            return  redirect()->route('sale.show',[
                'document_catagory'=>$document_catagory->name,'document'=>$pervious_document?->number
            ]) ;
        } else {
            return back();
        }   
    }

    /**
     * Display the specified resource.
     */
    public function show(Document_catagory $document_catagory, Document $document)
    {
        $sale =$document->sale()->first();
        $invoice_line = $sale->products->map(function($item){
            $item['pivot']['product'] =  ['id'=>$item['id']  ,'name'=>$item['name'] ];
            return $item['pivot'];
        }) ;
        $entry = $document->entry ;
        $entry_lines = $entry->accounts->map(function($item){
            $item['pivot']['account'] =  ['id'=>$item['id']  ,'name'=>$item['name'] ];
            return $item['pivot'];
        }) ;
        $last_document_number=Cache::store('tentant')->get('last '.$document_catagory->name);
        $invoice = $sale ->products->first(); 
        //dd( $invoice->pivot);
        $Invoice_Currency = ['name'=>'fff', 'id'>55, 'rate'=>15400];
        return Inertia::render('Invoice', [
            'document_catagory'=> $document_catagory ,'document' => $document ,
            'invoice_type'=>'sale','operation'=>'update','columns_count'=>8,
            'currency_id'=> $invoice->pivot->currency_id , 
            'Invoice_Currency' =>  $Invoice_Currency ,
            'Invoice_Currency_Rate'=>$invoice->pivot->currency_rate  ,
            'operation'=>'update',
            'invoice_lines'=>$invoice_line,'entry_lines'=>$entry_lines,
            'store_url'=>route('sale.store',[ 'document_catagory'=> $document_catagory->id,]),
            'delete_url'=>route('sale.delete',['document_catagory'=>$document_catagory->name,'document'=>$document->number,]),
            'update_url'=>route('sale.update',['document_catagory'=> $document_catagory->name,'document'=>$document->number ]),
            'customfields'=>CustomField::all('name')->map(function($Field){return $Field->name;})->toArray(),
            'cash_account'=>Account::find(12),
            'default_account'=>Account::find(22),
            
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update( Document_catagory $document_catagory, Document $document,Request $request )
    {
        $request['operation']='update';
        //dd($request->all());
        $valid_data = app(ValidateInvoice::class)->validate($request->all());
        $sale = $document->sale;
        app(UpdateInvoice::class,['invoice'=>$sale ,'document' => $document])->update($valid_data);
        return back()->with('success','ok');  
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document_catagory $document_catagory, Document $document)
    {
        $entry=Entry::find($document->entry_id);
        $sale=Sale::where('document_id',$document->id)->first();
        $last_document = Cache::store('tentant')->get('last '.$document_catagory->name);
        if ( $last_document->number ==$document->number) {
            $last_document=Document::where('number','<',$document->number)->orderBy('number','desc')->first();
            Cache::store('tentant')->put('last '.$document_catagory->name, $last_document);
        }
        $entry->accounts()->detach(); 
        $entry->delete();
        $sale->products()->detach();
        Sale::destroy( $sale->id);
        Document::destroy($document->id);
        return redirect()->route('sale.create',['document_catagory'=>$document_catagory->name]) ;
    }
}
