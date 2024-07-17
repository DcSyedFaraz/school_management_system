<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Cache;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Wards;
use App\Models\Ranks;
use App\Exports\MarksExport;
use App\Exports\StudentDataExport;
use Session;
use Excel;
use DB;

class ReportController extends Controller
{
    public function reports()
    {
        if (Session::get('adminLoggedin') == true) {
            $classId = 1; // Default class
            $examId = 1; // Default exam
            $regionId = '';
            $districtId = '';
            $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
            $endDate = date('Y-m-d');

            $selectFields = 'schoolId, ROUND(AVG(CASE WHEN average > 0 THEN average END), 2) as averageMarks';

            if ($classId == 1) {
                $selectFields .= ',
                ROUND(AVG(CASE WHEN kuhesabu > 0 THEN kuhesabu END), 2) as kuhesabu,
                ROUND(AVG(CASE WHEN kusoma > 0 THEN kusoma END), 2) as kusoma,
                ROUND(AVG(CASE WHEN kuandika > 0 THEN kuandika END), 2) as kuandika,
                ROUND(AVG(CASE WHEN english > 0 THEN english END), 2) as english,
                ROUND(AVG(CASE WHEN mazingira > 0 THEN mazingira END), 2) as mazingira,
                ROUND(AVG(CASE WHEN michezo > 0 THEN michezo END), 2) as michezo';
            } elseif ($classId == 2) {
                $selectFields .= ',
                ROUND(AVG(CASE WHEN kuhesabu > 0 THEN kuhesabu END), 2) as kuhesabu,
                ROUND(AVG(CASE WHEN kusoma > 0 THEN kusoma END), 2) as kusoma,
                ROUND(AVG(CASE WHEN kuandika > 0 THEN kuandika END), 2) as kuandika,
                ROUND(AVG(CASE WHEN english > 0 THEN english END), 2) as english,
                ROUND(AVG(CASE WHEN mazingira > 0 THEN mazingira END), 2) as mazingira,
                ROUND(AVG(CASE WHEN utamaduni > 0 THEN utamaduni END), 2) as utamaduni';
            } elseif ($classId == 3) {
                $selectFields .= ',
                ROUND(AVG(CASE WHEN hisabati > 0 THEN hisabati END), 2) as hisabati,
                ROUND(AVG(CASE WHEN kiswahili > 0 THEN kiswahili END), 2) as kiswahili,
                ROUND(AVG(CASE WHEN sayansi > 0 THEN sayansi END), 2) as sayansi,
                ROUND(AVG(CASE WHEN english > 0 THEN english END), 2) as english,
                ROUND(AVG(CASE WHEN maadili > 0 THEN maadili END), 2) as maadili,
                ROUND(AVG(CASE WHEN jiographia > 0 THEN jiographia END), 2) as jiographia,
                ROUND(AVG(CASE WHEN smichezo > 0 THEN smichezo END), 2) as smichezo';
            } else { // classes 4 to 7
                $selectFields .= ',
                ROUND(AVG(CASE WHEN hisabati > 0 THEN hisabati END), 2) as hisabati,
                ROUND(AVG(CASE WHEN kiswahili > 0 THEN kiswahili END), 2) as kiswahili,
                ROUND(AVG(CASE WHEN sayansi > 0 THEN sayansi END), 2) as sayansi,
                ROUND(AVG(CASE WHEN english > 0 THEN english END), 2) as english,
                ROUND(AVG(CASE WHEN jamii > 0 THEN jamii END), 2) as jamii,
                ROUND(AVG(CASE WHEN maadili > 0 THEN maadili END), 2) as maadili';
            }

            $marks = Marks::selectRaw($selectFields)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    ['classId', '=', $classId],
                    ['examId', '=', $examId]
                ])
                ->groupBy('schoolId')
                ->whereBetween('examDate', [$startDate, $endDate])
                ->orderBy('averageMarks', 'desc')
                ->get();

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

            $districts = Districts::select('districtId', 'districtName', 'districtCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('districtName', 'asc')->get();

            $dates = Marks::select('examDate')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('examDate', 'desc')->distinct()->pluck('examDate');

            session(['pageTitle' => "Ripoti"]);

            $data = compact('marks', 'classes', 'exams', 'regions', 'districts', 'dates', 'classId', 'examId', 'regionId', 'districtId', 'startDate', 'endDate');
            return view('admin.reports')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }


