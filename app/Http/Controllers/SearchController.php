<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Models\Account;
use App\Models\Product;

use App\Models\CostCenter ;
use Illuminate\Http\RedirectResponse;

class SearchController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->middleware('CurrentDatabase');

    }

    public function search_account(Request $request)
    {
        // $qurey = $request->searchForAccount;
        $qurey = $request->searchForAccount;
        
        $suggested_accounts = Account::where('name','like','%'.$qurey.'%')->get();
        return $suggested_accounts ;
    }

    public function search_cost_center(Request $request)
    {
        $qurey = $request->searchForCostCenter;
        
        $suggested_cost_centers = CostCenter::where('name','like','%'.$qurey.'%')->get();
        return $suggested_cost_centers ;
    }


    public function search_product(Request $request)
    {
        $qurey = $request->searchForProduct;
        
        $suggested_products = Product::where('name','like','%'.$qurey.'%')->get();
        return $suggested_products ;
    }
}
