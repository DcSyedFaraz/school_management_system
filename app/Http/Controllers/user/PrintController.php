<?php

namespace App\Http\Controllers\user;

use App\Facades\Grading;
use App\Http\Controllers\Controller;
use App\Models\Marks;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Illuminate\Http\Request;
use Session;
use setasign\Fpdi\Fpdi;

class PrintController extends Controller
{
    /**
     * Rebuild a student's subjects/total/average/grade from the DATABASE
     * record, discarding whatever the client sent for those fields. Only
     * the selected markId is trusted from the request. Prevents a client
     * from POSTing an inflated grade, total, or subject mark.
     */
    private function rebuildStudentFromMark($student, Marks $mark): array
    {
        $classId = $mark->classId;
        $subjects = config('subjects.' . $classId, config('subjects.class_default'));

        $rebuiltSubjects = [];
        foreach ($subjects as $subject) {
            $value = $mark->$subject;
            $rebuiltSubjects[] = [
                'name' => $subject,
                'mark' => $value,
                'grade' => Grading::gradeSubject($value),
            ];
        }

        $student['subjects'] = $rebuiltSubjects;
        $student['totalMarks'] = (float) $mark->total;
        $student['average'] = $mark->average;
        $student['grade'] = $mark->average !== null ? Grading::gradeTotal($mark->total) : Grading::absentGrade();

        return $student;
    }

    // Function ya kuhesabu position kwa wanafunzi wote walioteuliwa
    private function calculatePositions($students)
    {
        // Panga descending kwa totalMarks
        usort($students, function($a, $b) {
            return $b['totalMarks'] <=> $a['totalMarks'];
        });

        // Toa position
        $position = 1;
        $prevTotal = null;
        $sameRankCount = 0;
        foreach ($students as $index => &$student) {
            if ($prevTotal === $student['totalMarks']) {
                $student['position'] = $position;
                $sameRankCount++;
            } else {
                $position += $sameRankCount;
                $student['position'] = $position;
                $sameRankCount = 1;
            }
            $prevTotal = $student['totalMarks'];
        }

        return $students;
    }

    /**
     * Per-subject rank across the selected batch, computed from the DB
     * records already loaded — not from client-supplied positions.
     *
     * @param  array<int,\App\Models\Marks>  $markModels  keyed by markId
     * @return array<string,array<int,int>>  subject => [markId => position]
     */
    private function calculateSubjectPositions(array $markModels): array
    {
        if (empty($markModels)) {
            return [];
        }

        $first = reset($markModels);
        $subjects = config('subjects.' . $first->classId, config('subjects.class_default'));

        $positions = [];
        foreach ($subjects as $subject) {
            $scores = [];
            foreach ($markModels as $markId => $mark) {
                if ($mark->$subject !== null) {
                    $scores[$markId] = $mark->$subject;
                }
            }
            arsort($scores);

            $rank = 0;
            $seen = 0;
            $prevScore = null;
            foreach ($scores as $markId => $score) {
                $seen++;
                if ($score !== $prevScore) {
                    $rank = $seen;
                    $prevScore = $score;
                }
                $positions[$subject][$markId] = $rank;
            }
        }

        return $positions;
    }