    public function filterReport(Request $req)
    {
        if (Session::get('adminLoggedin') == true) {
            $classId = $req['class'];
            $regionId = $req['region'];
            $districtId = $req['district'];
            $examId = $req['exam'];

            $classCondition = ($classId == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
            $examCondition = ($examId == '') ? ['examId', '!=', null] : ['examId', '=', $examId];
            $regionCondition = ($regionId == '') ? ['regionId', '!=', null] : ['regionId', '=', $regionId];
            $districtCondition = ($districtId == '') ? ['districtId', '!=', null] : ['districtId', '=', $districtId];
            $startDate = ($req['startDate'] == '') ? date('Y-m-d', strtotime("2023-01-01")) : $req['startDate'];
            $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];
            // return $classId;
            $selectFields = 'schoolId, ROUND(AVG(CASE WHEN average > 0 THEN average END), 2) as averageMarks';

            if ($classId == 1) {
                $selectFields .= ',
                ROUND(AVG(CASE WHEN kuhesabu > 0 THEN kuhesabu END), 2) as kuhesabu,
                ROUND(AVG(CASE WHEN kusoma > 0 THEN kusoma END), 2) as kusoma,
                ROUND(AVG(CASE WHEN kuandika > 0 THEN kuandika END), 2) as kuandika,
                ROUND(AVG(CASE WHEN english > 0 THEN english END), 2) as english,
                ROUND(AVG(CASE WHEN mazingira > 0 THEN mazingira END), 2) as mazingira,
                ROUND(AVG(CASE WHEN michezo > 0 THEN michezo END), 2) as michezo';
            } elseif ($classId == 2) {
                $selectFields .= ',
                ROUND(AVG(CASE WHEN kuhesabu > 0 THEN kuhesabu END), 2) as kuhesabu,
                ROUND(AVG(CASE WHEN kusoma > 0 THEN kusoma END), 2) as kusoma,
                ROUND(AVG(CASE WHEN kuandika > 0 THEN kuandika END), 2) as kuandika,
                ROUND(AVG(CASE WHEN english > 0 THEN english END), 2) as english,
                ROUND(AVG(CASE WHEN mazingira > 0 THEN mazingira END), 2) as mazingira,
                ROUND(AVG(CASE WHEN utamaduni > 0 THEN utamaduni END), 2) as utamaduni';
            } elseif ($classId == 3) {
                $selectFields .= ',
                ROUND(AVG(CASE WHEN hisabati > 0 THEN hisabati END), 2) as hisabati,
                ROUND(AVG(CASE WHEN kiswahili > 0 THEN kiswahili END), 2) as kiswahili,
                ROUND(AVG(CASE WHEN sayansi > 0 THEN sayansi END), 2) as sayansi,
                ROUND(AVG(CASE WHEN english > 0 THEN english END), 2) as english,
                ROUND(AVG(CASE WHEN maadili > 0 THEN maadili END), 2) as maadili,
                ROUND(AVG(CASE WHEN jiographia > 0 THEN jiographia END), 2) as jiographia,
                ROUND(AVG(CASE WHEN smichezo > 0 THEN smichezo END), 2) as smichezo';
            } else { // classes 4 to 7
                $selectFields .= ',
                ROUND(AVG(CASE WHEN hisabati > 0 THEN hisabati END), 2) as hisabati,
                ROUND(AVG(CASE WHEN kiswahili > 0 THEN kiswahili END), 2) as kiswahili,
                ROUND(AVG(CASE WHEN sayansi > 0 THEN sayansi END), 2) as sayansi,
                ROUND(AVG(CASE WHEN english > 0 THEN english END), 2) as english,
                ROUND(AVG(CASE WHEN jamii > 0 THEN jamii END), 2) as jamii,
                ROUND(AVG(CASE WHEN maadili > 0 THEN maadili END), 2) as maadili';
            }

            $marks = Marks::selectRaw($selectFields)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    $classCondition,
                    $regionCondition,
                    $districtCondition,
                    $examCondition
                ])
                ->whereBetween('examDate', [$startDate, $endDate])
                ->groupBy('schoolId')
                ->orderBy('averageMarks', 'desc')
                ->get();
            // dd($marks);
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

            $districts = Districts::select('districtId', 'districtName', 'districtCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('districtName', 'asc')->get();

            $dates = Marks::select('examDate')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('examDate', 'desc')->distinct()->pluck('examDate');

            session(['pageTitle' => "Ripoti"]);
            $url3 = url('/reports/delete');

            $data = compact('marks', 'classes', 'exams', 'regions', 'districts', 'dates', 'url3', 'classId', 'examId', 'regionId', 'districtId', 'startDate', 'endDate');
            //    return $data;
            return view('admin.reports')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }


