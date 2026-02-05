<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadStudentDataJob;
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
use Illuminate\Support\Facades\Config;
use Session;
use Excel;
use DB;

class ReportController extends Controller
{
    public function reports()
    {
        if (Session::get('adminLoggedin') == true) {
            $classId = '1'; // Default class
            $examId = 1; // Default exam
            $regionId = '';
            $districtId = '';
            $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
            $endDate = date('Y-m-d');

            $subjects = config('subjects.' . $classId, config('subjects.class_default'));

            // Start with the base select fields.
            $selectFields = 'schoolId, ROUND(AVG(CASE WHEN average > 0 THEN average END), 2) as averageMarks';

            // Loop over each subject and append the respective AVG calculation.
            foreach ($subjects as $subject) {
                $selectFields .= ", ROUND(AVG(CASE WHEN {$subject} > 0 THEN {$subject} END), 2) as {$subject}";
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

            session(['pageTitle' => "Matokeo"]);

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
            $subjects = config('subjects.' . $classId, config('subjects.class_default'));

            // Start with the base select fields.
            $selectFields = 'schoolId, ROUND(AVG(CASE WHEN average > 0 THEN average END), 2) as averageMarks';

            // Loop over each subject and append the respective AVG calculation.
            foreach ($subjects as $subject) {
                $selectFields .= ", ROUND(AVG(CASE WHEN {$subject} > 0 THEN {$subject} END), 2) as {$subject}";
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

            session(['pageTitle' => "Matokeo"]);
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
        // $gradeBoundaries = [
        //     'A' => [41, 50],
        //     'B' => [31, 40],
        //     'C' => [21, 30],
        //     'D' => [11, 20],
        //     'E' => [0, 10],
        // ];

        // foreach ($gradeBoundaries as $grade => [$min, $max]) {
        //     if ($marks >= $min && $marks <= $max) {
        //         return $grade;
        //     }
        // }
        // return 'E';
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
    // New Code
    public function studentData()
    {
        set_time_limit(300);

        if (Session::get('adminLoggedin') == true) {
            $classId = '1';
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
            $subList = config('subjects.' . $classId, config('subjects.class_default'));

            $gradeDistribution = [
                'male' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'ABS' => 0],
                'female' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'ABS' => 0]
            ];

            // Initialize subject grades with clearer structure
            $subjectGradeCounts = array_fill_keys(
                ['A', 'B', 'C', 'D', 'E'],
                ['male' => array_fill(0, count($subjects), 0), 'female' => array_fill(0, count($subjects), 0)]
            );

            // Processing marks
            foreach ($allMarks as $mark) {
                $gender = $mark['gender'] === 'M' ? 'male' : 'female';

                if ($mark['average'] == 0) {
                    $gradeDistribution[$gender]['ABS']++;
                    continue; // Skip subject processing for ABS students
                }

                // Process subject averages
                foreach ($subjects as $index => $subject) {
                    $gAverage[$index] += $mark[$subject];
                }

                // Process overall grade
                $grade = $this->assignGrade($mark['average']);
                $gradeDistribution[$gender][$grade]++;

                // Process subject grades
                foreach ($subjects as $index => $subject) {
                    $subjectGrade = $this->assignGrade($mark[$subject]);
                    $subjectGradeCounts[$subjectGrade][$gender][$index]++;
                }
            }

            // Update cache data
            $processedData = [
                'gradeDistribution' => $gradeDistribution,
                'subjectGradeCounts' => $subjectGradeCounts,
                'gAverage' => $gAverage
            ];

            session(['pageTitle' => "Matokeo Kiwanafunzi"]);
            $data = compact(
                'classes',
                'allMarks',
                'gradeDistribution',
                'subjectGradeCounts',
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

            return view('admin.studentData')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }

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
                ->paginate(10)->appends($params);
            ;

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

            // Cache::clear();
            // Process marks
            $cacheKey = "processeds_marks_{$classId}_{$examId}_{$allMarks->count()}_{$regionId}_{$districtId}_{$wardId}_{$startDate}_{$endDate}";
            $processedData = Cache::get($cacheKey);
            // dd($cacheKey);
            if (!$processedData) {
                // dd('hi');
                $gradeDistribution = [
                    'male' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'ABS' => 0],
                    'female' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'ABS' => 0]
                ];

                // Initialize subject grades with clearer structure
                $subjectGradeCounts = array_fill_keys(
                    ['A', 'B', 'C', 'D', 'E'],
                    ['male' => array_fill(0, count($subjects), 0), 'female' => array_fill(0, count($subjects), 0)]
                );

                // Processing marks
                foreach ($allMarks as $mark) {
                    $gender = $mark['gender'] === 'M' ? 'male' : 'female';

                    if ($mark['average'] == 0) {
                        $gradeDistribution[$gender]['ABS']++;
                        continue; // Skip subject processing for ABS students
                    }

                    // Process subject averages
                    foreach ($subjects as $index => $subject) {
                        $gAverage[$index] += $mark[$subject];
                    }

                    // Process overall grade
                    $grade = $this->assignGrade($mark['average']);
                    $gradeDistribution[$gender][$grade]++;

                    // Process subject grades
                    foreach ($subjects as $index => $subject) {
                        $subjectGrade = $this->assignGrade($mark[$subject]);
                        $subjectGradeCounts[$subjectGrade][$gender][$index]++;
                    }
                }

                // Update cache data
                $processedData = [
                    'gradeDistribution' => $gradeDistribution,
                    'subjectGradeCounts' => $subjectGradeCounts,
                    'gAverage' => $gAverage
                ];
                Cache::put($cacheKey, $processedData, now()->addHours(24));
            } else {
                $gradeDistribution = $processedData['gradeDistribution'];
                $subjectGradeCounts = $processedData['subjectGradeCounts'];
                $gAverage = $processedData['gAverage'];
            }
            // dd($processedData);

            session(['pageTitle' => "Matokeo Kiwanafunzi"]);

            $data = compact(
                'classes',
                'allMarks',
                'gradeDistribution',
                'subjectGradeCounts',
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

    // Old Code
    // public function studentDataFilter(Request $req)
    // {
    //     set_time_limit(300);

    //     if (Session::get('adminLoggedin') == true) {
    //         $classId = $req['class'];
    //         $examId = $req['exam'];
    //         $regionId = $req['region'];
    //         $districtId = $req['district'];
    //         $wardId = $req['ward'];
    //         $startDate = ($req['startDate'] == '') ? date('Y-m-d', strtotime("2023-01-01")) : $req['startDate'];
    //         $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];

    //         $examCondition = ($examId == '') ? ['examId', '!=', null] : ['examId', '=', $examId];
    //         $classCondition = ($classId == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
    //         $regionCondition = ($regionId == '') ? ['regionId', '!=', null] : ['regionId', '=', $regionId];
    //         $districtCondition = ($districtId == '') ? ['districtId', '!=', null] : ['districtId', '=', $districtId];
    //         $wardCondition = ($wardId == '') ? ['wardId', '!=', null] : ['wardId', '=', $wardId];

    //         $params = $req->all();

    //         $subjects = $this->getSubjectsByClassId($classId);
    //         $columns = array_merge(
    //             ['markId', 'gender', 'studentName', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId'],
    //             $subjects,
    //             ['total', 'average']
    //         );
    //         $marks = Marks::select($columns)
    //             ->where([
    //                 ['isActive', '=', '1'],
    //                 ['isDeleted', '=', '0'],
    //                 $classCondition,
    //                 $examCondition,
    //                 $regionCondition,
    //                 $districtCondition,
    //                 $wardCondition
    //             ])
    //             ->whereBetween('examDate', [$startDate, $endDate])
    //             ->orderBy('average', 'desc')
    //             ->paginate(10)->appends($params);
    //         ;

    //         // Complete query for calculations
    //         $allMarks = Marks::select($columns)
    //             ->where([
    //                 ['isActive', '=', '1'],
    //                 ['isDeleted', '=', '0'],
    //                 $classCondition,
    //                 $examCondition,
    //                 $regionCondition,
    //                 $districtCondition,
    //                 $wardCondition
    //             ])
    //             ->whereBetween('examDate', [$startDate, $endDate])
    //             ->orderBy('average', 'desc')
    //             ->get();

    //         // Other necessary data
    //         $classes = Grades::select('gradeId', 'gradeName')
    //             ->where([
    //                 ['isActive', '=', '1'],
    //                 ['isDeleted', '=', '0']
    //             ])->get();

    //         $exams = Exams::select('examId', 'examName')
    //             ->where([
    //                 ['isActive', '=', '1'],
    //                 ['isDeleted', '=', '0']
    //             ])->get();

    //         $regions = Regions::select('regionId', 'regionName', 'regionCode')
    //             ->where([
    //                 ['isActive', '=', '1'],
    //                 ['isDeleted', '=', '0']
    //             ])->orderBy('regionName', 'asc')->get();

    //         $districts = Districts::select('districtId', 'districtName', 'districtCode')
    //             ->where([
    //                 ['isActive', '=', '1'],
    //                 ['isDeleted', '=', '0']
    //             ])->orderBy('districtName', 'asc')->get();

    //         $wards = Wards::select('wardId', 'wardName', 'wardCode')
    //             ->where([
    //                 ['isActive', '=', '1'],
    //                 ['isDeleted', '=', '0']
    //             ])->orderBy('wardName', 'asc')->get();

    //         // Initialize arrays for grade calculations
    //         $gradeArray = array_fill(0, 12, 0);
    //         $subjectGrades = ['A' => [], 'B' => [], 'C' => [], 'D' => [], 'E' => []];
    //         foreach ($subjectGrades as $grade => &$genderGrades) {
    //             $genderGrades['M'] = array_fill(0, count($subjects), 0);
    //             $genderGrades['F'] = array_fill(0, count($subjects), 0);
    //         }

    //         $gAverage = array_fill(0, count($subjects), 0);

    //         // Process marks
    //         $cacheKey = "processed_marks_{$classId}_{$examId}_{$allMarks->count()}_{$regionId}_{$districtId}_{$wardId}_{$startDate}_{$endDate}";
    //         $processedData = Cache::get($cacheKey);
    //         // dd($cacheKey);
    //         if (!$processedData) {
    //             // dd('hi');
    //             foreach ($allMarks as $mark) {
    //                 if ($mark['average'] == 0) {
    //                     $gradeArray[$mark['gender'] == 'M' ? 10 : 11]++;
    //                 } else {
    //                     foreach ($subjects as $index => $subject) {
    //                         $gAverage[$index] += $mark[$subject];
    //                     }

    //                     $grade = $this->assignGrade($mark['average']);
    //                     $genderIndex = $mark['gender'] == 'M' ? 0 : 5;
    //                     $gradeArray[$this->getGradeIndex($grade) + $genderIndex]++;

    //                     foreach ($subjects as $index => $subject) {
    //                         $subjectGrade = $this->assignGrade($mark[$subject]);
    //                         $subjectGrades[$subjectGrade][$mark['gender']][$index]++;
    //                     }
    //                 }
    //             }
    //             $processedData = [
    //                 'gradeArray' => $gradeArray,
    //                 'gAverage' => $gAverage,
    //                 'subjectGrades' => $subjectGrades
    //             ];
    //             Cache::put($cacheKey, $processedData, now()->addHours(24));
    //         } else {
    //             $gradeArray = $processedData['gradeArray'];
    //             $gAverage = $processedData['gAverage'];
    //             $subjectGrades = $processedData['subjectGrades'];
    //         }

    //         session(['pageTitle' => "Matokeo Kiwanafunzi"]);

    //         $data = compact(
    //             'classes',
    //             'allMarks',
    //             'gradeArray',
    //             'subjectGrades',
    //             'gAverage',
    //             'exams',
    //             'regions',
    //             'districts',
    //             'wards',
    //             'classId',
    //             'examId',
    //             'regionId',
    //             'districtId',
    //             'wardId',
    //             'startDate',
    //             'endDate',
    //             'subjects',
    //             'marks'
    //         );
    //         // return $data;
    //         return view('admin.studentData')->with($data);
    //     } else {
    //         return redirect('/')->with('accessDenied', 'Session Expired!');
    //     }
    // }
    // public function studentData()
    // {
    //     set_time_limit(300);

    //     if (Session::get('adminLoggedin') == true) {
    //         $classId = '1';
    //         $examId = 1;
    //         $regionId = '';
    //         $districtId = '';
    //         $wardId = '';
    //         $startDate = date('Y-m-d', strtotime('' . date('Y') . '-' . date('m') . '-01'));
    //         $endDate = date('Y-m-d');
    //         $subjects = $this->getSubjectsByClassId($classId);

    //         $allMarks = Marks::select('markId', 'studentName', 'gender', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId', 'kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo', 'total', 'average')
    //             ->where([
    //                 ['isActive', '=', '1'],
    //                 ['isDeleted', '=', '0'],
    //                 ['classId', '=', $classId],
    //                 ['examId', '=', $examId]
    //             ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('average', 'desc')->get();

    //         // Paginate marks for display
    //         $marks = Marks::select('markId', 'studentName', 'gender', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId', 'kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo', 'total', 'average')
    //             ->where([
    //                 ['isActive', '=', '1'],
    //                 ['isDeleted', '=', '0'],
    //                 ['classId', '=', $classId],
    //                 ['examId', '=', $examId]
    //             ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('average', 'desc')->paginate(10);

    //         $classes = Grades::select('gradeId', 'gradeName')->where([
    //             ['isActive', '=', '1'],
    //             ['isDeleted', '=', '0']
    //         ])->get();

    //         $exams = Exams::select('examId', 'examName')->where([
    //             ['isActive', '=', '1'],
    //             ['isDeleted', '=', '0']
    //         ])->get();

    //         $regions = Regions::select('regionId', 'regionName', 'regionCode')->where([
    //             ['isActive', '=', '1'],
    //             ['isDeleted', '=', '0']
    //         ])->orderBy('regionName', 'asc')->get();

    //         $districts = Districts::select('districtId', 'districtName', 'districtCode')->where([
    //             ['isActive', '=', '1'],
    //             ['isDeleted', '=', '0']
    //         ])->orderBy('districtName', 'asc')->get();

    //         $wards = Wards::select('wardId', 'wardName', 'wardCode')->where([
    //             ['isActive', '=', '1'],
    //             ['isDeleted', '=', '0']
    //         ])->orderBy('wardName', 'asc')->get();

    //         $gradeArray = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

    //         $subjectGrades = [
    //             'A' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]],
    //             'B' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]],
    //             'C' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]],
    //             'D' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]],
    //             'E' => ['M' => [0, 0, 0, 0, 0, 0], 'F' => [0, 0, 0, 0, 0, 0]]
    //         ];

    //         $gAverage = [0, 0, 0, 0, 0, 0];
    //         $subList = ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo'];

    //         foreach ($allMarks as $mark) {
    //             if ($mark['average'] == 0) {
    //                 ($mark['gender'] == 'M') ? $gradeArray[10]++ : $gradeArray[11]++;
    //             } else {
    //                 $gAverage[0] += $mark['kuhesabu'];
    //                 $gAverage[1] += $mark['kusoma'];
    //                 $gAverage[2] += $mark['kuandika'];
    //                 $gAverage[3] += $mark['english'];
    //                 $gAverage[4] += $mark['mazingira'];
    //                 $gAverage[5] += $mark['michezo'];

    //                 $grade = $this->assignGrade($mark['average']);
    //                 if ($grade == 'A') {
    //                     ($mark['gender'] == 'M') ? $gradeArray[0]++ : $gradeArray[5]++;
    //                 } elseif ($grade == 'B') {
    //                     ($mark['gender'] == 'M') ? $gradeArray[1]++ : $gradeArray[6]++;
    //                 } elseif ($grade == 'C') {
    //                     ($mark['gender'] == 'M') ? $gradeArray[2]++ : $gradeArray[7]++;
    //                 } elseif ($grade == 'D') {
    //                     ($mark['gender'] == 'M') ? $gradeArray[3]++ : $gradeArray[8]++;
    //                 } else {
    //                     ($mark['gender'] == 'M') ? $gradeArray[4]++ : $gradeArray[9]++;
    //                 }

    //                 foreach ($subList as $index => $subject) {
    //                     $subjectGrade = $this->assignGrade($mark[$subject]);
    //                     $subjectGrades[$subjectGrade][$mark['gender']][$index]++;
    //                 }
    //             }
    //         }

    //         session(['pageTitle' => "Matokeo Kiwanafunzi"]);
    //         $data = compact(
    //             'classes',
    //             'allMarks',
    //             'subjects',
    //             'marks',
    //             'gradeArray',
    //             'subjectGrades',
    //             'gAverage',
    //             'exams',
    //             'regions',
    //             'districts',
    //             'wards',
    //             'classId',
    //             'examId',
    //             'regionId',
    //             'districtId',
    //             'wardId',
    //             'startDate',
    //             'endDate'
    //         );

    //         return view('admin.studentData')->with($data);
    //     } else {
    //         return redirect('/')->with('accessDenied', 'Session Expired!');
    //     }
    // }



    private function getGradeIndex($grade)
    {
        switch ($grade) {
            case 'A':
                return 0;
            case 'B':
                return 1;
            case 'C':
                return 2;
            case 'D':
                return 3;
            default:
                return 4; // 'E' grade
        }
    }

    // New code
    private function getSubjectsByClassId($classId)
    {
        $subjects = Config::get('subjects');

        return $subjects[$classId] ?? $subjects['class_default'];
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
            // DownloadStudentDataJob::dispatch($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate);
            // Session::flash('success', 'Data Updated Successfully!');
            // return back();
            return Excel::download(new StudentDataExport($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate), 'studentData(' . date('Y-m-d H:i:s') . ').xlsx');
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }
    public function studentReportEnglish(Request $request){
    $openingDate = $request->input('openingDate');
    $closingDate = $request->input('closingDate');

    return view('pdf.english.student-report', compact('openingDate','closingDate'));
}

public function schoolReportEnglish(){
    return view('pdf.english.school-report');
}

}