    public function printReport(Request $request)
    {
        $openingDate = $request->input('openingDate');
        $closingDate = $request->input('closingDate');

        // Wanafunzi walioteuliwa
        $students = json_decode($request->input('selectedStudents'), true) ?? [];
        if(empty($students)) {
            return redirect()->back()->withErrors('Hakuna mwanafunzi aliyechaguliwa.');
        }

        // Idadi ya wanafunzi waliotumia mtihani
        $studentsTakenExam = count($students);

        // Andika folder ya reports kama haipo
        $reportsDirectory = storage_path('app/reports');
        if (!is_dir($reportsDirectory)) {
            mkdir($reportsDirectory, 0777, true);
        }
        $pdfPaths = [];

        // Pata jina la wilaya kutoka session
        $districtId = Session::get('userDistrict');
        $districtName = DB::table('districts')
            ->where('districtId', $districtId)
            ->value('districtName') ?? 'UNKNOWN';

        // Only markId is trusted from the client. Load the real records and
        // rebuild every graded value (subjects, total, average, grade) from
        // the database — the client can no longer forge a grade.
        $markIds = array_column($students, 'id');
        $markModels = Marks::whereIn('markId', $markIds)->get()->keyBy('markId');
        $subjectPositions = $this->calculateSubjectPositions($markModels->all());

        $students = array_values(array_filter(array_map(function ($student) use ($markModels) {
            $mark = $markModels->get($student['id']);
            return $mark ? $this->rebuildStudentFromMark($student, $mark) : null;
        }, $students)));

        if (empty($students)) {
            return redirect()->back()->withErrors('Hakuna mwanafunzi aliyechaguliwa.');
        }

        // Hesabu positions
        $students = $this->calculatePositions($students);

        foreach ($students as $student) {
            $mark = $markModels->get($student['id']);
            if (!$mark) continue;

            // Info za mwanafunzi
            $student['districtName'] = $districtName;
            $student['schoolname'] = $mark->school->schoolName ?? 'NOT AVAILABLE';
            $student['classname'] = $mark->class->gradeName ?? 'NOT AVAILABLE';
            $student['date'] = $mark->examDate ?? 'NOT AVAILABLE';
            $student['examname'] = $mark->exam->examName ?? 'NOT AVAILABLE';

            foreach ($student['subjects'] as &$subject) {
                $subject['gradeDescription'] = Grading::description($subject['grade']);
                $subject['position'] = $subjectPositions[$subject['name']][$student['id']] ?? '-';
            }

            // School contact from logged-in user's mobile
            $schoolContact = DB::table('users')->where('userId', Session::get('userId'))->value('mobile') ?? '';

            // Generate PDF ya mwanafunzi
            $pdf = PDF::loadView('pdf.report', compact('student', 'openingDate', 'closingDate', 'studentsTakenExam', 'schoolContact'))
                      ->setPaper('a5', 'portrait');

            $path = storage_path("app/reports/{$student['id']}.pdf");
            $pdf->save($path);
            $pdfPaths[] = $path;
        }

        // Merge PDFs kwa FPDI
        try {
            if (count($pdfPaths) === 0) {
                return redirect()->back()->withErrors('No valid PDF to merge.');
            }

            $pdfMerger = new Fpdi();
            foreach ($pdfPaths as $pdfPath) {
                if (!file_exists($pdfPath)) continue;

                $pageCount = $pdfMerger->setSourceFile($pdfPath);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $pdfMerger->AddPage();
                    $templateId = $pdfMerger->importPage($pageNo);
                    $pdfMerger->useTemplate($templateId, ['adjustPageSize' => true]);
                }
            }

            $mergedPdfPath = storage_path('app/reports/Ripoti.pdf');
            $pdfMerger->Output($mergedPdfPath, 'F');

            // Futa PDF za mwanafunzi mmoja mmoja
            foreach ($pdfPaths as $pdfPath) {
                if (file_exists($pdfPath)) unlink($pdfPath);
            }

            return response()->file($mergedPdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Ripoti.pdf"',
            ]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->withErrors('Tatizo limetokea: ' . $e->getMessage());
        }
    }

    public function printReportEnglish(Request $request)
    {
        $openingDate = $request->input('openingDate');
        $closingDate = $request->input('closingDate');

        // Selected students
        $students = json_decode($request->input('selectedStudents'), true) ?? [];
        if (empty($students)) {
            return redirect()->back()->withErrors('No student selected.');
        }

        // Total students who took the exam
        $studentsTakenExam = count($students);

        // Ensure reports directory exists
        $reportsDirectory = storage_path('app/reports');
        if (!is_dir($reportsDirectory)) {
            mkdir($reportsDirectory, 0777, true);
        }
        $pdfPaths = [];

        // District name from session
        $districtId = Session::get('userDistrict');
        $districtName = DB::table('districts')
            ->where('districtId', $districtId)
            ->value('districtName') ?? 'UNKNOWN';

        // Only markId is trusted from the client. Load the real records and
        // rebuild every graded value (subjects, total, average, grade) from
        // the database — the client can no longer forge a grade.
        $markIds = array_column($students, 'id');
        $markModels = Marks::whereIn('markId', $markIds)->get()->keyBy('markId');
        $subjectPositions = $this->calculateSubjectPositions($markModels->all());

        $students = array_values(array_filter(array_map(function ($student) use ($markModels) {
            $mark = $markModels->get($student['id']);
            return $mark ? $this->rebuildStudentFromMark($student, $mark) : null;
        }, $students)));

        if (empty($students)) {
            return redirect()->back()->withErrors('No student selected.');
        }

        // Calculate positions
        $students = $this->calculatePositions($students);

        foreach ($students as $student) {
            $mark = $markModels->get($student['id']);
            if (!$mark) continue;

            // Student info
            $student['districtName'] = $districtName;
            $student['schoolname'] = $mark->school->schoolName ?? 'NOT AVAILABLE';
            $student['classname'] = $mark->class->gradeName ?? 'NOT AVAILABLE';
            $student['date'] = $mark->examDate ?? 'NOT AVAILABLE';
            $student['examname'] = $mark->exam->examName ?? 'NOT AVAILABLE';

            foreach ($student['subjects'] as &$subject) {
                $subject['gradeDescription'] = Grading::description($subject['grade'], 'en');
                $subject['position'] = $subjectPositions[$subject['name']][$student['id']] ?? '-';
            }

            // School contact from logged-in user's mobile
            $schoolContact = DB::table('users')->where('userId', Session::get('userId'))->value('mobile') ?? '';

            // Generate English PDF per student
            $pdf = PDF::loadView('pdf.english.report', compact('student', 'openingDate', 'closingDate', 'studentsTakenExam', 'schoolContact'))
                      ->setPaper('a5', 'portrait');

            $path = storage_path("app/reports/eng_{$student['id']}.pdf");
            $pdf->save($path);
            $pdfPaths[] = $path;
        }

        // Merge PDFs with FPDI
        try {
            if (count($pdfPaths) === 0) {
                return redirect()->back()->withErrors('No valid PDF to merge.');
            }

            $pdfMerger = new Fpdi();
            foreach ($pdfPaths as $pdfPath) {
                if (!file_exists($pdfPath)) continue;

                $pageCount = $pdfMerger->setSourceFile($pdfPath);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $pdfMerger->AddPage();
                    $templateId = $pdfMerger->importPage($pageNo);
                    $pdfMerger->useTemplate($templateId, ['adjustPageSize' => true]);
                }
            }

            $mergedPdfPath = storage_path('app/reports/Ripoti-ENG.pdf');
            $pdfMerger->Output($mergedPdfPath, 'F');

            // Delete individual PDFs
            foreach ($pdfPaths as $pdfPath) {
                if (file_exists($pdfPath)) unlink($pdfPath);
            }

            return response()->file($mergedPdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Ripoti-ENG.pdf"',
            ]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->withErrors('An error occurred: ' . $e->getMessage());
        }
    }

    public function studentEnglishReport(Request $request)
    {
        return $this->printReportEnglish($request);
    }

}
