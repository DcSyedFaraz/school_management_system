<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Marks;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;

class PrintController extends Controller
{
    private function getGradeDescription($grade)
    {
        switch ($grade) {
            case 'A':
                return 'Bora';
            case 'B':
                return 'Nzuri sana';
            case 'C':
                return 'Nzuri';
            case 'D':
                return 'Inaridhisha';
            case 'E':
                return 'Dhaifu';
            default:
                return 'Hajafanya';
        }
    }
    public function printReport(Request $request)
    {
        $openingDate = $request->input('openingDate');
        $closingDate = $request->input('closingDate');

        $students = json_decode($request->input('selectedStudents'), true);

        $reportsDirectory = storage_path('app/reports');

        // Create the directory if it doesn't exist
        if (!is_dir($reportsDirectory)) {
            mkdir($reportsDirectory, 0777, true);
        }
        $pdfPaths = [];

        // Generate individual PDFs for each student
        foreach ($students as $student) {
            $mark = Marks::where('markId', $student['id'])->select('markId', 'classId', 'examId', 'schoolId', 'examDate')->first();
            // dd(count($student['subjects']));
            $student['schoolname'] = $mark->school->schoolName ?? 'NOT AVAILABLE';
            $student['classname'] = $mark->class->gradeName ?? 'NOT AVAILABLE';
            $student['date'] = $mark->examDate ?? 'NOT AVAILABLE';
            $student['examname'] = $mark->exam->examName ?? 'NOT AVAILABLE';

            foreach ($student['subjects'] as &$subject) {
                $subject['gradeDescription'] = $this->getGradeDescription($subject['grade']);
                // dd($subject);
            }

            $pdf = PDF::loadView('pdf.report', compact('student', 'openingDate', 'closingDate'))->setPaper('a5', 'portrait');
            $path = storage_path("app/reports/{$student['id']}.pdf");
            $pdf->save($path);
            $pdfPaths[] = $path;
        }



        try {
            // Merge PDFs
            $pdfMerger = new Fpdi();
            foreach ($pdfPaths as $pdfPath) {
                $pageCount = $pdfMerger->setSourceFile($pdfPath);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $pdfMerger->AddPage();
                    $templateId = $pdfMerger->importPage($pageNo);
                    $pdfMerger->useTemplate($templateId, ['adjustPageSize' => true]);
                }
            }

            // Output the merged PDF
            $mergedPdfPath = storage_path('app/reports/Ripoti.pdf');
            $pdfMerger->Output($mergedPdfPath, 'F');

            // Delete individual PDFs
            foreach ($pdfPaths as $pdfPath) {
                unlink($pdfPath);
            }
            // return response()->file($mergedPdfPath, [
            //     'Content-Type' => 'application/pdf',
            //     'Content-Disposition' => 'inline; filename="Ripoti.pdf"',
            // ]);

            return response()->download($mergedPdfPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // Handle the exception
            \Log::error($e->getMessage());
            return redirect()->back()->withErrors('An error occured' . $$e->getMessage());
        }
        // Return the merged PDF for download
    }
}
