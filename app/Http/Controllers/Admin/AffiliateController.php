<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Affiliate;

class AffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ref_url_base = env('APP_URL','https://jeeb.tech/').'download-app?ref_code=';
        // Affiliates
        $affiliates = Affiliate::where(['deleted'=>0])
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('admin.affiliates.index', ['affiliates' => $affiliates, 'ref_url_base' => $ref_url_base]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $ref_url_base = env('APP_URL','https://jeeb.tech/').'download-app?ref_code=';
        // generate unique affiliate code
        $affiliate_last_id = Affiliate::orderBy('id', 'DESC')->first();
        $affiliate_last_id = $affiliate_last_id ? $affiliate_last_id->id : 0;
        $affiliate_code = bin2hex(random_bytes(10)).($affiliate_last_id+1);

        return view('admin.affiliates.create', ['affiliate_code' => $affiliate_code, 'ref_url_base' => $ref_url_base]);
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
        $request->validate([
            // 'affiliates_name' => 'required|max:255',            
            'affiliates_code' => 'required|min:10',
        ]);
        
        $insert_arr = [
            'name' => $request->input('affiliates_name'),
            'email' => $request->input('affiliates_email'),
            'mobile' => $request->input('affiliates_mobile'),
            'code' => $request->input('affiliates_code'),
            'status' => 1,
        ];

        if ($request->hasFile('affiliates_image')) {
            $fileName = $request->input('affiliates_code').'.'.$request->file('affiliates_image')->extension(); 
            $request->file('affiliates_image')->storeAs('public/referrel_qr_codes/',$fileName);
            $insert_arr['qr_code_image_url'] = 'referrel_qr_codes/'.$fileName;
        }
        $add = Affiliate::create($insert_arr);

        if ($add) {
            return redirect('admin/affiliates')->with('success', 'Affiliate added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding affiliate');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $ref_url_base = env("APP_URL").'download-app?ref_code=';
        $id = base64url_decode($id);
        $affiliates = Affiliate::find($id);
        // echo '<pre>';
        // var_dump(Affiliate::find($id)->userMobiles);
        // echo '</pre>';

        return view('admin.affiliates.detail', [
            'affiliates' => $affiliates,
            'ref_url_base' => $ref_url_base
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
        //
        $ref_url_base = env("APP_URL").'download-app?ref_code=';
        $id = base64url_decode($id);
        $affiliates = Affiliate::find($id);

        return view('admin.affiliates.edit', [
            'affiliates' => $affiliates,
            'ref_url_base' => $ref_url_base
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
        $id = base64url_decode($id);

        $affiliates = Affiliate::find($id);

        $update_arr = [
            'name' => $request->input('affiliates_name'),
            'email' => $request->input('affiliates_email'),
            'mobile' => $request->input('affiliates_mobile'),
            'code' => $request->input('affiliates_code'),
            'status' => $request->input('status'),
        ];

        if ($request->hasFile('affiliates_image')) {
            $fileName = $request->input('affiliates_code').'.'.$request->file('affiliates_image')->extension(); 
            $request->file('affiliates_image')->storeAs('public/referrel_qr_codes/',$fileName);
            $update_arr['qr_code_image_url'] = 'referrel_qr_codes/'.$fileName;
        }

        $update = Affiliate::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/affiliates')->with('success', 'Affiliate updated successfully');
        }
        return back()->withInput()->with('error', 'Error while adding brand');
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
        $ids = base64url_decode($id);

        $affiliates = Affiliate::find($ids);
        if ($affiliates) {
            Affiliate::where('id', $ids)->update(['deleted' => 1]);
            return redirect('admin/affiliates')->with('success', 'Affiliate deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting banner');
        }
    }
}
