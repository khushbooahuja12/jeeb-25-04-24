<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Vendor;
use Illuminate\Support\Facades\Hash;

class VendorController extends CoreApiController {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $vendors = Vendor::orderBy('id', 'desc')->get();
        return view('admin.vendors.index', ['vendors' => $vendors]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        return view('admin.vendors.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $password = $request->input('password');
        $vendor = Vendor::where('email', $request->input('email'))->get();
        if (count($vendor) > 0) {
            return back()->withInput()->with('error', 'Email already exist');
        } else {
            $insert_arr = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'company_name' => $request->input('company_name'),
                'address' => $request->input('address'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'country' => $request->input('country'),
                'pin' => $request->input('pin'),
                'password' => Hash::make($request->input('password')),
                'status' => 2
            ];

            $add = Vendor::create($insert_arr);
            if ($add) {
                $full_name = $request->input('name');
                $email = $request->input('email');
                \Mail::send('emails.vendorregistration', ['full_name' => $full_name, 'password' => $password], function ($message) use ($email) {
                    $message->from('jeebdeveloper@gmail.com', 'Jeeb');
                    $message->to($email);
                    $message->subject('Jeeb Registration');
                });
                return redirect('admin/vendors')->with('success', 'Vendor added successfully');
            }
            return back()->withInput()->with('error', 'Error while adding Vendor');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null) {
        $id = base64url_decode($id);

        $vendor = Vendor::find($id);

        return view('admin.vendors.show', ['vendor' => $vendor]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id = null) {
        $id = base64url_decode($id);

        $vendor = Vendor::find($id);

        return view('admin.vendors.edit', ['vendor' => $vendor]);
    }

    /** 
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = null) {
        $id = base64url_decode($id);

        $vendor = Vendor::find($id);
        $update_arr = [
            'name' => $request->input('name'),
            'mobile' => $request->input('mobile'),
            'company_name' => $request->input('company_name'),
            'address' => $request->input('address'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'country' => $request->input('country'),
            'pin' => $request->input('pin')
        ];
        $update = Vendor::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/vendors')->with('success', 'Vendor updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating Vendor');
    }

    public function change_vendor_status(Request $request) {
        $id = $request->input('id');
        $status = $request->input('action');
        $update = Vendor::find($id)->update(['status' => $status]);
        if ($update) {
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating status']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

}
