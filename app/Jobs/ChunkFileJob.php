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
use League\Csv\Reader;

class ChunkFileJob implements ShouldQueue
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
        private CsvFile $newCsvFile,
        private Progress $progress,
        private $cleanedPath = '',
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $csv = Reader::createFromPath(storage_path('app/' . $this->cleanedPath), 'r');
        $csv->setHeaderOffset(0); //may contain duplicates
        $interestedHeaders = [
            'UNIQUE_KEY',
            'PRODUCT_TITLE',
            'PRODUCT_DESCRIPTION',
            'STYLE#',
            'SANMAR_MAINFRAME_COLOR',
            'SIZE',
            'COLOR_NAME',
            'PIECE_PRICE',
        ];

        $headerFields = [];
        $headers = $csv->getHeader();
        foreach ($headers as $k => $v) {
            if(in_array($v, $interestedHeaders)) {
                $headerFields[$v] = $k;
            } else {
                continue;
            }

            $check = array_keys($headerFields);
            $result = array_diff($interestedHeaders, $check);
            if(count($result) < 1) {
                break;
            }
        }

        $csvNew = Reader::createFromPath(storage_path('app/' . $this->cleanedPath), 'r');
        $chunkSize = 50;

        $chunks = [];
        foreach ($csvNew->getRecords() as $row) {
            $chunks[] = $row;

            if (count($chunks) === $chunkSize) {
                // Dispatch a single job for this chunk
                InsertProcessJob::dispatch($chunks, $csvNew->count(), $this->progress, $this->newCsvFile, $headerFields)->onQueue('upload');
                $chunks = [];
            }
        }

        // Dispatch any remaining data as a final job
        if (!empty($chunks)) {
            InsertProcessJob::dispatch($chunks, $csvNew->count(), $this->progress, $this->newCsvFile, $headerFields, true)->onQueue('upload');
        }
    }
}
