<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Exports\MarksUserExport;
use Session;
use Excel;
use DB;
use Validator;

class UserReportController extends Controller
{
    public function reports()
    {
        if (Session::get('loggedin') == true) {
            $classId = '1';
            $examId = 1;
            $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
            $endDate = date('Y-m-d');

            // Get subjects based on class
            $subjects = [];
            $subjects = config('subjects.' . $classId, config('subjects.class_default'));


            // Select relevant columns including dynamic subjects
            $selectColumns = array_merge(['markId', 'studentName', 'gender', 'total', 'average'], $subjects);

            $marks = Marks::select($selectColumns)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    ['classId', '=', $classId],
                    ['examId', '=', $examId],
                    ['userId', '=', Session::get('userId')]
                ])->whereBetween('examDate', [$startDate, $endDate])
                ->orderBy('average', 'desc')
                ->get();

            $allMarks = Marks::select($selectColumns)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    ['classId', '=', $classId],
                    ['examId', '=', $examId],
                    ['userId', '=', Session::get('userId')]
                ])->whereBetween('examDate', [$startDate, $endDate])
                ->get();

            $classes = Grades::select('gradeId', 'gradeName')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->get();

            $exams = Exams::select('examId', 'examName')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->get();

            $regions = Regions::select('regionId', 'regionName', 'regionCode')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->orderBy('regionName', 'asc')
                ->get();

            $dates = Marks::select('examDate')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->orderBy('examDate', 'desc')
                ->distinct()
                ->pluck('examDate');

            session(['pageTitle' => "Matokeo"]);
            $url3 = url('/reports/delete');

            $data = compact('marks', 'allMarks', 'classes', 'exams', 'regions', 'dates', 'url3', 'classId', 'examId', 'startDate', 'endDate', 'subjects');
            return view('user.reports')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }


    public function filterReport(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $classId = $req['class'];
            $examId = $req['exam'];

            $examCondition = ($examId == '') ? ['examId', '!=', null] : ['examId', '=', $examId];
            $classCondition = ($classId == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
            $startDate = ($req['startDate'] == '') ? date('Y-m-d', strtotime("2023-01-01")) : $req['startDate'];
            $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];

            // Get subjects based on class
            $subjects = [];
            $subjects = config('subjects.' . $classId, config('subjects.class_default'));

            // Select relevant columns including dynamic subjects
            $selectColumns = array_merge(['markId', 'studentName', 'gender', 'total', 'average'], $subjects);

            $marks = Marks::select($selectColumns)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    $classCondition,
                    $examCondition,
                    ['userId', '=', Session::get('userId')]
                ])->whereBetween('examDate', [$startDate, $endDate])
                ->orderBy('average', 'desc')
                ->get();

            $allMarks = Marks::select($selectColumns)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    $classCondition,
                    $examCondition,
                    ['userId', '=', Session::get('userId')]
                ])->whereBetween('examDate', [$startDate, $endDate])
                ->get();

            $classes = Grades::select('gradeId', 'gradeName')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->get();

            $exams = Exams::select('examId', 'examName')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->get();

            $regions = Regions::select('regionId', 'regionName', 'regionCode')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->orderBy('regionName', 'asc')
                ->get();

            $dates = Marks::select('examDate')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->orderBy('examDate', 'desc')
                ->distinct()
                ->pluck('examDate');

            session(['pageTitle' => "Matokeo"]);
            $url3 = url('/reports/delete');

            $data = compact('marks', 'allMarks', 'classes', 'exams', 'regions', 'dates', 'url3', 'classId', 'examId', 'startDate', 'endDate', 'subjects');
            // return $data;
            return view('user.reports')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }


    public function downloadTeacherReport(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $examId = $req['rExam'];
            $classId = $req['rClass'];
            $startDate = $req['rStartDate'];
            $endDate = $req['rEndDate'];

            return Excel::download(new MarksUserExport($examId, $classId, $startDate, $endDate), 'studentReport(' . date('Y-m-d H:i:s') . ').xlsx');
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }
    public function printAllReport(Request $request)
    {
        // dd($request->input('reportData'));
        $schoolId = Session::get('userSchool');
        $schoolName = DB::table('schools')->where('schoolId', $schoolId)->value('schoolName');
        $districtId = Session::get('userDistrict');
        $districtName = DB::table('districts')->where('districtId', $districtId)->value('districtName');

        $reportData = json_decode($request->input('reportData'), true);

        $reportData['schoolName'] = $schoolName;
        $reportData['districtName'] = $districtName;

        $validator = Validator::make($reportData, [
            'marks' => 'required|array', // Marks should be an array and required
            'marks.*' => 'required', // Each element of the marks array should be an array
        ]);
        if ($validator->fails()) {
            return redirect()->route('user.reports')->withErrors($validator)->withInput(); // This would redirect as GET

        }


        // Load the PDF view and pass in the precomputed data
        $pdf = Pdf::loadView('pdf.report-all', ['reportData' => $reportData]);
        $pdf->output(); // render the PDF
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        // Add page number text to the bottom-right corner
// Add the "Designed by" text in the center
        $canvas->page_text(
            ($canvas->get_width() / 2) - 70,  // Center the text horizontally
            $canvas->get_height() - 30,       // Place it near the bottom of the page
            "Created and Designed by rmstechnology.co.tz",
            null,
            8,
            [0, 0, 0]  // Color (black)
        );

        // Add the page number on the left side
        $canvas->page_text(
            30,  // Place it near the left of the page
            $canvas->get_height() - 30,  // Same Y-position to align with the "Designed by" text
            "Page {PAGE_NUM} of {PAGE_COUNT}",
            null,
            8,
            [0, 0, 0]  // Color (black)
        );

        // Return the PDF as a stream (you may also use ->download('report.pdf'))
        return $pdf->stream('report.pdf');
    }
}
