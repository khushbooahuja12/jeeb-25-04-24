<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\CoreApiController;
use App\Model\Bill;
use App\Model\Order;
use App\Model\OrderProduct;
use App\Model\TechnicalSupportProduct;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OrderController extends CoreApiController
{

    protected $order_status = [
        'Order placed',
        'Order confirmed',
        'Order assigned',
        'Order invoiced',
        'Cancelled',
        'Order in progress',
        'Out for delivery',
        'Delivered'
    ];

    public function __construct(Request $request)
    {
        $this->loadApi = new \App\Model\LoadApi();

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function index(Request $request, $slot = null)
    {
        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc');

            if (isset($_GET['delivery_date']) && $request->query('delivery_date') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($request->input('delivery_date'))));
            }
            if (isset($_GET['status']) && $request->query('status') != '') {
                $orders = $orders->where('orders.status', '=', $_GET['status']);
            }
            if (isset($_GET['orderId']) && $request->query('orderId') != '') {
                $orders = $orders->where('orders.orderId', 'like', '%' . $request->query('orderId') . '%');
            }

            $orders = $orders->paginate(50);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
        } else {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
        }

        return view('account.order.index', [
            'orders' => $orders,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : ''
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);
        $sub_order = Order::where(['parent_id' => $id])->first();
        $totalOutOfStockProduct = (new OrderProduct())->getTotalOfOutOfStockProduct($id);

        $order_products = OrderProduct::where(['fk_order_id'=>$id])
        ->orderBy('id','asc')
        ->get();

        return view('account.order.show', [
            'order' => $order,
            'order_status' => $this->order_status,
            'totalOutOfStockProduct' => $totalOutOfStockProduct,
            'sub_order' => $sub_order,
            'order_products' => $order_products
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