    public function downloadReport(Request $req)
    {
        if (Session::get('adminLoggedin') == true) {
            $examId = $req['rExam'];
            $classId = $req['rClass'];
            $startDate = $req['rStartDate'];
            $endDate = $req['rEndDate'];
            $regionId = $req['rRegion'];
            $districtId = $req['rDistrict'];

            return Excel::download(new MarksExport($examId, $classId, $regionId, $districtId, $startDate, $endDate), 'schoolReport(' . date('Y-m-d H:i:s') . ').xlsx');
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

    function assignGrade($marks)
    {
        $rank = Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0']
        ])->orderBy('rankName', 'asc')->get();

        if ($rank) {
            if ($rank[0]['rankRangeMin'] < $marks && $rank[0]['rankRangeMax'] >= $marks) {
                return $rank[0]['rankName'];
            } else if ($rank[1]['rankRangeMin'] < $marks && $rank[1]['rankRangeMax'] >= $marks) {
                return $rank[1]['rankName'];
            } else if ($rank[2]['rankRangeMin'] < $marks && $rank[2]['rankRangeMax'] >= $marks) {
                return $rank[2]['rankName'];
            } else if ($rank[3]['rankRangeMin'] < $marks && $rank[3]['rankRangeMax'] >= $marks) {
                return $rank[3]['rankName'];
            } else {
                return $rank[4]['rankName'];
            }
        } else {
            return "Null";
        }
    }

    public function studentData()
{
    set_time_limit(300);

    if (Session::get('adminLoggedin') == true) {
        $classId = 1;
        $examId = 1;
        $regionId = '';
        $districtId = '';
        $wardId = '';
        $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
        $endDate = date('Y-m-d');
        $subjects = $this->getSubjectsByClassId($classId);

        $allMarks = Marks::select('markId', 'studentName', 'gender', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId', 'kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo', 'total', 'average')
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['classId', '=', $classId],
                ['examId', '=', $examId]
            ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('average', 'desc')->get();

        // Paginate marks for display
        $marks = Marks::select('markId', 'studentName', 'gender', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId', 'kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo', 'total', 'average')
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['classId', '=', $classId],
                ['examId', '=', $examId]
            ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('average', 'desc')->paginate(10);

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

        $districts = Districts::select('districtId', 'districtName', 'districtCode')->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0']
        ])->orderBy('districtName', 'asc')->get();

        $wards = Wards::select('wardId', 'wardName', 'wardCode')->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0']
        ])->orderBy('wardName', 'asc')->get();

