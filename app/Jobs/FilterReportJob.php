<?php

namespace App\Jobs;

use App\Models\Marks;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FilterReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $classId, $regionId, $wardId, $districtId, $examId, $startDate, $endDate;

    public function __construct($classId, $regionId, $wardId, $districtId, $examId, $startDate, $endDate)
    {
        $this->classId = $classId;
        $this->regionId = $regionId;
        $this->wardId = $wardId;
        $this->districtId = $districtId;
        $this->examId = $examId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function handle()
    {
        // Check if the filtered data is cached
        Log::info('Fetching filtered report data');
        $cacheKey = "filtered_report_{$this->classId}_{$this->regionId}_{$this->wardId}_{$this->districtId}_{$this->examId}_{$this->startDate}_{$this->endDate}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        // Fetch the marks data
        $marks = Marks::selectRaw("regionId, districtId, wardId, schoolId, ROUND(AVG(average), 2) as averageMarks")
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['classId', '=', $this->classId],
                ['regionId', '=', $this->regionId],
                ['districtId', '=', $this->districtId],
                ['wardId', '=', $this->wardId],
                ['examId', '=', $this->examId]
            ])
            ->whereBetween('examDate', [$this->startDate, $this->endDate])
            ->groupBy('schoolId', 'regionId', 'districtId', 'wardId')
            ->orderBy('averageMarks', 'desc')
            ->get();

        // Perform additional calculations here (e.g., assign grades and sums)
        $subjects = config("subjects.{$this->classId}") ?: config('subjects.class_default');
        $marksWithCalculatedData = [];

        foreach ($marks as $aMark) {
            $gradeArray = [];
            $stuMarks = Marks::select(array_merge($subjects, ['total']))
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    ['schoolId', '=', $aMark['schoolId']],
                    ['classId', '=', $this->classId],
                    ['regionId', '=', $this->regionId],
                    ['districtId', '=', $this->districtId],
                    ['wardId', '=', $this->wardId],
                    ['examId', '=', $this->examId],
                ])
                ->whereBetween('examDate', [$this->startDate, $this->endDate])
                ->get();

            foreach ($stuMarks as $stuMark) {
                foreach ($subjects as $subject) {
                    $grade = $this->assignGrade($stuMark[$subject]);
                    $gradeArray[] = $subject . $grade;
                }
            }

            $groupArray = array_count_values($gradeArray);

            $marksWithCalculatedData[] = [
                'schoolId' => $aMark['schoolId'],
                'regionId' => $aMark['regionId'],
                'districtId' => $aMark['districtId'],
                'wardId' => $aMark['wardId'],
                'gradeSummary' => $groupArray,
                'totalMarks' => $this->getTotalMarks($groupArray)
            ];
        }
        Log::info('Filtered report data fetched successfully' . json_encode($marksWithCalculatedData));
        // Cache the result for 3 hours
        Cache::put($cacheKey, $marksWithCalculatedData, 180);

        return $marksWithCalculatedData;
    }

    private function assignGrade($marks)
    {
        $rank = \App\Models\Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')
            ->where([['isActive', '=', '1'], ['isDeleted', '=', '0']])
            ->orderBy('rankName', 'asc')
            ->get();

        foreach ($rank as $r) {
            if ($r['rankRangeMin'] < $marks && $r['rankRangeMax'] >= $marks) {
                return $r['rankName'];
            }
        }

        return 'Null';
    }

    private function getTotalMarks($groupArray)
    {
        return array_sum(array_slice($groupArray, 0, 6)); // Sum for the total marks
    }
}
