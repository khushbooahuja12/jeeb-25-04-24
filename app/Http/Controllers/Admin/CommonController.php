<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\DeliveryArea;
use App\Model\DeliverySlot;

class CommonController extends Controller {

    protected function delivery_area(Request $request) {
        $delivery_area = DeliveryArea::orderBy('id', 'desc')->get();
        return view('admin.common.delivery_area', [
            'delivery_area' => $delivery_area
        ]);
    }

    protected function update_delivery_area(Request $request) {
        if ($request->input('id') != '') {
            $update = DeliveryArea::where(['id' => $request->input('id')])->update([
                'location' => $request->input('location'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'radius' => $request->input('radius'),
                'blocked_timeslots' => $request->input('blocked_timeslots')
            ]);
            $type = "edit";
            $arr = DeliveryArea::find($request->input('id'));
        } else {
            $update = DeliveryArea::create([
                        'location' => $request->input('location'),
                        'latitude' => $request->input('latitude'),
                        'longitude' => $request->input('longitude'),
                        'radius' => $request->input('radius'),
                        'blocked_timeslots' => $request->input('blocked_timeslots')
            ]);
            $type = "add";
            $arr = $update;
        }
        if ($update) {
            return response()->json([
                        'error' => false,
                        'status_code' => 200,
                        'message' => "Updated successfully!",
                        'result' => [
                            'id' => $arr->id,
                            'location' => $arr->location,
                            'latitude' => $arr->latitude,
                            'longitude' => $arr->longitude,
                            'radius' => $arr->radius,
                            'blocked_timeslots' => $arr->blocked_timeslots,
                            'type' => $type
                        ]
            ]);
        } else {
            return response()->json([
                        'error' => true,
                        'status_code' => 404,
                        'message' => "Some error found"
            ]);
        }
    }

    public function destroy_area($id = null) {
        $id = base64url_decode($id);
        $area = DeliveryArea::find($id);
        if ($area) {
            DeliveryArea::find($id)->delete();
            return redirect('admin/delivery-area')->with('success', 'Deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting');
        }
    }

    protected function create_delivery_slots(Request $request) {
        $slots = DeliverySlot::groupBy('date')->orderBy('date', 'desc')->get();
        return view('admin.common.create_delivery_slots', ['slots' => $slots]);
    }

    protected function store_delivery_slots(Request $request) {
        if ($request->input('start_time_hours')) {
            for ($i = 0; $i < count($request->input('start_time_hours')); $i++) {
                $insert_arr = [
                    'start_date' => date('Y-m-d', strtotime($request->input('start_date')[$i])),
                    'from' => $request->input('start_time_hours')[$i] . ':' . $request->input('start_time_minutes')[$i] . ':00',
                    'to' => $request->input('end_time_hours')[$i] . ':' . $request->input('end_time_minutes')[$i] . ':00',
                    'order_limit' => $request->input('order_limit')[$i],
                ];
                \App\Model\DeliverySlotSetting::create($insert_arr);
            }
            return redirect('admin/delivery-slots')->with('success', 'Slot added!');
        } else {
            return back()->withInput()->with('error', 'Error while adding slots');
        }
    }

    protected function delivery_slots(Request $request) {
        $slots = DeliverySlot::groupBy('date')->orderBy('date', 'desc')->get();

        return view('admin.common.delivery_slots', [
            'slots' => $slots
        ]);
    }

    protected function delete_slot_setting(Request $request) {
        $slot_setting = \App\Model\DeliverySlotSetting::find($request->input('id'));

        if ($slot_setting->start_date <= \Carbon\Carbon::now()->format('Y-m-d')) {
            return response()->json([
                        'error' => true,
                        'status_code' => 404,
                        'message' => "Cant delete today or past date"
            ]);
        }
        $delete = \App\Model\DeliverySlotSetting::find($request->input('id'))->delete();
        if ($delete) {
            return response()->json([
                        'error' => false,
                        'status_code' => 200,
                        'message' => "Deleted successfully!"
            ]);
        } else {
            return response()->json([
                        'error' => true,
                        'status_code' => 404,
                        'message' => "Some error found"
            ]);
        }
    }

    protected function slot_settings(Request $request) {
        $slots = \App\Model\DeliverySlotSetting::groupBy('start_date')->orderBy('start_date', 'desc')->get();

        return view('admin.common.slot_settings', [
            'slots' => $slots
        ]);
    }

    protected function edit_delivery_slots(Request $request, $id = null) {
        $id = base64url_decode($id);
        $slot = \App\Model\DeliverySlotSetting::find($id);
        return view('admin.common.edit_delivery_slots', ['slot' => $slot]);
    }

    protected function update_delivery_slots(Request $request) {
//        if ($request->input('start_time_hours')) {
//            for ($i = 0; $i < count($request->input('start_time_hours')); $i++) {
//                $insert_arr = [
//                    'start_date' => date('Y-m-d', strtotime($request->input('start_date')[$i])),
//                    'from' => $request->input('start_time_hours')[$i] . ':' . $request->input('start_time_minutes')[$i] . ':00',
//                    'to' => $request->input('end_time_hours')[$i] . ':' . $request->input('end_time_minutes')[$i] . ':00',
//                    'order_limit' => $request->input('order_limit')[$i],
//                ];
//                \App\Model\DeliverySlotSetting::create($insert_arr);
//            }
//            return redirect('admin/delivery-slots')->with('success', 'Slot added!');
//        } else {
//            return back()->withInput()->with('error', 'Error while adding slots');
//        }
    }

}
