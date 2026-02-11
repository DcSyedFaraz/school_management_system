<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Marks;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;
use Illuminate\Http\Request;
use Session;
use setasign\Fpdi\Fpdi;

class PrintController extends Controller
{
    // Function ya kurudisha maelezo ya grade
    private function getGradeDescription($grade)
    {
        switch ($grade) {
            case 'A': return 'Bora';
            case 'B': return 'Nzuri sana';
            case 'C': return 'Nzuri';
            case 'D': return 'Inaridhisha';
            case 'E': return 'Dhaifu';
            default: return 'Hajafanya';
        }
    }

    // Function ya kuhesabu position kwa wanafunzi wote walioteuliwa
    private function calculatePositions($students)
    {
        // Hesabu jumla ya alama kwa kila mwanafunzi
        foreach ($students as &$student) {
            $subjects = $student['subjects'] ?? [];
            $totalMarks = 0;
            foreach ($subjects as $sub) {
                if(isset($sub['total']) && $sub['grade'] != 'Null') {
                    $totalMarks += $sub['total'];
                }
            }
            $student['totalMarks'] = $totalMarks;
        }

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

        // Hesabu positions
        $students = $this->calculatePositions($students);

        foreach ($students as $student) {
            $mark = Marks::where('markId', $student['id'])->first();
            if (!$mark) continue;

            // Info za mwanafunzi
            $student['districtName'] = $districtName;
            $student['schoolname'] = $mark->school->schoolName ?? 'NOT AVAILABLE';
            $student['classname'] = $mark->class->gradeName ?? 'NOT AVAILABLE';
            $student['date'] = $mark->examDate ?? 'NOT AVAILABLE';
            $student['examname'] = $mark->exam->examName ?? 'NOT AVAILABLE';

            $student['subjects'] = $student['subjects'] ?? [];

            foreach ($student['subjects'] as &$subject) {
                $subject['gradeDescription'] = $this->getGradeDescription($subject['grade']);
                $subject['position'] = $subject['position'] ?? '-';
            }

            // Generate PDF ya mwanafunzi
            $pdf = PDF::loadView('pdf.report', compact('student', 'openingDate', 'closingDate', 'studentsTakenExam'))
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

        // Calculate positions
        $students = $this->calculatePositions($students);

        foreach ($students as $student) {
            $mark = Marks::where('markId', $student['id'])->first();
            if (!$mark) continue;

            // Student info
            $student['districtName'] = $districtName;
            $student['schoolname'] = $mark->school->schoolName ?? 'NOT AVAILABLE';
            $student['classname'] = $mark->class->gradeName ?? 'NOT AVAILABLE';
            $student['date'] = $mark->examDate ?? 'NOT AVAILABLE';
            $student['examname'] = $mark->exam->examName ?? 'NOT AVAILABLE';

            $student['subjects'] = $student['subjects'] ?? [];

            foreach ($student['subjects'] as &$subject) {
                $subject['gradeDescription'] = $this->getGradeDescription($subject['grade']);
                $subject['position'] = $subject['position'] ?? '-';
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
