<?php

namespace App\Jobs;

use App\Exports\StudentDataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DownloadStudentDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate)
    {
        $this->examId = $examId;
        $this->classId = $classId;
        $this->regionId = $regionId;
        $this->districtId = $districtId;
        $this->wardId = $wardId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $filename = 'studentData(' . date('Y-m-d_H-i-s') . ').xlsx';
        $filePath = 'public/exports/' . $filename;

        // Store the file in the storage/public directory
        Excel::store(new StudentDataExport($this->examId, $this->classId, $this->regionId, $this->districtId, $this->wardId, $this->startDate, $this->endDate), $filePath);

        // Get the full path to the file
        $fullFilePath = Storage::path($filePath);

        // Store the file path in a cache layer
        \Cache::put('student_data_export_' . $this->examId, $fullFilePath, 60); // cache for 1 hour
    }
}
