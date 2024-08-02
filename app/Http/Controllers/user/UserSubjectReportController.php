<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Config;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Ranks;
use App\Exports\SubjectUserExport;
use Excel;
use Session;

class UserSubjectReportController extends Controller
{
    public function reports()
    {
        set_time_limit(300);

        if (Session::get('loggedin') == true) {
            $classId = '1';
            $examId = 1;
            $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
            $endDate = date('Y-m-d');

            $subjects = $this->getSubjectsForClass($classId);

            $allMarks = Marks::select('regionId', 'districtId', 'wardId', 'schoolId', 'total', 'gender', ...$subjects)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    ['classId', '=', $classId],
                    ['examId', '=', $examId],
                    ['userId', '=', Session::get('userId')]
                ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $classes = Grades::select('gradeId', 'gradeName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $exams = Exams::select('examId', 'examName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $rank = Ranks::select('rankRangeMin', 'rankRangeMax')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('rankName', 'asc')->get();

            $borderLine = $rank[3]['rankRangeMin'];

            session(['pageTitle' => "Ripoti Kimasomo"]);

            $data = compact('borderLine', 'allMarks', 'classes', 'exams', 'classId', 'examId', 'startDate', 'endDate', 'subjects');
            return view('user.subjectReport')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function filterReport(Request $req)
    {
        set_time_limit(300);

        if (Session::get('loggedin') == true) {
            $classId = $req['class'];
            $examId = $req['exam'];

            $examCondition = ($examId == '') ? ['examId', '!=', null] : ['examId', '=', $examId];
            $classCondition = ($classId == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
            $startDate = ($req['startDate'] == '') ? date('Y-m-d', strtotime("2023-01-01")) : $req['startDate'];
            $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];

            $subjects = $this->getSubjectsForClass($classId);

            $allMarks = Marks::select('regionId', 'districtId', 'wardId', 'schoolId', 'total', 'gender', ...$subjects)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    $classCondition,
                    $examCondition,
                    ['userId', '=', Session::get('userId')]
                ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $classes = Grades::select('gradeId', 'gradeName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $exams = Exams::select('examId', 'examName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $rank = Ranks::select('rankRangeMin', 'rankRangeMax')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('rankName', 'asc')->get();

            if ($classId > 4) {
                $borderLine = $rank[2]['rankRangeMin'];
            } else {
                $borderLine = $rank[3]['rankRangeMin'];
            }

            session(['pageTitle' => "Ripoti Kimasomo"]);

            $data = compact('borderLine', 'allMarks', 'classes', 'exams', 'classId', 'examId', 'startDate', 'endDate', 'subjects');
            return view('user.subjectReport')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function getSubjectsForClass($classId)
    {
        $subjects = Config::get('subjects');

        return $subjects[$classId] ?? $subjects['class_default'];
    }


    public function downloadTeacherSubjectReport(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $examId = $req['rExam'];
            $classId = $req['rClass'];
            $startDate = $req['rStartDate'];
            $endDate = $req['rEndDate'];
            $borderLine = $req['rBorderline'];

            return Excel::download(new SubjectUserExport($examId, $classId, $startDate, $endDate, $borderLine), 'studentSubjectReport(' . date('Y-m-d H:i:s') . ').xlsx');
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }
}
