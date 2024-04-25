<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OrderAssignment extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:orderassignment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assigning order to driver';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $setting = \App\Model\AdminSetting::where('key', 'warehouse_location')
                ->first();
        $setting_arr = explode("|", $setting->value);

        $today_delivery_slots = \App\Model\DeliverySlot::where('date', '=', \Carbon\Carbon::now()->format('Y-m-d'))
                ->where('from', '>', \Carbon\Carbon::now()->subMinutes(62)->format('H:i:s'))
                ->orderBy('from', 'asc')
                ->get();
        if ($today_delivery_slots->count()) {
            foreach ($today_delivery_slots as $key => $value) {
                $curr_time = time();
                $slot_time = strtotime($value->date . ' ' . $value->from);

                $diff = ($slot_time - $curr_time) / 60;

                if ($diff > 89 && $diff <= 90) {
                    $orders = \App\Model\Order::join('order_address', 'order_address.fk_order_id', '=', 'orders.id')
                            ->join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                            ->select("orders.*")
                            ->selectRaw("(
                                6371 * ACOS(
                                    COS(RADIANS('" . $setting_arr[1] . "')) * COS(
                                        RADIANS(order_address.latitude)
                                    ) * COS(
                                        RADIANS(order_address.longitude) - RADIANS('" . $setting_arr[2] . "')
                                    ) + SIN(RADIANS('" . $setting_arr[1] . "')) * SIN(
                                        RADIANS(order_address.latitude)
                                    )
                                )
                            ) AS distance")
                            ->where('order_delivery_slots.delivery_date', '=', $value->date)
                            ->where('order_delivery_slots.delivery_slot', '=', DB::raw("CONCAT(LEFT('" . $value->from . "' , 5), '-',LEFT('" . $value->to . "' , 5))"))
                            ->where('orders.status', '=', 1)
                            ->orderBy('distance', 'asc')
                            ->get();

                    if ($orders->count()) {
                        foreach ($orders as $key => $value) {
                            $driver = \App\Model\Driver::where('is_available', '=', 1)->first();

                            $updateArr = [
                                'fk_driver_id' => $driver ? $driver->id : 0,
                                'status' => 0
                            ];
                            \App\Model\OrderDriver::where(['fk_order_id' => $value->id])->update($updateArr);

                            if ($driver) {
                                updateDriverAvailability($driver->id);
                            }
                        }
                    }
                }

                if ($diff > 74 && $diff <= 75) {
                    $orders = \App\Model\Order::join('order_address', 'order_address.fk_order_id', '=', 'orders.id')
                            ->join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                            ->select("orders.*")
                            ->selectRaw("(
                                6371 * ACOS(
                                    COS(RADIANS('" . $setting_arr[1] . "')) * COS(
                                        RADIANS(order_address.latitude)
                                    ) * COS(
                                        RADIANS(order_address.longitude) - RADIANS('" . $setting_arr[2] . "')
                                    ) + SIN(RADIANS('" . $setting_arr[1] . "')) * SIN(
                                        RADIANS(order_address.latitude)
                                    )
                                )
                            ) AS distance")
                            ->where('order_delivery_slots.delivery_date', '=', $value->date)
                            ->where('order_delivery_slots.delivery_slot', '=', DB::raw("CONCAT(LEFT('" . $value->from . "' , 5), '-',LEFT('" . $value->to . "' , 5))"))
                            ->where('orders.status', '=', 1)
                            ->orderBy('distance', 'asc')
                            ->get();

                    if ($orders->count()) {
                        foreach ($orders as $key => $value) {

                            $isDriverAssigned = \App\Model\OrderDriver::where('fk_order_id', '=', $value->id)
                                    ->where('fk_driver_id', '!=', 0)
                                    ->first();

                            if ($isDriverAssigned) {
                                \App\Model\Order::where(['id' => $value->id])
                                        ->update(['status' => 2]);

                                \App\Model\OrderStatus::create([
                                    'fk_order_id' => $value->id,
                                    'status' => 2
                                ]);

                                \App\Model\OrderDriver::where('fk_order_id', '=', $value->id)->update(['status' => 1]);
                            }
                        }
                        /* Sending notification to vendor about new order assigned to the driver */
                        event(new \App\Events\MyEvent('New order assigned'));
                    }
                }

                if ($diff > -60 && $diff <= -59) {
                    // condition to check if all drivers become free within 1 hr of slot started
                    $totalBusyDrivers = \App\Model\Driver::where(['is_available' => 0])->count();
                    if ($totalBusyDrivers == 0) {
                        $orders = \App\Model\Order::join('order_address', 'order_address.fk_order_id', '=', 'orders.id')
                                ->join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                                ->select("orders.*")
                                ->selectRaw("(
                                6371 * ACOS(
                                    COS(RADIANS('" . $setting_arr[1] . "')) * COS(
                                        RADIANS(order_address.latitude)
                                    ) * COS(
                                        RADIANS(order_address.longitude) - RADIANS('" . $setting_arr[2] . "')
                                    ) + SIN(RADIANS('" . $setting_arr[1] . "')) * SIN(
                                        RADIANS(order_address.latitude)
                                    )
                                )
                            ) AS distance")
                                ->where('order_delivery_slots.delivery_date', '=', $value->date)
                                ->where('order_delivery_slots.delivery_slot', '=', date('H:i', strtotime($value->from)) . '-' . date('H:i', strtotime($value->to)))
                                ->where('orders.status', '=', 1)
                                ->orderBy('distance', 'asc')
                                ->get();

                        if ($orders->count()) {
                            foreach ($orders as $key => $value) {
                                $driver = \App\Model\Driver::where('is_available', '=', 1)->first();

                                $update_arr = [
                                    'fk_driver_id' => $driver ? $driver->id : 0,
                                    'status' => $driver ? 1 : 0,
                                ];
                                \App\Model\OrderDriver::where(['fk_order_id' => $value->id])->update($update_arr);

                                if ($driver) {
                                    \App\Model\Order::find($value->id)->update(['status' => 2]);
                                    \App\Model\OrderStatus::create([
                                        'fk_order_id' => $value->id,
                                        'status' => 2
                                    ]);

                                    updateDriverAvailability($driver->id);
                                }
                            }
                            /* Sending notification to vendor about new order assigned to the driver */
                            event(new \App\Events\MyEvent('New order assigned'));
                        }
                    }
                }
            }
        }
    }

}
