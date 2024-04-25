<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddNewSlot extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:addnewslot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adding new slots for next week';

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
//        $slots = \App\Model\DeliverySlot::where('date', '=', \Carbon\Carbon::now()->subDays(1)->format('Y-m-d'))
//                ->orderBy('id', 'asc')
//                ->get();
//        if ($slots->count()) {
//            for ($i = 2; $i < 10; $i++) {
//                foreach ($slots as $key => $value) {
//                    $insert_arr = [
//                        'date' => \Carbon\Carbon::now()->addDays($i)->format('Y-m-d'),
//                        'from' => $value->from,
//                        'to' => $value->to
//                    ];
//                    \App\Model\DeliverySlot::create($insert_arr);
//                }
//            }
//        }

        $slot_settings = \App\Model\DeliverySlotSetting::where('start_date', '=', \Carbon\Carbon::now()->format('Y-m-d'))
                ->orderBy('start_date', 'asc')
                ->get();
        if ($slot_settings->count()) {
            foreach ($slot_settings as $key => $value) {
                $insert_arr = [
                    'date' => \Carbon\Carbon::now()->addDays(3)->format('Y-m-d'),
                    'from' => $value->from,
                    'to' => $value->to,
                    'order_limit' => $value->order_limit
                ];
                \App\Model\DeliverySlot::create($insert_arr);
            }
        } else {
            $delivery_slot = \App\Model\DeliverySlot::where('date', '=', \Carbon\Carbon::now()->addDays(2)->format('Y-m-d'))
                    ->orderBy('date', 'desc')
                    ->get();
            if ($delivery_slot->count()) {
                foreach ($delivery_slot as $key => $value) {
                    $insert_arr = [
                        'date' => \Carbon\Carbon::now()->addDays(3)->format('Y-m-d'),
                        'from' => $value->from,
                        'to' => $value->to,
                        'order_limit' => $value->order_limit
                    ];
                    \App\Model\DeliverySlot::create($insert_arr);
                }
            }
        }
    }

}
