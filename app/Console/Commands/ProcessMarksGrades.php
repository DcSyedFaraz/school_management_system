<?php

namespace App\Console\Commands;

use App\Models\MarkGrade;
use App\Models\Marks;
use Illuminate\Console\Command;

class ProcessMarksGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-marks-grades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing marks grades started...');

        // Get subjects mapping from config (e.g., config/subjects.php)
        $subjectsMapping = config('subjects');

        // Process marks in chunks to handle large datasets efficiently
        Marks::chunk(50000, function ($marks) use ($subjectsMapping) {
            foreach ($marks as $mark) {
                $classId = $mark->classId;
                // Use specific class subjects or fall back to default
                $subjects = $subjectsMapping[$classId] ?? $subjectsMapping['class_default'];

                $total = 0;
                $subjectCount = count($subjects);

                // foreach ($subjects as $subject) {
                //     // Get the score from the mark record dynamically
                //     $score = $mark->$subject;
                //     $grade = $this->calculateGrade($score);

                //     $total += $score;

                //     // Insert or update the subject grade in the mark_grades table
                //     MarkGrade::updateOrCreate(
                //         [
                //             'markId' => $mark->markId,
                //             'subject' => $subject,
                //         ],
                //         [
                //             'grade' => $grade,
                //         ]
                //     );
                // }

                // Recalculate total and average, then update the mark record
                $average = $subjectCount > 0 ? ($total / $subjectCount) : 0;
                // $mark->total = $total;
                $mark->average = $average;
                $mark->save();
            }
        });

        $this->info('Processing marks grades completed.');
        return 0;
    }

    /**
     * Calculate the grade for a given score.
     *
     * @param  int  $score
     * @return string
     */
    // private function calculateGrade($score)
    // {
    //     $rank = \App\Models\Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')
    //         ->where([['isActive', '=', '1'], ['isDeleted', '=', '0']])
    //         ->orderBy('rankName', 'asc')
    //         ->get();

    //     foreach ($rank as $r) {
    //         if ($r['rankRangeMin'] <= $score && $r['rankRangeMax'] >= $score) {
    //             return $r['rankName'];
    //         }
    //     }
    //     return 'Null';
    // }
}