        $gradeArray = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        $subjectGrades = [
            'A' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]],
            'B' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]],
            'C' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]],
            'D' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]],
            'E' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]]
        ];

        $gAverage = [0, 0, 0, 0, 0, 0];
        $subList = ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo'];

        foreach ($allMarks as $mark) {
            if ($mark['average'] == 0) {
                ($mark['gender'] == 'M') ? $gradeArray[10]++ : $gradeArray[11]++;
            } else {
                $gAverage[0] += $mark['kuhesabu'];
                $gAverage[1] += $mark['kusoma'];
                $gAverage[2] += $mark['kuandika'];
                $gAverage[3] += $mark['english'];
                $gAverage[4] += $mark['mazingira'];
                $gAverage[5] += $mark['michezo'];

                $grade = $this->assignGrade($mark['average']);
                if ($grade == 'A') {
                    ($mark['gender'] == 'M') ? $gradeArray[0]++ : $gradeArray[5]++;
                } elseif ($grade == 'B') {
                    ($mark['gender'] == 'M') ? $gradeArray[1]++ : $gradeArray[6]++;
                } elseif ($grade == 'C') {
                    ($mark['gender'] == 'M') ? $gradeArray[2]++ : $gradeArray[7]++;
                } elseif ($grade == 'D') {
                    ($mark['gender'] == 'M') ? $gradeArray[3]++ : $gradeArray[8]++;
                } else {
                    ($mark['gender'] == 'M') ? $gradeArray[4]++ : $gradeArray[9]++;
                }

                foreach ($subList as $index => $subject) {
                    $subjectGrade = $this->assignGrade($mark[$subject]);
                    $subjectGrades[$subjectGrade][$mark['gender']][$index]++;
                }
            }
        }

        session(['pageTitle' => "Matokeo Kiwanafunzi"]);
        $data = compact(
            'classes',
            'allMarks',
            'subjects',
            'marks',
            'gradeArray',
            'subjectGrades',
            'gAverage',
            'exams',
            'regions',
            'districts',
            'wards',
            'classId',
            'examId',
            'regionId',
            'districtId',
            'wardId',
            'startDate',
            'endDate'
        );

        return view('admin.studentData')->with($data);
    } else {
        return redirect('/')->with('accessDenied', 'Session Expired!');
    }
}


    // New code
    private function getSubjectsByClassId($classId)
    {
        switch ($classId) {
            case 1:
                return ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo'];
            case 2:
                return ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'utamaduni'];
            case 3:
                return ['hisabati', 'kiswahili', 'sayansi', 'english', 'maadili', 'jiographia', 'michezo'];
            default: // classes 4 to 7
                return ['hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili'];
        }
    }
    // public function studentDataFilter(Request $request)
    // {
    //     set_time_limit(300);

    //     if (!Session::get('adminLoggedin')) {
    //         return redirect('/')->with('accessDenied', 'Session Expired!');
    //     }

    //     $classId = $request->input('class');
    //     $examId = $request->input('exam');
    //     $regionId = $request->input('region');
    //     $districtId = $request->input('district');
    //     $wardId = $request->input('ward');
    //     $startDate = $request->input('startDate', date('Y-m-d', strtotime("2023-01-01")));
    //     $endDate = $request->input('endDate', date('Y-m-d'));

    //     $conditions = [
    //         ['isActive', '=', 1],
    //         ['isDeleted', '=', 0],
    //     ];

    //     if ($classId)
    //         $conditions[] = ['classId', '=', $classId];
    //     if ($examId)
    //         $conditions[] = ['examId', '=', $examId];
    //     if ($regionId)
    //         $conditions[] = ['regionId', '=', $regionId];
    //     if ($districtId)
    //         $conditions[] = ['districtId', '=', $districtId];
    //     if ($wardId)
    //         $conditions[] = ['wardId', '=', $wardId];

    //     $subjects = $this->getSubjectsByClassId($classId);
    //     $subjectColumns = array_merge(['markId', 'gender', 'studentName', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId'], $subjects, ['total', 'average']);

    //     $query = Marks::select($subjectColumns)
    //         ->where($conditions)
    //         ->whereBetween('examDate', [$startDate, $endDate])
    //         ->orderBy('average', 'desc');

    //     $marks = $query->paginate(10);

    //     // Cache related data
    //     $classes = Cache::remember('active_classes', 60 * 60, function () {
    //         return Grades::select('gradeId', 'gradeName')
    //             ->where(['isActive' => 1, 'isDeleted' => 0])
    //             ->get();
    //     });

    //     $exams = Cache::remember('active_exams', 60 * 60, function () {
    //         return Exams::select('examId', 'examName')
    //             ->where(['isActive' => 1, 'isDeleted' => 0])
    //             ->get();
    //     });

    //     $regions = Cache::remember('active_regions', 60 * 60, function () {
    //         return Regions::select('regionId', 'regionName', 'regionCode')
    //             ->where(['isActive' => 1, 'isDeleted' => 0])
    //             ->orderBy('regionName', 'asc')
    //             ->get();
    //     });

    //     $districts = Cache::remember('active_districts', 60 * 60, function () {
    //         return Districts::select('districtId', 'districtName', 'districtCode')
    //             ->where(['isActive' => 1, 'isDeleted' => 0])
    //             ->orderBy('districtName', 'asc')
    //             ->get();
    //     });

    //     $wards = Cache::remember('active_wards', 60 * 60, function () {
    //         return Wards::select('wardId', 'wardName', 'wardCode')
    //             ->where(['isActive' => 1, 'isDeleted' => 0])
    //             ->orderBy('wardName', 'asc')
    //             ->get();
    //     });

    //     $cacheKey = "marks_chunked_{$classId}_{$examId}_{$regionId}_{$districtId}_{$wardId}_{$startDate}_{$endDate}";

    //     if (!Cache::has($cacheKey . 'd')) {
    //         $gradeArray = array_fill(0, 12, 0);
    //         $aMaleGrade = $aFemaleGrade = $bMaleGrade = $bFemaleGrade = $cMaleGrade = $cFemaleGrade = $dMaleGrade = $dFemaleGrade = $eMaleGrade = $eFemaleGrade = array_fill(0, 6, 0);
    //         $gAverage = array_fill(0, count($subjects), 0);

    //         $gAverage = array_fill(0, count($subjects), 0);
    //         Marks::where($conditions)
    //         ->whereBetween('examDate', [$startDate, $endDate])
    //         ->chunk(1000, function ($allMarks) use (&$gradeArray, &$aMaleGrade, &$aFemaleGrade, &$bMaleGrade, &$bFemaleGrade, &$cMaleGrade, &$cFemaleGrade, &$dMaleGrade, &$dFemaleGrade, &$eMaleGrade, &$eFemaleGrade, &$gAverage, $subjects) {
    //                 // dd($allMarks->count());
    //                 foreach ($allMarks as $mark) {
    //                     if ($mark->average != 0) { // Ensure average is not zero
    //                         foreach ($subjects as $index => $subject) {
    //                             $gAverage[$index] += $mark->$subject;
    //                         }

    //                         $grade = $this->assignGrade($mark->average);
    //                         switch ($grade) {
    //                             case 'A':
    //                                 $mark->gender == 'M' ? $gradeArray[0]++ : $gradeArray[5]++;
    //                                 break;
    //                             case 'B':
    //                                 $mark->gender == 'M' ? $gradeArray[1]++ : $gradeArray[6]++;
    //                                 break;
    //                             case 'C':
    //                                 $mark->gender == 'M' ? $gradeArray[2]++ : $gradeArray[7]++;
    //                                 break;
    //                             case 'D':
    //                                 $mark->gender == 'M' ? $gradeArray[3]++ : $gradeArray[8]++;
    //                                 break;
    //                             default:
    //                                 $mark->gender == 'M' ? $gradeArray[4]++ : $gradeArray[9]++;
    //                                 break;
    //                         }

    //                         foreach ($subjects as $index => $subject) {
    //                             $subGrade = $this->assignGrade($mark->$subject);
    //                             switch ($subGrade) {
    //                                 case 'A':
    //                                     $mark->gender == 'M' ? $aMaleGrade[$index]++ : $aFemaleGrade[$index]++;
    //                                     break;
    //                                 case 'B':
    //                                     $mark->gender == 'M' ? $bMaleGrade[$index]++ : $bFemaleGrade[$index]++; // Corrected
    //                                     break;
    //                                 case 'C':
    //                                     $mark->gender == 'M' ? $cMaleGrade[$index]++ : $cFemaleGrade[$index]++; // Corrected
    //                                     break;
    //                                 case 'D':
    //                                     $mark->gender == 'M' ? $dMaleGrade[$index]++ : $dFemaleGrade[$index]++;
    //                                     break;
    //                                 default:
    //                                     $mark->gender == 'M' ? $eMaleGrade[$index]++ : $eFemaleGrade[$index]++;
    //                                     break;
    //                             }
    //                         }

    //                     } else {
    //                         $mark->gender == 'M' ? $gradeArray[10]++ : $gradeArray[11]++;
    //                     }
    //                     dump([
    //                         'grade' => $grade,
    //                     ]);
    //                 }
    //             });

    //         // Cache the processed data
    //         Cache::put($cacheKey, [
    //             'gradeArray' => $gradeArray,
    //             'aMaleGrade' => $aMaleGrade,
    //             'bMaleGrade' => $bMaleGrade,
    //             'cMaleGrade' => $cMaleGrade,
    //             'dMaleGrade' => $dMaleGrade,
    //             'eMaleGrade' => $eMaleGrade,
    //             'aFemaleGrade' => $aFemaleGrade,
    //             'bFemaleGrade' => $bFemaleGrade,
    //             'cFemaleGrade' => $cFemaleGrade,
    //             'dFemaleGrade' => $dFemaleGrade,
    //             'eFemaleGrade' => $eFemaleGrade,
    //             'gAverage' => $gAverage,
    //         ], 60 * 60);

    //     } else {
    //         // Retrieve the cached data
    //         $cachedData = Cache::get($cacheKey);
    //         $gradeArray = $cachedData['gradeArray'];
    //         $aMaleGrade = $cachedData['aMaleGrade'];
    //         $bMaleGrade = $cachedData['bMaleGrade'];
    //         $cMaleGrade = $cachedData['cMaleGrade'];
    //         $dMaleGrade = $cachedData['dMaleGrade'];
    //         $eMaleGrade = $cachedData['eMaleGrade'];
    //         $aFemaleGrade = $cachedData['aFemaleGrade'];
    //         $bFemaleGrade = $cachedData['bFemaleGrade'];
    //         $cFemaleGrade = $cachedData['cFemaleGrade'];
    //         $dFemaleGrade = $cachedData['dFemaleGrade'];
    //         $eFemaleGrade = $cachedData['eFemaleGrade'];
    //         $gAverage = $cachedData['gAverage'];
    //     }

    //     session(['pageTitle' => "Matokeo Kiwanafunzi"]);
    //     // dd($gAverage);
    //     $data = compact(
    //         'classes',
    //         // 'marks',
    //         'gradeArray',
    //         'aMaleGrade',
    //         'bMaleGrade',
    //         'cMaleGrade',
    //         'dMaleGrade',
    //         'eMaleGrade',
    //         'aFemaleGrade',
    //         'bFemaleGrade',
    //         'cFemaleGrade',
    //         'dFemaleGrade',
    //         'eFemaleGrade',
    //         'gAverage',
    //         'exams',
    //         'regions',
    //         'districts',
    //         'wards',
    //         'classId',
    //         'examId',
    //         'regionId',
    //         'districtId',
    //         'wardId',
    //         'startDate',
    //         'endDate',
    //         'subjects'
    //     );
    //     return $data;
    //     return view('admin.studentData')->with($data);
    // }

    public function studentDataFilter(Request $req)
    {
        set_time_limit(300);

        if (Session::get('adminLoggedin') == true) {
            $classId = $req['class'];
            $examId = $req['exam'];
            $regionId = $req['region'];
            $districtId = $req['district'];
            $wardId = $req['ward'];
            $startDate = ($req['startDate'] == '') ? date('Y-m-d', strtotime("2023-01-01")) : $req['startDate'];
            $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];

            $examCondition = ($examId == '') ? ['examId', '!=', null] : ['examId', '=', $examId];
            $classCondition = ($classId == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
            $regionCondition = ($regionId == '') ? ['regionId', '!=', null] : ['regionId', '=', $regionId];
            $districtCondition = ($districtId == '') ? ['districtId', '!=', null] : ['districtId', '=', $districtId];
            $wardCondition = ($wardId == '') ? ['wardId', '!=', null] : ['wardId', '=', $wardId];

            $params = $req->all();

            $subjects = $this->getSubjectsByClassId($classId);
            $columns = array_merge(
                ['markId', 'gender', 'studentName', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId'],
                $subjects,
                ['total', 'average']
            );
            $marks = Marks::select($columns)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    $classCondition,
                    $examCondition,
                    $regionCondition,
                    $districtCondition,
                    $wardCondition
                ])
                ->whereBetween('examDate', [$startDate, $endDate])
                ->orderBy('average', 'desc')
                ->paginate(10)->appends($params);;

            // Complete query for calculations
            $allMarks = Marks::select($columns)
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    $classCondition,
                    $examCondition,
                    $regionCondition,
                    $districtCondition,
                    $wardCondition
                ])
                ->whereBetween('examDate', [$startDate, $endDate])
                ->orderBy('average', 'desc')
                ->get();

            // Other necessary data
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
                ])->orderBy('regionName', 'asc')->get();

            $districts = Districts::select('districtId', 'districtName', 'districtCode')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->orderBy('districtName', 'asc')->get();

            $wards = Wards::select('wardId', 'wardName', 'wardCode')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0']
                ])->orderBy('wardName', 'asc')->get();

            // Initialize arrays for grade calculations
            $gradeArray = array_fill(0, 12, 0);
            $subjectGrades = ['A' => [], 'B' => [], 'C' => [], 'D' => [], 'E' => []];
            foreach ($subjectGrades as $grade => &$genderGrades) {
                $genderGrades['M'] = array_fill(0, count($subjects), 0);
                $genderGrades['F'] = array_fill(0, count($subjects), 0);
            }

            $gAverage = array_fill(0, count($subjects), 0);

            // Process marks
            $cacheKey = "processed_marks_{$classId}_{$examId}_{$allMarks->count()}_{$regionId}_{$districtId}_{$wardId}_{$startDate}_{$endDate}";
        $processedData = Cache::get($cacheKey);
            // dd($cacheKey);
        if (!$processedData)
           {
            // dd('hi');
             foreach ($allMarks as $mark) {
                if ($mark['average'] == 0) {
                    $gradeArray[$mark['gender'] == 'M' ? 10 : 11]++;
                } else {
                    foreach ($subjects as $index => $subject) {
                        $gAverage[$index] += $mark[$subject];
                    }

                    $grade = $this->assignGrade($mark['average']);
                    $genderIndex = $mark['gender'] == 'M' ? 0 : 5;
                    $gradeArray[$this->getGradeIndex($grade) + $genderIndex]++;

                    foreach ($subjects as $index => $subject) {
                        $subjectGrade = $this->assignGrade($mark[$subject]);
                        $subjectGrades[$subjectGrade][$mark['gender']][$index]++;
                    }
                }
            }
            $processedData = [
                'gradeArray' => $gradeArray,
                'gAverage' => $gAverage,
                'subjectGrades' => $subjectGrades
            ];
            Cache::put($cacheKey, $processedData, now()->addHours(24));
        } else{
            $gradeArray = $processedData['gradeArray'];
            $gAverage = $processedData['gAverage'];
            $subjectGrades = $processedData['subjectGrades'];
        }

            session(['pageTitle' => "Matokeo Kiwanafunzi"]);

            $data = compact(
                'classes',
                'allMarks',
                'gradeArray',
                'subjectGrades',
                'gAverage',
                'exams',
                'regions',
                'districts',
                'wards',
                'classId',
                'examId',
                'regionId',
                'districtId',
                'wardId',
                'startDate',
                'endDate',
                'subjects',
                'marks'
            );
