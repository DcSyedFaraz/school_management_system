<?php

namespace App\Http\Controllers\user;

use App\Facades\Grading;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Models\Schools;
use DB;
use Session;

class UserDashboardController extends Controller
{
    public function adminDashboard()
    {
        if (Session::get('loggedin') == true) {
            $classId = 1;
            $regionId = Session::get('userRegion');
            $examId = 1;
            $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
            $endDate = date('Y-m-d');

            $classes = Grades::select('gradeId', 'gradeName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $exams = Exams::select('examId', 'examName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $regions = Regions::select('regionId', 'regionName', 'regionCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('regionName', 'asc')->get();

            $dates = Marks::select('examDate')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('examDate', 'desc')->distinct()->pluck('examDate');

            $maleAveargeMarks = Marks::select('average', 'total')->where([
                        ['isActive', '=', '1'],
                        ['isDeleted', '=', '0'],
                        ['gender', '=', 'M'],
                        ['userId', '=', Session::get('userId')],
                        ['classId', '=', $classId],
                        ['regionId', '=', $regionId],
                        ['examId', '=', $examId]
                    ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $femaleAveargeMarks = Marks::select('average', 'total')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'F'],
                ['userId', '=', Session::get('userId')],
                ['classId', '=', $classId],
                ['regionId', '=', $regionId],
                ['examId', '=', $examId]
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            // Grade distribution — based on TOTAL, not average. average IS
            // NULL is the sole absence flag (excluded from the tally).
            $maleRanks = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
            $femaleRanks = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];

            foreach ($maleAveargeMarks as $average) {
                if ($average['average'] !== null) {
                    $rankName = Grading::gradeTotal($average['total']);
                    $maleRanks[$rankName] = ($maleRanks[$rankName] ?? 0) + 1;
                }
            }

            foreach ($femaleAveargeMarks as $average) {
                if ($average['average'] !== null) {
                    $rankName = Grading::gradeTotal($average['total']);
                    $femaleRanks[$rankName] = ($femaleRanks[$rankName] ?? 0) + 1;
                }
            }

            $schoolRanks = Marks::select('studentName', 'average', 'total')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['classId', '=', $classId],
                ['regionId', '=', $regionId],
                ['examId', '=', $examId],
                ['userId', '=', Session::get('userId')]
            ])->whereBetween('examDate', [$startDate, $endDate])->orderByRaw('(average IS NULL), total DESC')
                ->get();

            session(['pageTitle' => "Ubao"]);
            $borderLine = Grading::passingTotal($classId);
            $data = compact('classes', 'exams', 'regions', 'dates', 'classId', 'regionId', 'examId', 'startDate', 'endDate', 'maleRanks', 'femaleRanks', 'schoolRanks', 'borderLine');
            return view('user.dashboard')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    public function adminDashboardFilter(Request $req)
    {
        if (Session::get('loggedin') == true) {
            $classId = $req['class'];
            $regionId = Session::get('userRegion');
            $examId = $req['exam'];

            $startDate = ($req['startDate'] == '') ? date('Y-m-d', strtotime("2023-01-01")) : $req['startDate'];
            $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];
            $classCondition = ($req['class'] == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
            $regionCondition = ['regionId', '=', $regionId];
            $examCondition = ($req['exam'] == '') ? ['examId', '!=', null] : ['examId', '=', $examId];

            $classCondition2 = ($req['class'] == '') ? ['marks.classId', '!=', null] : ['marks.classId', '=', $classId];
            $regionCondition2 = ['marks.regionId', '=', $regionId];
            $examCondition2 = ($req['exam'] == '') ? ['marks.examId', '!=', null] : ['marks.examId', '=', $examId];

            $classes = Grades::select('gradeId', 'gradeName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $exams = Exams::select('examId', 'examName')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->get();

            $regions = Regions::select('regionId', 'regionName', 'regionCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('regionName', 'asc')->get();

            $dates = Marks::select('examDate')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('examDate', 'desc')->distinct()->pluck('examDate');

            $maleAveargeMarks = Marks::select('average', 'total')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'M'],
                ['userId', '=', Session::get('userId')],
                $classCondition,
                $regionCondition,
                $examCondition
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $femaleAveargeMarks = Marks::select('average', 'total')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'F'],
                ['userId', '=', Session::get('userId')],
                $classCondition,
                $regionCondition,
                $examCondition
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            // Grade distribution — based on TOTAL, not average. average IS
            // NULL is the sole absence flag (excluded from the tally).
            $maleRanks = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
            $femaleRanks = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];

            foreach ($maleAveargeMarks as $average) {
                if ($average['average'] !== null) {
                    $rankName = Grading::gradeTotal($average['total']);
                    $maleRanks[$rankName] = ($maleRanks[$rankName] ?? 0) + 1;
                }
            }

            foreach ($femaleAveargeMarks as $average) {
                if ($average['average'] !== null) {
                    $rankName = Grading::gradeTotal($average['total']);
                    $femaleRanks[$rankName] = ($femaleRanks[$rankName] ?? 0) + 1;
                }
            }


            $schoolRanks = Marks::select('studentName', 'average', 'total')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['userId', '=', Session::get('userId')],
                $classCondition2,
                $regionCondition2,
                $examCondition2
            ])->whereBetween('examDate', [$startDate, $endDate])
                ->orderByRaw('(average IS NULL), total DESC')
                ->get();

            session(['pageTitle' => "Ubao"]);

            $borderLine = Grading::passingTotal($classId);

            $data = compact('classes', 'exams', 'regions', 'dates', 'classId', 'regionId', 'examId', 'startDate', 'endDate', 'maleRanks', 'femaleRanks', 'schoolRanks', 'borderLine');
            return view('user.dashboard')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }
}
