<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Role;
use App\Model\Permission;
use App\Model\RolePermission;
use Str;

class RoleController extends CoreApiController
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $roles = Role::where('name', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            $roles = Role::sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $roles->appends(['filter' => $filter]);

        return view('admin.roles.index', ['roles' => $roles, 'filter' => $filter]);
    }

    public function create(Request $request)
    {
        $permissions = Permission::all();
        return view('admin.roles.create',['permissions'=> $permissions]);
    }

    public function store(Request $request)
    {
        if($request->input('permissions')){

            $role = Role::where('name',$request->input('name'))->first();
            if($role){
                return back()->withInput()->with('error', 'A role with this name already exists');
            }

            $slug = Str::slug($request->input('name'), '-');
            
            for($i = 1; $i < $i + 1; $i++) {
                $check_slug_exist = Role::where(['slug' => $slug])->first();
                if($check_slug_exist) {
                    $slug = $slug.$i;
                } else {
                    break;
                }
            }
            
            $insert_arr = [
                'name' => $request->input('name'),
                'slug' => $slug,
                'description' => $request->input('description'),
            ];

            $create = Role::create($insert_arr);
            $create->permissions()->sync($request->input('permissions'));  
            if ($create) {
                return redirect('admin/roles')->with('success', 'Role added successfully');
            }
            return back()->withInput()->with('error', 'Error while adding Role');
        }else{
            return back()->withInput()->with('error', 'Please Select at least one permission for creating this role');
        }

    }

    public function edit($id)
    {
        $role = Role::find(base64url_decode($id));
        $permissions = Permission::all();
        $rol_permissions = RolePermission::where('fk_role_id',$role->id)->pluck('fk_permission_id')->toArray();
        return view('admin.roles.edit',['role'=> $role, 'permissions' => $permissions, 'rol_permissions' => $rol_permissions]);
    }

    public function update(Request $request, $id)
    {
        $insert_arr = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ];

        $role = Role::find(base64url_decode($id));
        $role->update($insert_arr);
        $role->permissions()->sync($request->input('permissions'));
        if ($role) {
            return redirect('admin/roles')->with('success', 'Role updated successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Role');
            

    }

    public function delete($id)
    {
        $role = Role::find(base64url_decode($id));
        $role->permissions()->detach();
        $role->delete();
        if ($role) {
            return redirect('admin/roles')->with('success', 'Role Deleted successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Role');

    }
}
