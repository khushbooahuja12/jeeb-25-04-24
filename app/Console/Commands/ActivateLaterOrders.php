<?php

namespace App\Console\Commands;

use App\Model\Driver;
use App\Model\Order;
use App\Model\OrderDriver;
use App\Model\OrderStatus;
use App\Model\Storekeeper;
use App\Model\StorekeeperProduct;
use Illuminate\Console\Command;

class ActivateLaterOrders extends Command
{

    use \App\Http\Traits\PushNotification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:activatelaterorders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activating orders that was placed for future time in a day';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $later_orders = Order::join('order_delivery_slots', 'orders.id', '=', 'order_delivery_slots.fk_order_id')
            ->select('orders.*', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
            ->where('orders.parent_id', '=', 0)
            ->where('orders.status', '=', 1)
            ->where('order_delivery_slots.delivery_time', '=', 2)
            ->orderBy('orders.created_at', 'asc')
            ->get();

        if ($later_orders->count()) {

            foreach ($later_orders as $key => $value) {
                $delivery_start_time = strtotime($value->delivery_date . ' ' . substr($value->later_time, 0, 5) . ':00');
                $now_sheduling_time_start = strtotime("+25 minutes");
                $now_sheduling_time_end = strtotime("+35 minutes");

                \Log::info('Sheduled order '.$value->id.' at '.date('m/d/Y H:i:s',$delivery_start_time).' checked if within '.date('m/d/Y H:i:s',$now_sheduling_time_start).' to '.date('m/d/Y H:i:s',$now_sheduling_time_end));
                
                // $diff = ($delivery_start_time - time());
                // if ($diff > 1140 && $diff < 1200) {
                
                if ($delivery_start_time <= $now_sheduling_time_end && $delivery_start_time >= $now_sheduling_time_start) {
                    
                    \Log::info($value->id." - Yes\n");
                    
                    /* -------------------- 
                    We don't assign drivers until the order is invoiced, 
                    order can be invoiced either by storekeeper or by admin 
                    --------------------- */
                    
                    // $driver = Driver::where(['fk_store_id' => $value->fk_store_id, 'is_available' => 1])->orderBy('name', 'asc')->first();
                    // OrderDriver::create([
                    //     'fk_order_id' => $value->id,
                    //     'fk_driver_id' => $driver->id ?? '0',
                    //     'status' => $driver->id ? 1 : 0
                    // ]);
                    // $driver ? Driver::find($driver->id)->update(['is_available' => 0]) : '';

                    // Order::find($value->id)->update(['status' => 2]);
                    // OrderStatus::create([
                    //     'fk_order_id' => $value->id,
                    //     'status' => 2
                    // ]);

                    /* -------------------- 
                    Assign to storekeepers
                    --------------------- */
                    \Log::info("Products\n");
                    foreach ($value->getOrderProducts as $key1 => $value1) {
                        $storekeeper = Storekeeper::join('stores', 'storekeepers.fk_store_id', '=', 'stores.id')
                            ->join('storekeeper_sub_categories', 'storekeepers.id', '=', 'storekeeper_sub_categories.fk_storekeeper_id')
                            ->select("storekeepers.*", 'storekeeper_sub_categories.fk_storekeeper_id')
                            ->groupBy('storekeeper_sub_categories.fk_sub_category_id')
                            ->where('storekeepers.deleted', '=', 0)
                            ->where('storekeepers.fk_store_id', '=', $value->fk_store_id)
                            ->where('storekeeper_sub_categories.fk_sub_category_id', '=', $value1->getProduct->fk_sub_category_id)
                            ->first();
                            \Log::info("Product ID: ".$value1->id." - Yes\n");
                        if ($storekeeper) {
                            \Log::info("Storekeeper ID: ".$storekeeper->id." - Yes\n");
                            $storekeeper_product = StorekeeperProduct::where(['fk_order_id'=>$value->id,'fk_product_id'=>$value1->fk_product_id])->first();
                            if (!$storekeeper_product) {
                                \Log::info("StorekeeperProduct added - Yes\n");
                                StorekeeperProduct::create([
                                    'fk_storekeeper_id' => $storekeeper->id ?? '',
                                    'fk_order_id' => $value->id,
                                    'fk_product_id' => $value1->fk_product_id,
                                    'status' => 0
                                ]);
                            } else {
                                \Log::info("StorekeeperProduct added - No\n");
                            }
                        }
                    }
                }
            }
        }
    }
}
