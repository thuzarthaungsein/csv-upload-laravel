<?php

namespace App\Jobs;

use App\Events\ListenUploadProgressEvent;
use App\Models\CsvEntry;
use App\Models\CsvFile;
use App\Models\Progress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class InsertProcessJob implements ShouldQueue
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
        private $chunk,
        private $totalRows,
        private Progress $progress,
        private CsvFile $new,
        private $headers
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $csv = $this->chunk;
        $oldPercentage = $this->progress->percentage ?? 0;
        $newData = [];

        foreach ($csv ?? [] as $key => $row) {
            // Process each row
            if($key == 0 && $row[0] === 'UNIQUE_KEY') {
                continue;
            }

            if($oldPercentage < 100) {
                $percentage = (($oldPercentage + ($key + 1)) / $this->totalRows) * 100;
                $percentage = $percentage > 100 ? 100 : $percentage;
                $oldPercentage = $percentage;

                $this->progress->update(['percentage' => $percentage]);
                event(new ListenUploadProgressEvent($this->new, $this->progress));
            } else {
                $this->progress->update(['percentage' => 100]);
                event(new ListenUploadProgressEvent($this->new, $this->progress));
            }

            $uniqueIdentifier = $row[$this->headers['UNIQUE_KEY']]; // Replace with the actual header name

            // Check if an entry with the same unique identifier exists
            $existingEntry = CsvEntry::where('unique_key', $uniqueIdentifier)->first();

            if ($existingEntry) {
                // Update existing entry with new data from the CSV row
                $existingEntry->unique_key = $uniqueIdentifier ?? '';
                $existingEntry->product_title = $row[$this->headers['PRODUCT_TITLE']] ?? '';
                $existingEntry->product_description = $row[$this->headers['PRODUCT_DESCRIPTION']] ?? '';
                $existingEntry->style_hash = $row[$this->headers['STYLE#']] ?? '';
                $existingEntry->sanmar_mainframe_color = $row[$this->headers['SANMAR_MAINFRAME_COLOR']] ?? '';
                $existingEntry->size = $row[$this->headers['SIZE']] ?? '';
                $existingEntry->color_name = $row[$this->headers['COLOR_NAME']] ?? '';
                $existingEntry->piece_price = (floatval($row[$this->headers['PIECE_PRICE']] ?? 0)) * 100; // save as cent
                $existingEntry->save();
            } else {
                // Create a new entry if it doesn't exist
                $newData[] = [
                    'unique_key' => $uniqueIdentifier ?? '',
                    'product_title' => $row[$this->headers['PRODUCT_TITLE']] ?? '',
                    'product_description' => $row[$this->headers['PRODUCT_DESCRIPTION']] ?? '',
                    'style_hash' => $row[$this->headers['STYLE#']] ?? '',
                    'sanmar_mainframe_color' => $row[$this->headers['SANMAR_MAINFRAME_COLOR']] ?? '',
                    'size' => $row[$this->headers['SIZE']] ?? '',
                    'color_name' => $row[$this->headers['COLOR_NAME']] ?? '',
                    'piece_price' => (floatval($row[$this->headers['PIECE_PRICE']] ?? 0)) * 100, // save as cent
                ];
            }

        }

        // insert multi rows for new records
        CsvEntry::insert($newData);
    }
}
