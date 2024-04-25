<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Driver;
use App\Model\Store;
use App\Model\InstantStoreGroup;
use App\Model\InstantStoreGroupStore;
use App\Model\DriverInstantGroup;
use App\Model\DriverGroup;
use App\Model\StoreGroup;
use Illuminate\Support\Facades\Hash;

class DriverController extends CoreApiController
{

    protected function index(Request $request)
    {
        $drivers = Driver::where(['deleted' => 0])->orderBy('id', 'desc')->get();
        $vehicles = \App\Model\Vehicle::orderBy('id', 'desc')->get();
        return view('admin.drivers.index', [
            'drivers' => $drivers,
            'vehicles' => $vehicles
        ]);
    }

    protected function create(Request $request)
    {
        $stores = Store::where(['deleted' => 0,'status'=>1])->orderBy('name', 'asc')->get();
        return view('admin.drivers.create', ['stores' => $stores]);
    }

    protected function store(Request $request)
    {
        $driver = Driver::where('email', $request->input('email'))->get();
        if (count($driver) > 0) {
            return back()->withInput()->with('error', 'Email already exist');
        } else {
            $insert_arr = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'country_code' => $request->input('country_code'),
                'mobile' => $request->input('mobile'),
                'driving_licence_no' => $request->input('driving_licence_no'),
                'status' => 1,
                'is_available' => 1,
                'fk_store_id' => $request->input('fk_store_id'),
                'role' => $request->input('driver_role')
            ];
            if ($request->hasFile('driver_image')) {
                $path = "/images/driver_images/";
                $check = $this->uploadFile($request, 'driver_image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $insert_arr['driver_image'] = $returnArr->id;
                endif;
            }
            if ($request->hasFile('driving_licence')) {
                $path = "/images/driver_images/";
                $check = $this->uploadFile($request, 'driving_licence', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $insert_arr['driving_licence'] = $returnArr->id;
                endif;
            }
            if ($request->hasFile('national_id')) {
                $path = "/images/driver_images/";
                $check = $this->uploadFile($request, 'national_id', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $insert_arr['national_id'] = $returnArr->id;
                endif;
            }

            $create = Driver::create($insert_arr);
            if ($create) {
                
                if($request->input('driver_role') == 2){

                    if($request->input('driver_group_store')){
                        $stores = $request->input('driver_group_store');
                        foreach ($stores as $key => $value) {
                            DriverInstantGroup::create([
                                'fk_driver_id' => $create->id,
                                'fk_group_id' => $request->input('driver_group'),
                                'fk_store_id' => $value
                            ]);
                        }
                    }

                }elseif($request->input('driver_role') == 0){
                    
                    if($request->input('driver_group_store')){

                        $stores = $request->input('driver_group_store');
                        foreach ($stores as $key => $value) {
                            DriverGroup::create([
                                'fk_driver_id' => $create->id,
                                'group_id' => $request->input('driver_group'),
                                'fk_store_id' => $value
                            ]);
                        }
                    }
                }elseif($request->input('driver_role') == 0){
                    
                    if($request->input('fk_store_id')){

                        DriverGroup::create([
                            'fk_driver_id' => $create->id,
                            'group_id' => 0,
                            'fk_store_id' => $request->input('fk_store_id')
                        ]);
                    }
                }
                
                return redirect('admin/drivers')->with('success', 'Driver added successfully');
            }
            return back()->withInput()->with('error', 'Error while adding driver');
        }
    }

    protected function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $driver = Driver::find($id);

        $update_arr = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'country_code' => $request->input('country_code'),
            'mobile' => $request->input('mobile'),
            'national_id' => $request->input('national_id'),
            'driving_licence_no' => $request->input('driving_licence_no'),
            'fk_store_id' => $request->input('fk_store_id'),
            'role' => $request->input('driver_role')
        ];
        if ($request->hasFile('driver_image')) {
            $path = "/images/driver_images/";
            $check = $this->uploadFile($request, 'driver_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($driver->driver_image != '') {
                    $destinationPath = public_path("images/driver_images/");
                    if (!empty($driver->getDriverImage) && file_exists($destinationPath . $driver->getDriverImage->file_name)) {
                        unlink($destinationPath . $driver->getDriverImage->file_name);
                    }
                    $returnArr = $this->updateFile($req, $driver->driver_image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['driver_image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('driving_licence')) {
            $path = "/images/driver_images/";
            $check = $this->uploadFile($request, 'driving_licence', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($driver->driving_licence != '') {
                    $destinationPath = public_path("images/driver_images/");
                    if (!empty($driver->getDrivingLicence) && file_exists($destinationPath . $driver->getDrivingLicence->file_name)) {
                        unlink($destinationPath . $driver->getDrivingLicence->file_name);
                    }
                    $returnArr = $this->updateFile($req, $driver->driving_licence);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['driving_licence'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('national_id')) {
            $path = "/images/driver_images/";
            $check = $this->uploadFile($request, 'national_id', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($driver->national_id != '') {
                    $destinationPath = public_path("images/driver_images/");
                    if (!empty($driver->getNationalId) && file_exists($destinationPath . $driver->getNationalId->file_name)) {
                        unlink($destinationPath . $driver->getNationalId->file_name);
                    }
                    $returnArr = $this->updateFile($req, $driver->national_id);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['national_id'] = $returnArr->id;
            endif;
        }

        $update = Driver::find($id)->update($update_arr);
        if ($update) {
            if($request->input('driver_role') == 2){

                $instant_driver_group = DriverInstantGroup::where('fk_driver_id',$driver->id)->delete();
                $instant_driver_group_stores = $request->input('instant_driver_group_store');
                
                foreach ($instant_driver_group_stores as $key => $value) {
                    DriverInstantGroup::create([
                        'fk_driver_id' => $driver->id,
                        'fk_group_id' => $request->input('instant_driver_group'),
                        'fk_store_id' => $value
                    ]);

                    InstantStoreGroupStore::create([
                        'fk_group_id' => $driver->id,
                        'fk_store_id' => $value
                    ]);
                }
                
                
            }elseif($request->input('driver_role') == 0){

                $collector_driver_group_stores = $request->input('collector_driver_group_store');
                
                $collector_driver_assined_groups = DriverGroup::where('fk_driver_id', $driver->id)->get();

                if ($collector_driver_assined_groups->count() > 0) {
                    // Delete existing collector driver groups
                    $collector_driver_assined_groups->each(function ($collector_driver_assined_groups) {
                        $collector_driver_assined_groups->delete();
                    });
                }

                if ($collector_driver_group_stores) {
                    foreach ($collector_driver_group_stores as $key => $collector_driver_group_store) {
                        DriverGroup::create([
                            'fk_driver_id' => $driver->id,
                            'group_id' => $request->input('collector_driver_group'),
                            'fk_store_id' => $collector_driver_group_store,
                        ]);
                    }
                }
            }
            return redirect('admin/drivers')->with('success', 'Driver updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating driver');
    }

    protected function edit(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $driver = Driver::find($id);
        $stores = Store::where(['deleted' => 0,'status'=>1])->orderBy('name', 'asc')->get();

        $collector_driver_store_groups = StoreGroup::where(['deleted' => 0,'status'=>1])->groupBy('group_id')->get();
        $collector_driver_group = DriverGroup::select('group_id')->where('fk_driver_id',$id)->groupBy('group_id')->first();
        $collector_driver_stores = DriverGroup::where('fk_driver_id',$id)->pluck('fk_store_id')->toArray();

        $instant_driver_store_group_stores = [];
        $instant_driver_group = DriverInstantGroup::where('fk_driver_id',$driver->id)->first();
        if ($instant_driver_group) {
            $instant_driver_store_group_stores = DriverInstantGroup::where('fk_group_id',$instant_driver_group->fk_group_id)
                ->get()->pluck('fk_store_id')->toArray();
        }
        $instant_driver_store_groups = InstantStoreGroup::where(['status' => 1, 'deleted' => 0])->get();

        return view('admin.drivers.edit', [
            'driver' => $driver,
            'stores' => $stores,

            'collector_driver_store_groups' => $collector_driver_store_groups,
            'collector_driver_group' => $collector_driver_group,
            'collector_driver_stores' => $collector_driver_stores,
            
            'instant_driver_store_groups' => $instant_driver_store_groups,
            'instant_driver_group' => $instant_driver_group,
            'instant_driver_store_group_stores' => $instant_driver_store_group_stores,
            
        ]);
        
    }

    protected function show($id = null)
    {
        $id = base64url_decode($id);
        $driver = Driver::find($id);
        return view('admin.drivers.show', ['driver' => $driver]);
    }

    public function destroy($id)
    {
        $id = base64url_decode($id);
        $update = Driver::find($id)->update(['deleted' => 1]);
        if ($update) {
            return redirect('admin/drivers')->with('success', 'Driver deleted successfully');
        }
        return back()->withInput()->with('error', 'Error while deleting driver');
    }

    protected function change_driver_status(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('action');
        $update = Driver::find($id)->update(['status' => $status]);
        if ($update) {
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating status']);
        }
    }

    protected function vehicles(Request $request)
    {
        $vehicles = \App\Model\Vehicle::orderBy('id', 'desc')->get();

        return view('admin.drivers.vehicles', [
            'vehicles' => $vehicles
        ]);
    }

    protected function create_vehicle()
    {
        return view('admin.drivers.create_vehicle');
    }

    protected function store_vehicle(Request $request)
    {
        $insert_arr = [
            'vehicle_number' => $request->input('vehicle_number'),
            'vehicle_type' => $request->input('vehicle_type'),
            'vehicle_capacity' => $request->input('vehicle_capacity'),
        ];
        $add = \App\Model\Vehicle::create($insert_arr);

        if ($add) {
            return redirect('admin/vehicles')->with('success', 'Vehicle added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding vehicle');
    }

    protected function edit_vehicle(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $vehicle = \App\Model\Vehicle::find($id);
        return view('admin.drivers.edit_vehicle', ['vehicle' => $vehicle]);
    }

    protected function update_vehicle(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $update_arr = [
            'vehicle_number' => $request->input('vehicle_number'),
            'vehicle_type' => $request->input('vehicle_type'),
            'vehicle_capacity' => $request->input('vehicle_capacity'),
        ];
        $update = \App\Model\Vehicle::find($id)->update($update_arr);

        if ($update) {
            return redirect('admin/vehicles')->with('success', 'Vehicle updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating vehicle');
    }

    protected function update_driver_vehicle(Request $request)
    {
        $exist = Driver::where(['fk_vehicle_id' => $request->input('id')])->first();
        if ($request->input('id') != '' && $exist) {
            return response()->json([
                'error' => true,
                'status_code' => 201,
                'message' => "This vehicle already assigned to someone else"
            ]);
        }
        $update = Driver::find($request->input('driver_id'))->update([
            'fk_vehicle_id' => $request->input('id')
        ]);
        if ($update) {
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Vehicle updated successfully!"
            ]);
        } else {
            return response()->json([
                'error' => true,
                'status_code' => 105,
                'message' => "Some error found!"
            ]);
        }
    }

    protected function get_store_groups(Request $request)
    {
        $driver_role = $request->input('driver_role');
        
        if($driver_role == 0){
            $store_groups = StoreGroup::where(['status' => 1, 'deleted' => 0])->groupBy('group_id')->get();
            $driver_group = DriverGroup::where('fk_driver_id',$request->input('fk_driver_id'))->first();

        }elseif($driver_role == 2){
            $store_groups = InstantStoreGroup::where(['status' => 1, 'deleted' => 0])->get();
            $driver_group = DriverInstantGroup::where('fk_driver_id',$request->input('fk_driver_id'))->first();
        }

        return response()->json([
            'error' => false,
            'status_code' => 200,
            'data' => [
                'store_groups' => $store_groups,
                'driver_group' => $driver_group
            ],
        ]);
    }

    protected function get_group_stores(Request $request)
    {
        $driver_group = $request->input('driver_group');
        $driver_role = $request->input('driver_role');

        if ($driver_role == 0) {
            $group_stores = StoreGroup::join('stores', 'stores.id', '=', 'store_groups.fk_store_id')
                ->select('stores.*')
                ->where('store_groups.group_id', $driver_group)
                ->where('store_groups.deleted', 0)
                ->where('stores.status', 1)
                ->where('stores.deleted', 0)
                ->pluck('stores.id')
                ->toArray();

        } elseif ($driver_role == 2) {
            $group_stores = InstantStoreGroupStore::join('stores', 'instant_store_group_stores.fk_store_id', '=', 'stores.id')
                ->select('stores.*')
                ->where('instant_store_group_stores.fk_group_id', $driver_group)
                ->where('stores.status', 1)
                ->where('stores.deleted', 0)
                ->pluck('stores.id')
                ->toArray(); 
        }


        $stores = Store::select('stores.*')
                ->where('stores.status', 1)
                ->where('stores.deleted', 0)
                ->get();
        
        
        return response()->json([
            'error' => false,
            'status_code' => 200,
            'data' => array(
                'stores' => $stores, 
                'group_stores' => $group_stores
            )
        ]);
    }
}
