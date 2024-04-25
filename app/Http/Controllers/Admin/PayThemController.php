<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\PayThemProduct;
use App\Model\BaseProduct;

class PayThemController extends Controller
{
    protected function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $products = BaseProduct::with('stocks')->where('parent_id', '=', 0)
                ->where('product_type', '=', 'paythem')
                ->where('deleted', '=', 0)
                ->where('product_name_en', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        } else {
            $products = BaseProduct::with('stocks')->where('parent_id', '=', 0)
                ->where('product_type','=','paythem')
                ->where('deleted', '=', 0)
                ->orderBy('id', 'desc')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        }
        $products->appends(['filter' => $filter]);

        $classification = \App\Model\Classification::where(['parent_id' => 0])
            ->orderBy('name_en', 'asc')
            ->get();

        return view('admin.paythem.index', [
            'products' => $products,
            'filter' => $filter,
        ]);
    }
}
