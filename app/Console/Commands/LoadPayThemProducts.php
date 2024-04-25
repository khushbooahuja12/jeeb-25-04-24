<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LoadPayThemProducts extends Command
{
    use \App\Http\Traits\PayThem;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:LoadPayThemProducts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $products = $this->paythem_call_api('get_ProductList');

        if($products){
            $this->update_paythem_product($products);
        }
        
        
    }
}
