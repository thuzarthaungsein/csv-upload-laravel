<?php

namespace App\Http\Controllers;

use App\Events\ListenUploadProgressEvent;
use App\Jobs\FileWriteProcessJob;
use Illuminate\Http\Request;
use App\Jobs\UploadProcessJob;
use App\Jobs\InsertProcessJob;
use App\Models\CsvEntry;
use App\Models\CsvFile;
use App\Models\Progress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Writer;

class UploadCsvController extends Controller
{
    public function show()
    {
        return view('home', ['data' => CsvFile::with('progress')->orderBy('created_at', 'desc')->get()]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $fileContents = file_get_contents($file->getRealPath());
        $fileHash = sha1($fileContents);

        // Store the uploaded file
        $path = $file->store('csv');
        $originalName = $file->getClientOriginalName();
        $cleanedPath = 'csv/' . pathinfo($path, PATHINFO_FILENAME) . '_cleaned.csv';

        $existingFile = CsvFile::where('unique_key', $fileHash)->orderBy('created_at', 'desc')->first();
        $original = $originalName;
        $version = 1;

        if ($existingFile) {
            $original = $existingFile['version'] + 1 . '_' . $originalName;
            $version = $existingFile['version'] + 1;
        }

        $newCsvFile = new CsvFile();
        $newCsvFile->unique_key = $fileHash;
        $newCsvFile->path = $path;
        $newCsvFile->original_name = $original;
        $newCsvFile->version = $version;
        $newCsvFile->user_id = Auth::user()->id;
        $newCsvFile->save();

        // Create a record to track progress
        $progress = new Progress();
        $progress->csv_file_id = $newCsvFile->id;
        $progress->percentage = 0;
        $progress->save();

        // Create a temporary storage for the cleaned CSV
        $csv = Reader::createFromPath(storage_path('app/' . $path), 'r');

        $writeChunkSize = 50;
        $writeChunks = [];

        foreach ($csv->getRecords() as $row) {
            $writeChunks[] = $row;

            if (count($writeChunks) === $writeChunkSize) {
                // Dispatch a single job for this chunk
                FileWriteProcessJob::dispatch($writeChunks, $newCsvFile, $progress, $cleanedPath);
                $writeChunks = [];
            }
        }

        // Dispatch any remaining data as a final job
        if (!empty($writeChunks)) {
            FileWriteProcessJob::dispatch($writeChunks, $newCsvFile, $progress, $cleanedPath, true);
        }

        $allFiles = CsvFile::with('progress')->orderBy('created_at', 'desc')->get();

        return ['files' => $allFiles, 'msg' => 'Success'];
    }
}
