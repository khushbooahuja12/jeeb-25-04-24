<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;

class SettingController extends CoreApiController
{

    protected function index(Request $request)
    {
        $setting = \App\Model\AdminSetting::get();

        if ($setting->count()) {
            $settingArr = [];
            foreach ($setting as $key => $value) {
                $settingArr[$value->key] = $value->value;

                if ($value->key == 'warehouse_location') {
                    $locArr = explode('|', $value->value);

                    $settingArr[$value->key] = $locArr[0];
                }
            }
        }
        return view('admin.setting.index', ['setting' => $settingArr]);
    }

    protected function update_setting(Request $request)
    {
        \App\Model\AdminSetting::where('key', '=', 'minimum_purchase_amount')->update([
            'value' => $request->input('minimum_purchase_amount')
        ]);

        \App\Model\AdminSetting::where('key', '=', 'minimum_purchase_amount_for_free_delivery')->update([
            'value' => $request->input('minimum_purchase_amount_for_free_delivery')
        ]);

        \App\Model\AdminSetting::where('key', '=', 'delivery_cost')->update([
            'value' => $request->input('delivery_cost')
        ]);

        \App\Model\AdminSetting::where('key', '=', 'packing_time')->update([
            'value' => $request->input('packing_time')
        ]);

        \App\Model\AdminSetting::where('key', '=', 'minimum_purchase_amount_instant')->update([
            'value' => $request->input('minimum_purchase_amount_instant')
        ]);

        \App\Model\AdminSetting::where('key', '=', 'minimum_purchase_amount_instant_for_free_delivery')->update([
            'value' => $request->input('minimum_purchase_amount_instant_for_free_delivery')
        ]);

        \App\Model\AdminSetting::where('key', '=', 'delivery_cost_instant')->update([
            'value' => $request->input('delivery_cost_instant')
        ]);

        \App\Model\AdminSetting::where('key', '=', 'packing_time_instant')->update([
            'value' => $request->input('packing_time_instant')
        ]);

        \App\Model\AdminSetting::where('key', '=', 'warehouse_location')->update([
            'value' => $request->input('warehouse_location') . '|' . $request->input('latitude') . '|' . $request->input('longitude')
        ]);

        \App\Model\AdminSetting::where('key', '=', 'facebook')->update([
            'value' => $request->input('facebook')
        ]);
        \App\Model\AdminSetting::where('key', '=', 'instagram')->update([
            'value' => $request->input('instagram')
        ]);
        \App\Model\AdminSetting::where('key', '=', 'youtube')->update([
            'value' => $request->input('youtube')
        ]);
        \App\Model\AdminSetting::where('key', '=', 'linkedin')->update([
            'value' => $request->input('linkedin')
        ]);
        \App\Model\AdminSetting::where('key', '=', 'snapchat')->update([
            'value' => $request->input('snapchat')
        ]);
        \App\Model\AdminSetting::where('key', '=', 'twitter')->update([
            'value' => $request->input('twitter')
        ]);
        \App\Model\AdminSetting::where('key', '=', 'referral_bonus')->update([
            'value' => $request->input('referral_bonus')
        ]);
        \App\Model\AdminSetting::where('key', '=', 'max_order_amount_for_referral')->update([
            'value' => $request->input('max_order_amount_for_referral')
        ]);

        return redirect('admin/settings')->with('success', 'Setting updated !');
    }

    protected function update_maintenance_setting(Request $request)
    {
        \App\Model\AdminSetting::where('key', '=', 'under_maintenance')->update([
            'value' => $request->input('under_maintenance')
        ]);
        \App\Model\AdminSetting::where('key', '=', 'maintenance_title')->update([
            'value' => $request->input('maintenance_title')
        ]);
        \App\Model\AdminSetting::where('key', '=', 'maintenance_desc')->update([
            'value' => $request->input('maintenance_desc')
        ]);

        return redirect('admin/settings')->with('success', 'Setting updated !');
    }
}
