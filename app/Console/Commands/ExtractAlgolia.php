<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Algolia\AlgoliaSearch\SearchClient;
use Illuminate\Support\Facades\Http;

class ExtractAlgolia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExtractAlgolia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To extract Algolia Data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function extract_algolia($page) {
        
        // Create and store the updated CSV file
        $json_url_base = "algolia/";
        $temp_file_name = 'backup_base_products_'.$page.'.csv';
        $temp_file_store_path = storage_path('app/public/' . $json_url_base . $temp_file_name);

        // Algolia
        $client = SearchClient::create(env('ALGOLIA_APP_ID'), env('ALGOLIA_SECRET'));
        $index = $client->initIndex(env('ALGOLIA_PRODUCT_INDEX'));

        $numericFilters = [
            ['product_store_stock > 0'],
            ['fk_product_store_id != 0'],
            ['product_store_price != 0.0'],
        ];

        $res = $index->browseObjects(
            // [
            //     'browseParameters'=>[
            //         'numericFilters' => $numericFilters
            //     ]
            // ]
        );
        $items = $res;

        // Write to CSV
        // file_put_contents($temp_file_store_path, json_encode($items));
        $fp = fopen($temp_file_store_path, 'w');
        foreach ($items as $line) {
            if (is_array($line)) {
                foreach ($line as $key => $value) {
                    $line[$key] = json_encode($value, JSON_UNESCAPED_SLASHES);
                }
            }
            fputcsv($fp, $line);
        } 
        fclose($fp);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        \Log::info('php artisan command:ExtractAlgolia');

        $page = 0;
        $this->extract_algolia($page);
        
    }
}
