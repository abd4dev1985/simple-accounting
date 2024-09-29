<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entry;


class DocumentController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->middleware('CurrentDatabase');

    }

    /**
     * Display the documents by entry id .
     */
    public function Get_Document_By_Entry( $entry_id)
    {   
        $document = Entry::find($entry_id)->document;
        $catagory = $document->document_catagory;
        switch ($catagory->type) {
            case 'entry':
                return redirect()->route('entry.show',['document_catagory'=>$catagory->name ,'document'=>$document->number ]);
            break;
            case 'purchase':
                return redirect()->route('purchase.show',['document_catagory'=>$catagory->name ,'document'=>$document->number ]);
            break;
            case 'sale':
                return redirect()->route('sale.show',['document_catagory'=>$catagory->name ,'document'=>$document->number ]);
            break; 
        }

    }



}
