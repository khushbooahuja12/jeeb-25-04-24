<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Jobs\UpdateBaseProductStoreIsActive;
use App\Model\StoreSchedule;
use App\Model\Store;

class StoreActivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:StoreActivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store activate and deactivate based on the day and date shedules';

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
     * Round the time.
     *
     * @return void
     */
    public function roundToNearestMinuteInterval(\DateTime $dateTime, $minuteInterval = 10)
    {
        return $dateTime->setTime(
            $dateTime->format('H'),
            round($dateTime->format('i') / $minuteInterval) * $minuteInterval,
            0
        );
    }

    /**
     * Match the time
     *
     * @return void
     */
    public function checkTimeMatched($slot)
    {
        $matched = false;
        $status = '';

        // Current time - Rounded to 10 minutes
        $current_time = date('H:i:00');
        $current_time = new \DateTime($current_time);
        $current_time_rounded = $this->roundToNearestMinuteInterval($current_time);

        // Open time - Rounded to 10 minutes
        $open_time = date($slot->from);
        $open_time = new \DateTime($open_time);
        $open_time_rounded = $this->roundToNearestMinuteInterval($open_time);

        if ($open_time_rounded == $current_time_rounded) {
            $matched = true;
            $status = 'open';
        }

        // Close time - 1 hour before and rounded to 10 minutes
        $close_time = date('H:i:00', strtotime($slot->to.' -1 hour'));
        $close_time = new \DateTime($close_time);
        $close_time_rounded = $this->roundToNearestMinuteInterval($close_time);

        if ($close_time_rounded == $current_time_rounded) {
            $matched = true;
            $status = 'close';
        }

        // $current_time_rounded_str = $current_time_rounded->format('Y-m-d H:i:s');
        // $open_time_rounded_str = $open_time_rounded->format('Y-m-d H:i:s');
        // $close_time_rounded_str = $close_time_rounded->format('Y-m-d H:i:s');
        // \Log::info($slot->fk_store_id.' current_time_rounded: '.$current_time_rounded_str.', open_time_rounded: '.$open_time_rounded_str.', close_time_rounded: '.$close_time_rounded_str);

        return [$matched,$status];
    }

    /**
     * Update the store schedule and update the products
     *
     * @return void
     */
    public function updateBaseProductsForSchedule($store_id, $status)
    {
        
        // Mark store scheduled open or close
        Store::find($store_id)->update(['schedule_active'=>$status]);
        // // Select all base products and deactivate
        $perPage = 1000; // Number of items per page
        $query = \App\Model\BaseProductStore::where(['deleted'=>0,'product_type'=>'product','fk_store_id'=>$store_id]); 

        $paginator = $query->paginate($perPage);
        $lastPage = $paginator->lastPage();

        for ($i=1; $i <= $lastPage; $i++) { 
            
            $base_products = $query->paginate($perPage, ['*'], 'page', $i);
            $base_products_arr = $base_products->map(function ($base_product) {
                if ($base_product !== null && is_object($base_product)) {
                    return [
                        'id' => $base_product->id
                    ];
                }
            })->toArray();

            // Dispatch the batch job with the array
            UpdateBaseProductStoreIsActive::dispatch($i,$base_products_arr,$store_id,$status);
            
        }

        return true;

    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Request $request)
    {

        \Log::info('php artisan command:StoreActivate');

        // Get all active stores
        $stores = Store::where(['deleted' => 0, 'status' => 1])->orderBy('id','asc')->get(['id','schedule_active']);
        foreach ($stores as $store) {

            $store_id = $store->id;
            $selected_slot = false;
            $selected_slot_status = '';
            $open_24hours = false;
            $close_24hours = false;
            $date_selection = false;

            // -----------------------------------------
            // If special date is selected
            // -----------------------------------------
            // Check special date 24 hours open
            $schedule_date_24hours = StoreSchedule::where([
                'fk_store_id' => $store_id,
                'date' => date('Y-m-d'),
                'deleted'=>0,
                '24hours_open' => 1, 
                'from'=>NULL, 
                'to'=>NULL
                ])->orderBy('id', 'desc')->first();
            if ($schedule_date_24hours) {
                $open_24hours = true;
                $selected_slot = $schedule_date_24hours;
                $date_selection = true;
            }

            // Check special date 24 hours close
            if (!$selected_slot) {
                $schedule_date_24hours = StoreSchedule::where([
                    'fk_store_id' => $store_id,
                    'date' => date('Y-m-d'),
                    'deleted'=>0,
                    '24hours_open' => 0, 
                    'from'=>NULL, 
                    'to'=>NULL
                    ])->orderBy('id', 'desc')->first();
                if ($schedule_date_24hours) {
                    $close_24hours = true;
                    $selected_slot = $schedule_date_24hours;
                    $date_selection = true;
                }
            }

            // Check special date slots available for open or close
            if (!$selected_slot) {
                $schedule_date_slots = StoreSchedule::where([
                    'fk_store_id' => $store_id,
                    'date' => date('Y-m-d'),
                    'deleted'=>0,
                    '24hours_open' => 0
                    ])->orderBy('from', 'asc')->get();
                if ($schedule_date_slots->count()) {
                    $date_selection = true;
                    foreach ($schedule_date_slots as $key => $slot) {
                        $matched = $this->checkTimeMatched($slot);
                        if ($matched[0]) {
                            $selected_slot = $slot;
                            $selected_slot_status = $matched[1];
                            break;
                        }
                    }
                }
            }

            // -----------------------------------------
            // If no date selected check for days
            // -----------------------------------------
            // Check special date 24 hours open
            if (!$selected_slot && !$date_selection) {
                $day = strtolower(date('l'));
                $schedule_date_24hours = StoreSchedule::where([
                    'fk_store_id' => $store_id,
                    'day' => $day,
                    'deleted'=>0,
                    '24hours_open' => 1, 
                    'from'=>NULL, 
                    'to'=>NULL
                    ])->orderBy('id', 'desc')->first();
                if ($schedule_date_24hours) {
                    $open_24hours = true;
                    $selected_slot = $schedule_date_24hours;
                }
            }
                
            // Check special date 24 hours close
            if (!$selected_slot && !$date_selection) {
                $schedule_date_24hours = StoreSchedule::where([
                    'fk_store_id' => $store_id,
                    'day' => $day,
                    'deleted'=>0,
                    '24hours_open' => 0, 
                    'from'=>NULL, 
                    'to'=>NULL
                    ])->orderBy('id', 'desc')->first();
                if ($schedule_date_24hours) {
                    $close_24hours = true;
                    $selected_slot = $schedule_date_24hours;
                }
            }

            // Check special date slots available for open or close
            if (!$selected_slot && !$date_selection) {
                $schedule_date_slots = StoreSchedule::where([
                    'fk_store_id' => $store_id,
                    'day' => $day,
                    'deleted'=>0,
                    '24hours_open' => 0
                    ])->orderBy('from', 'asc')->get();
                if ($schedule_date_slots->count()) {
                    foreach ($schedule_date_slots as $key => $slot) {
                        $matched = $this->checkTimeMatched($slot);
                        if ($matched[0]) {
                            $selected_slot = $slot;
                            $selected_slot_status = $matched[1];
                            break;
                        }
                    }
                }
            }

            // --------------------------------
            // Activate / Deactivate the stores
            // --------------------------------
            
            // If open for 24 hours
            if ($open_24hours && $selected_slot) {
                // Mark store scheduled open or close
                $status = 1;
                if ($store->schedule_active != $status) {
                    // Today date and cron_triggered_at
                    $today = date('Y-m-d');
                    $cron_triggered_at = date('Y-m-d', strtotime($selected_slot->cron_triggered_at));
                    if ($cron_triggered_at != $today) {
                        $this->updateBaseProductsForSchedule($store_id, $status);
                        StoreSchedule::find($selected_slot->id)->update(['cron_triggered_at'=>date('Y-m-d H:i:s')]);
                    }
                }

            }
            // If close for 24 hours
            elseif ($close_24hours && $selected_slot) {
                // Mark store scheduled open or close
                $status = 0;
                if ($store->schedule_active != $status) {
                    // Today date and cron_triggered_at
                    $today = date('Y-m-d');
                    $cron_triggered_at = date('Y-m-d', strtotime($selected_slot->cron_triggered_at));
                    if ($cron_triggered_at != $today) {
                        $this->updateBaseProductsForSchedule($store_id, $status);
                        StoreSchedule::find($selected_slot->id)->update(['cron_triggered_at'=>date('Y-m-d H:i:s')]);
                    }
                }

            }
            // If slot is selected based on date or day
            elseif ($selected_slot) {

                // Mark store scheduled open or close
                $status = $selected_slot_status=='close' ? 0 : 1;
                $this->updateBaseProductsForSchedule($store_id, $status);
                StoreSchedule::find($selected_slot->id)->update(['cron_triggered_at'=>date('Y-m-d H:i:s')]);

            } 

        // End stores loop
        }
        
        return 0;
    }

}
