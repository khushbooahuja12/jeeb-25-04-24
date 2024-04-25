<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackUp extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:databasebackup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for daily database backup at 01:00 AM';

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
        $filename = "jeeb-" . \Carbon\Carbon::now()->format('d-m-Y') . ".sql";
        $storage_path = storage_path();

        $host = 'jeeb.tech';
        $username = 'admin';
        $password = 'JeebDb321@';

        $command = "mysqldump --user=" . $username . " --password=" . $password . " --host=" . $host . " jeeb > '$storage_path/app/backup/'$filename";

        $returnVar = NULL;
        $output = NULL;

        exec($command, $output, $returnVar);

        $one_month_old_date = \Carbon\Carbon::now()->subMonths(1)->format('d-m-Y');

        $one_month_old_file = storage_path() . '/app/backup/jeeb-' . $one_month_old_date . '.sql';

        if($one_month_old_file){
            unlink(storage_path() . '/app/backup/jeeb-' . $one_month_old_date . '.sql');    
        }
        
    }

}
