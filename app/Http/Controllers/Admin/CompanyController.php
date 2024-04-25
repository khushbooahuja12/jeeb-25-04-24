<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Company;
use App\Model\Role;
use App\Model\Admin;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter');
        if (!empty($filter)) {
            $companies = Company::where('name', 'like', '%' . $filter . '%')
                ->where('deleted', '=', 0)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            $companies = Company::where('deleted', '=', 0)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $companies->appends(['filter' => $filter]);

        return view('admin.companies.index', ['companies' => $companies, 'filter' => $filter]);
    }

    public function create(Request $request)
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $company = Company::where(['name' => $request->input('name'),'deleted'=> 0])->first();
        if ($company) {
            return back()->withInput()->with('error', 'Company already exist');
        } else {
            $insert_arr = [
                'name' => $request->input('name'),
                'notes' => $request->input('notes'),
            ];

            $add = Company::create($insert_arr);
            if ($add) {
                $role = Role::where('slug','stores-master-panel-admin')->first();
                $insert_arr = [
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => bcrypt($request->input('password')),
                    'fk_role_id' => $role->id,
                    'fk_company_id' => $add->id
                ];

                Admin::create($insert_arr);

                return redirect('admin/companies')->with('success', 'Company added successfully');
            }
            return back()->withInput()->with('error', 'Error while adding company');
        }
    }

    public function edit($id)
    {
        $id = base64url_decode($id);
        $company = Company::select('companies.*','admins.email')->leftJoin('admins','companies.id','=','admins.fk_company_id')->where('companies.id','=',$id)->first();
        return view('admin.companies.edit',['company' => $company]);
    }

    public function update(Request $request, $id)
    {
        $id = base64url_decode($id);
        $company = Company::where(['name' => $request->input('name'),'deleted'=> 0])->where('id','!=',$id)->first();
        if ($company) {
            return back()->withInput()->with('error', 'Company already exist');
        } else {
            
            $company = Company::find($id);

            $update_arr = [
                'name' => $request->input('name'),
                'notes' => $request->input('notes'),
            ];

            $update = $company->update($update_arr);
            if ($update) {

                $admin = Admin::where('email',$request->input('email'))->first();
                $role = Role::where('slug','stores-master-panel-admin')->first();
                $insert_arr = [
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'fk_company_id' => $id,
                    'fk_role_id' => $role->id
                ];

                if(isset($request->password)){
                    $insert_arr['password'] = bcrypt($request->password);
                }

                if($admin == null){

                    Admin::create($insert_arr);
                }else{
                    $admin->update($insert_arr);
                }

                return redirect('admin/companies')->with('success', 'Company updated successfully');
            }
            return back()->withInput()->with('error', 'Error while updating company');
        }
    }

    public function destroy($id)
    {
        $id = base64url_decode($id);
        $update = Company::find($id)->update(['deleted' => 1]);
        if ($update) {
            return redirect('admin/companies')->with('success', 'Company deleted successfully');
        }
        return back()->withInput()->with('error', 'Error while deleting company');
        
    }
}
