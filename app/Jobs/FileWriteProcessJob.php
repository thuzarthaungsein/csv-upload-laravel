<?php

namespace App\Jobs;

use App\Models\CsvFile;
use App\Models\Progress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Writer;

class FileWriteProcessJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private $fileContents,
        private $cleanedPath = '',
        private CsvFile $newCsvFile,
        private Progress $progress,
        private bool $isLastJob = false
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $content = $this->fileContents ?? [];
        $writer = Writer::createFromPath(storage_path('app/' . $this->cleanedPath), 'a');

        // clean non utf8 chars and write
        foreach ($content as $key => $row) {
            $cleanedRow = array_map(function ($value) {
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                return preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value);
            }, $row);
            $writer->insertOne($cleanedRow);
        }

        if($this->isLastJob) {
            // chunk cleaned file to small job for inserting
            ChunkFileJob::dispatch($this->cleanedPath, $this->newCsvFile, $this->progress);
        }
    }
}