// return $data;
            return view('admin.studentData')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }



    private function getGradeIndex($grade)
    {
        switch ($grade) {
            case 'A': return 0;
            case 'B': return 1;
            case 'C': return 2;
            case 'D': return 3;
            default: return 4; // 'E' grade
        }
    }



    // Old Code
    public function studentDataFilterOLD(Request $req)
    {
        set_time_limit(300);

        if (Session::get('adminLoggedin') == true) {
            $classId = $req['class'];
            $examId = $req['exam'];
            $regionId = $req['region'];
            $districtId = $req['district'];
            $wardId = $req['ward'];
            $examId = $req['exam'];

            $examCondition = ($examId == '') ? ['examId', '!=', null] : ['examId', '=', $examId];
            $classCondition = ($classId == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
            $startDate = ($req['startDate'] == '') ? date('Y-m-d', strtotime("2023-01-01")) : $req['startDate'];
            $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];
            $regionCondition = ($regionId == '') ? ['regionId', '!=', null] : ['regionId', '=', $regionId];
            $districtCondition = ($districtId == '') ? ['districtId', '!=', null] : ['districtId', '=', $districtId];
            $wardCondition = ($wardId == '') ? ['wardId', '!=', null] : ['wardId', '=', $wardId];

            $marks = Marks::select('markId', 'gender', 'studentName', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId', 'hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili', 'total', 'average')
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    $classCondition,
                    $examCondition,
                    $regionCondition,
                    $districtCondition,
                    $wardCondition
                ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('average', 'desc')->get();

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

            $districts = Districts::select('districtId', 'districtName', 'districtCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('districtName', 'asc')->get();

            $wards = Wards::select('wardId', 'wardName', 'wardCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('wardName', 'asc')->get();

            $gradeArray = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

            $aMaleGrade = [0, 0, 0, 0, 0, 0];
            $aFemaleGrade = [0, 0, 0, 0, 0, 0];
            $bMaleGrade = [0, 0, 0, 0, 0, 0];
            $bFemaleGrade = [0, 0, 0, 0, 0, 0];
            $cMaleGrade = [0, 0, 0, 0, 0, 0];
            $cFemaleGrade = [0, 0, 0, 0, 0, 0];
            $dMaleGrade = [0, 0, 0, 0, 0, 0];
            $dFemaleGrade = [0, 0, 0, 0, 0, 0];
            $eMaleGrade = [0, 0, 0, 0, 0, 0];
            $eFemaleGrade = [0, 0, 0, 0, 0, 0];

            $gAverage = [0, 0, 0, 0, 0, 0];
            $subjects = ['hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili'];

            foreach ($marks as $mark) {
                if ($mark['average'] == 0) {
                    ($mark['gender'] == 'M') ? $gradeArray[10]++ : $gradeArray[11]++;
                } else {
                    $gAverage[0] = $gAverage[0] + $mark['hisabati'];
                    $gAverage[1] = $gAverage[1] + $mark['kiswahili'];
                    $gAverage[2] = $gAverage[2] + $mark['sayansi'];
                    $gAverage[3] = $gAverage[3] + $mark['english'];
                    $gAverage[4] = $gAverage[4] + $mark['jamii'];
                    $gAverage[5] = $gAverage[5] + $mark['maadili'];

                    if ($this->assignGrade($mark['average']) == 'A') {
                        ($mark['gender'] == 'M') ? $gradeArray[0]++ : $gradeArray[5]++;
                    } else if ($this->assignGrade($mark['average']) == 'B') {
                        ($mark['gender'] == 'M') ? $gradeArray[1]++ : $gradeArray[6]++;
                    } else if ($this->assignGrade($mark['average']) == 'C') {
                        ($mark['gender'] == 'M') ? $gradeArray[2]++ : $gradeArray[7]++;
                    } else if ($this->assignGrade($mark['average']) == 'D') {
                        ($mark['gender'] == 'M') ? $gradeArray[3]++ : $gradeArray[8]++;
                    } else {
                        ($mark['gender'] == 'M') ? $gradeArray[4]++ : $gradeArray[9]++;
                    }

                    foreach ($subjects as $list) {
                        if ($this->assignGrade($mark[$list]) == 'A') {
                            if ($list == 'hisabati') {
                                ($mark['gender'] == 'M') ? $aMaleGrade[0]++ : $aFemaleGrade[0]++;
                            } else if ($list == 'kiswahili') {
                                ($mark['gender'] == 'M') ? $aMaleGrade[1]++ : $aFemaleGrade[1]++;
                            } else if ($list == 'sayansi') {
                                ($mark['gender'] == 'M') ? $aMaleGrade[2]++ : $aFemaleGrade[2]++;
                            } else if ($list == 'english') {
                                ($mark['gender'] == 'M') ? $aMaleGrade[3]++ : $aFemaleGrade[3]++;
                            } else if ($list == 'jamii') {
                                ($mark['gender'] == 'M') ? $aMaleGrade[4]++ : $aFemaleGrade[4]++;
                            } else {
                                ($mark['gender'] == 'M') ? $aMaleGrade[5]++ : $aFemaleGrade[5]++;
                            }
                        } else if ($this->assignGrade($mark[$list]) == 'B') {
                            if ($list == 'hisabati') {
                                ($mark['gender'] == 'M') ? $bMaleGrade[0]++ : $bFemaleGrade[0]++;
                            } else if ($list == 'kiswahili') {
                                ($mark['gender'] == 'M') ? $bMaleGrade[1]++ : $bFemaleGrade[1]++;
                            } else if ($list == 'sayansi') {
                                ($mark['gender'] == 'M') ? $bMaleGrade[2]++ : $bFemaleGrade[2]++;
                            } else if ($list == 'english') {
                                ($mark['gender'] == 'M') ? $bMaleGrade[3]++ : $bFemaleGrade[3]++;
                            } else if ($list == 'jamii') {
                                ($mark['gender'] == 'M') ? $bMaleGrade[4]++ : $bFemaleGrade[4]++;
                            } else {
                                ($mark['gender'] == 'M') ? $bMaleGrade[5]++ : $bFemaleGrade[5]++;
                            }
                        } else if ($this->assignGrade($mark[$list]) == 'C') {
                            if ($list == 'hisabati') {
                                ($mark['gender'] == 'M') ? $cMaleGrade[0]++ : $cFemaleGrade[0]++;
                            } else if ($list == 'kiswahili') {
                                ($mark['gender'] == 'M') ? $bMaleGrade[1]++ : $bFemaleGrade[1]++;
                            } else if ($list == 'sayansi') {
                                ($mark['gender'] == 'M') ? $bMaleGrade[2]++ : $bFemaleGrade[2]++;
                            } else if ($list == 'english') {
                                ($mark['gender'] == 'M') ? $bMaleGrade[3]++ : $bFemaleGrade[3]++;
                            } else if ($list == 'jamii') {
                                ($mark['gender'] == 'M') ? $bMaleGrade[4]++ : $bFemaleGrade[4]++;
                            } else {
                                ($mark['gender'] == 'M') ? $bMaleGrade[5]++ : $bFemaleGrade[5]++;
                            }
                        } else if ($this->assignGrade($mark[$list]) == 'D') {
                            if ($list == 'hisabati') {
                                ($mark['gender'] == 'M') ? $dMaleGrade[0]++ : $dFemaleGrade[0]++;
                            } else if ($list == 'kiswahili') {
                                ($mark['gender'] == 'M') ? $dMaleGrade[1]++ : $dFemaleGrade[1]++;
                            } else if ($list == 'sayansi') {
                                ($mark['gender'] == 'M') ? $dMaleGrade[2]++ : $dFemaleGrade[2]++;
                            } else if ($list == 'english') {
                                ($mark['gender'] == 'M') ? $dMaleGrade[3]++ : $dFemaleGrade[3]++;
                            } else if ($list == 'jamii') {
                                ($mark['gender'] == 'M') ? $dMaleGrade[4]++ : $dFemaleGrade[4]++;
                            } else {
                                ($mark['gender'] == 'M') ? $dMaleGrade[5]++ : $dFemaleGrade[5]++;
                            }
                        } else {
                            if ($list == 'hisabati') {
                                ($mark['gender'] == 'M') ? $eMaleGrade[0]++ : $eFemaleGrade[0]++;
                            } else if ($list == 'kiswahili') {
                                ($mark['gender'] == 'M') ? $eMaleGrade[1]++ : $eFemaleGrade[1]++;
                            } else if ($list == 'sayansi') {
                                ($mark['gender'] == 'M') ? $eMaleGrade[2]++ : $eFemaleGrade[2]++;
                            } else if ($list == 'english') {
                                ($mark['gender'] == 'M') ? $eMaleGrade[3]++ : $eFemaleGrade[3]++;
                            } else if ($list == 'jamii') {
                                ($mark['gender'] == 'M') ? $eMaleGrade[4]++ : $eFemaleGrade[4]++;
                            } else {
                                ($mark['gender'] == 'M') ? $eMaleGrade[5]++ : $eFemaleGrade[5]++;
                            }
                        }
                    }
                }
            }

            session(['pageTitle' => "Matokeo Kiwanafunzi"]);
            // dd($gAverage);
            $data = compact(
                'classes',
                'marks',
                'subjects',
                'gradeArray',
                'aMaleGrade',
                'bMaleGrade',
                'cMaleGrade',
                'dMaleGrade',
                'eMaleGrade',
                'aFemaleGrade',
                'bFemaleGrade',
                'cFemaleGrade',
                'dFemaleGrade',
                'eFemaleGrade',
                'gAverage',
                'exams',
                'regions',
                'districts',
                'wards',
                'classId',
                'examId',
                'regionId',
                'districtId',
                'wardId',
                'startDate',
                'endDate'
            );
            return $data;
            return view('admin.studentData')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }



    public function downloadStudentData(Request $req)
    {
        set_time_limit(300);

        if (Session::get('adminLoggedin') == true) {
            $examId = $req['rExam'];
            $classId = $req['rClass'];
            $startDate = $req['rStartDate'];
            $endDate = $req['rEndDate'];
            $regionId = $req['rRegion'];
            $districtId = $req['rDistrict'];
            $wardId = $req['rWard'];

            return Excel::download(new StudentDataExport($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate), 'studentData(' . date('Y-m-d H:i:s') . ').xlsx');
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }
}
