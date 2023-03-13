<?php
namespace App\Jobs;

use DB;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Log;

class ImportUsersJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        $batchSize = 1000;
        $rowCount = 0;
        $header = null;

        $file = fopen($this->filePath, 'r');

        while (($row = fgetcsv($file, 0, ',')) !== false) {
            if (!$header) {
                $header = $row;
            } else {
                $data = array_combine($header, $row);
                $users[] = $data;

                $rowCount++;

                if ($rowCount % $batchSize == 0) {
                    DB::table('users')->upsert($users, ['name', 'email', 'password']);
                    $users = [];
                }
            }
        }

        if (!empty($users)) {
            DB::table('users')->upsert($users, ['name', 'email', 'password']);
        }

        fclose($file);

        if(file_exists($this->filePath)) {
            unlink($this->filePath);
        }

    }


}
