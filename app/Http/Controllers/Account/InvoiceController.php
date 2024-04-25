<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\CoreApiController;
use App\Model\Bill;
use App\Model\Invoice;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvoiceController extends CoreApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // echo Hash::make('BJ@account');die;
        return view('account.index',[
            'invoices'  => Invoice::orderBy('id','desc')->groupBy('order_id','selling_price','purchase_price')->get()
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
        $bills = Bill::where('invoice_id',$id)->get();
        return view('account.invoice.show',[
            'invoice'  => Invoice::find($id),
            'attached_bills'      => $bills,
            'bills'  => $bills->count() - 1,
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
        $bills = Bill::where('invoice_id',$id)->get();
        return view('account.invoice.add-bill',[
            'invoice'  => Invoice::find($id),
            'attached_bills'      => $bills,
            'bills'  => $bills->count() - 1,
        ]);
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
        $total_purchase_amount  = 0;
        $purchase_parties = $request->input('purchase_parties');
        $purchases_by = $request->input('purchases_by');
        $purchase_amounts = $request->input('purchase_amounts');  
            // Delete any old data regarding Record
        Bill::where('invoice_id',$id)->delete();
            // insert bills data
        if($purchase_parties != null){
            for($i=0; $i < count($request->input('purchase_parties')); $i++){
                $file_name = 'not_found.jpeg';
                if($request->hasFile('bill_images') && isset($request->bill_images[$i])){ 
                    $file_name = time(). '-' . 'bill_images-'.$i . '.' . $request->bill_images[$i]->extension();
                    $request->bill_images[$i]->move(public_path('images/bill-images'),$file_name);
                }
                // dd($purchase_amounts[$i]);
                $total_purchase_amount  += $purchase_amounts[$i];
                // dd('wait');
                // dump($file_name);
                Bill::create([
                    'invoice_id'   => $id,
                    'purchase_party'  => $purchase_parties[$i],
                    'purchase_by'  => $purchases_by[$i],
                    'bill_image'  => $file_name,
                    'purchase_amount'  => $purchase_amounts[$i],
                ]);
            }
        }
            // Update Account Record with Purchase Price
        Invoice::where('id',$id)->update([
            'purchase_price' => $total_purchase_amount
        ]);
        // dd('wait');
        $request->session()->flash('success','Record Updated.');
        return redirect('account/invoice');
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

    public function downloadImage(Request $request){
        $file_name = $request->input('image');
        
        $myFile = public_path("images/bill-images/".$file_name);
        $headers = ['Content-Type: application/file'];
        $path = public_path('images/bill-images/'.$file_name);
        $isExists =   File::exists($path);
        if($isExists == false){
            $request->session()->flash('danger','File Not Found');
            return redirect('account/invoice');
        }
        else{
            return response()->download($myFile, $file_name, $headers);
        }
        return redirect('account/invoice');
    }
}
