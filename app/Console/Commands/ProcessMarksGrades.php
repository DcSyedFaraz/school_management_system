<?php

namespace App\Console\Commands;

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
    protected $description = 'Recompute total and average for every mark from its subject columns. '
        .'total = sum of non-null subjects for the row\'s class. average = total / (count of non-null '
        .'subjects), or NULL if the student sat nothing at all — average IS NULL is the sole absence flag '
        .'used throughout the grading system, so this command must never write a non-null average for a '
        .'student with zero subjects recorded.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing marks grades started...');

        // Get subjects mapping from config (e.g., config/subjects.php)
        $subjectsMapping = config('subjects');

        $updated = 0;

        // Process marks in chunks to handle large datasets efficiently
        Marks::chunkById(5000, function ($marks) use ($subjectsMapping, &$updated) {
            foreach ($marks as $mark) {
                $classId = $mark->classId;
                // Use specific class subjects or fall back to default
                $subjects = $subjectsMapping[$classId] ?? $subjectsMapping['class_default'];

                $total = 0;
                $subjectCount = 0;

                foreach ($subjects as $subject) {
                    $score = $mark->$subject;

                    if ($score !== null) {
                        $total += $score;
                        $subjectCount++;
                    }
                }

                $mark->total = $total;
                $mark->average = $subjectCount > 0 ? round($total / $subjectCount, 2) : null;
                $mark->save();
                $updated++;
            }
        }, 'markId');

        $this->info("Processing marks grades completed. {$updated} rows updated.");

        return 0;
    }
}
