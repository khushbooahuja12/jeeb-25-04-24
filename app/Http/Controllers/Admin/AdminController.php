<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Admin;
use App\Model\Role;
use App\Model\Store;
use App\Model\Company;

class AdminController extends CoreApiController
{
    public function forbidden(Request $request){
        return view('admin.forbidden');
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $administrators = Admin::with('getRole')->where('name', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            $administrators = Admin::with('getRole')->sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $administrators->appends(['filter' => $filter]);

        return view('admin.administrator.index', ['administrators' => $administrators, 'filter' => $filter]);
    }

    public function create(Request $request)
    {
        $roles = Role::where('status',1)->get();
        $stores = Store::where('status',1)->get();
        $companies = Company::where('deleted',0)->get();
        return view('admin.administrator.create',['roles' => $roles, 'stores' => $stores, 'companies' => $companies]);
    }

    public function store(Request $request)
    {
        $exists = Admin::where('email', $request->input('email'))->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'An Administrator alreday exist with this email');
        } else {
            $insert_arr = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->password),
                'fk_role_id' => $request->fk_role_id,
                'fk_store_id' => $request->fk_store_id,
                'fk_company_id' => $request->fk_company_id
            ];

            $create = Admin::create($insert_arr);
            if($create){
                return redirect('admin/administrators')->with('success', 'Administrator created successfully');
            }

            return back()->withInput()->with('error', 'Error while creating administrator');
            
        }
        
    }

    public function edit($id)
    {
        $administrator = Admin::with('getRole')->find($id);
        $roles = Role::where('status',1)->get();
        $stores = Store::where('status',1)->get();
        $companies = Company::where('deleted',0)->get();
        return view('admin.administrator.edit', ['administrator' => $administrator, 'roles' => $roles, 'stores' => $stores, 'companies'=> $companies]);
    }

    public function update(Request $request, $id)
    {
        $id = base64url_decode($id);
        $exists = Admin::where(['email' => $request->input('email'),['id','!=',$id]])->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'An Administrator alreday exist with this email');
        } else {

            $update_arr = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'fk_role_id' => $request->fk_role_id,
                'fk_store_id' => $request->fk_store_id,
                'fk_company_id' => $request->fk_company_id
            ];

            if(isset($request->password)){
                $update_arr['password'] = bcrypt($request->password);
            }

            $administrator = Admin::find($id);
            $administrator->update($update_arr);

            return redirect('admin/administrators')->with('success', 'Administrator created successfully');
            
        }

        return back()->withInput()->with('error', 'Error while creating administrator');
        
    }

    public function delete($id)
    {
        $id = base64url_decode($id);
        $administrator = Admin::find($id);
        if($administrator->delete()){
            return redirect('admin/administrators')->with('success', 'Administrator deleted successfully');
        }
        
        return back()->withInput()->with('error', 'Error while deleteing administrator');
    }
}
