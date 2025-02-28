<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Jobs\FilterReportJob;
use Cache;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Ranks;
use App\Models\Regions;
use App\Models\Wards;
use App\Models\Districts;
use App\Exports\SubjectExport;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use DB;

class SubjectReportController extends Controller
{
    public function reports()
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

            $subjects = config('subjects.' . $classId) ?: config('subjects.class_default');
            $subjectsSelect = implode(', ', array_map(function ($subject) {
                return "ROUND(AVG($subject), 2) as $subject";
            }, $subjects));

            $marks = Marks::selectRaw("regionId, districtId, wardId, schoolId, $subjectsSelect, ROUND(AVG(average), 2) as averageMarks")
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    ['classId', '=', $classId],
                    ['examId', '=', $examId]
                ])
                ->groupBy('schoolId', 'regionId', 'districtId', 'wardId')
                ->whereBetween('examDate', [$startDate, $endDate])->orderBy('averageMarks', 'desc')
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

            $wards = Wards::select('wardId', 'wardName', 'wardCode')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('wardName', 'asc')->get();

            $rank = Ranks::select('rankRangeMin', 'rankRangeMax')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('rankName', 'asc')->get();

            $borderLine = $rank[3]['rankRangeMin'];

            session(['pageTitle' => "Kimasomo Ripoti"]);

            $data = compact('borderLine', 'marks', 'classes', 'exams', 'regions', 'districts', 'wards', 'classId', 'examId', 'regionId', 'districtId', 'wardId', 'startDate', 'endDate', 'subjects');
            return view('admin.subjectReport')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }


    public function oldfilterReport(Request $req)
    {
        set_time_limit(300);

        if (Session::get('adminLoggedin') == true) {
            $classId = $req['class'];
            $regionId = $req['region'];
            $wardId = $req['ward'];
            $districtId = $req['district'];
            $examId = $req['exam'];

            $classCondition = ($req['class'] == '') ? ['classId', '!=', null] : ['classId', '=', $classId];
            $examCondition = ($req['exam'] == '') ? ['examId', '!=', null] : ['examId', '=', $examId];
            $regionCondition = ($regionId == '') ? ['regionId', '!=', null] : ['regionId', '=', $regionId];
            $wardCondition = ($wardId == '') ? ['wardId', '!=', null] : ['wardId', '=', $wardId];
            $districtCondition = ($districtId == '') ? ['districtId', '!=', null] : ['districtId', '=', $districtId];
            $startDate = ($req['startDate'] == '') ? date('Y-m-d', strtotime("2023-01-01")) : $req['startDate'];
            $endDate = ($req['endDate'] == '') ? date('Y-m-d') : $req['endDate'];

            $subjects = config("subjects.$classId") ?: config('subjects.class_default');
            $subjectsSelect = implode(', ', array_map(function ($subject) {
                return "ROUND(AVG($subject), 2) as $subject";
            }, $subjects));

            $marks = Marks::selectRaw("regionId, districtId, wardId, schoolId, $subjectsSelect, ROUND(AVG(average), 2) as averageMarks")
                ->where([
                    ['isActive', '=', '1'],
                    ['isDeleted', '=', '0'],
                    $classCondition,
                    $regionCondition,
                    $districtCondition,
                    $wardCondition,
                    $examCondition
                ])
                ->whereBetween('examDate', [$startDate, $endDate])
                ->groupBy('schoolId', 'regionId', 'districtId', 'wardId')
                ->orderBy('averageMarks', 'desc')
                ->get();
            // dd($startDate, $endDate, $examCondition);

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

            $rank = Ranks::select('rankRangeMin', 'rankRangeMax')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0']
            ])->orderBy('rankName', 'asc')->get();

            if ($classId > 4) {
                $borderLine = $rank[2]['rankRangeMin'];
            } else {
                $borderLine = $rank[3]['rankRangeMin'];
            }

            session(['pageTitle' => "Kimasomo Ripoti"]);

            $data = compact('borderLine', 'marks', 'classes', 'exams', 'regions', 'districts', 'wards', 'classId', 'examId', 'regionId', 'districtId', 'wardId', 'startDate', 'endDate', 'subjects');
            return view('admin.subjectReport')->with($data);
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }
    public function fetchCachedFilteredReport(Request $req)
    {
        // dd($req);
        $classId = $req['class'];
        $regionId = $req['region'];
        $wardId = $req['ward'];
        $districtId = $req['district'];
        $examId = $req['exam'];
        $startDate = $req['startDate'];
        $endDate = $req['endDate'];

        $cacheKey = "filtered_report_{$classId}_{$regionId}_{$wardId}_{$districtId}_{$examId}_{$startDate}_{$endDate}";

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        } else {
            return response()->json(['message' => 'Data is not available yet. Please try again later.'], 404);
        }
    }

    public function filterReport(Request $req)
    {
        // Ensure the user is logged in
        if (Session::get('adminLoggedin') == true) {
            // Capture the filter inputs from the request
            $classId = $req['class'];
            $regionId = $req['region'];
            $wardId = $req['ward'];
            $districtId = $req['district'];
            $examId = $req['exam'];
            $startDate = $req['startDate'];
            $endDate = $req['endDate'];

            // Generate a cache key based on the filter inputs
            $cacheKey = "filtered_report_{$classId}_{$regionId}_{$wardId}_{$districtId}_{$examId}_{$startDate}_{$endDate}";
            Cache::delete($cacheKey);
            // Check if the data is already cached
            if (Cache::has($cacheKey)) {
                // Return the cached data directly
                dd(Cache::get($cacheKey));
                return response()->json(Cache::get($cacheKey));
            } else {
                // If the data is not cached, dispatch the job to handle the filtering
                $job = FilterReportJob::dispatch($classId, $regionId, $wardId, $districtId, $examId, $startDate, $endDate);

                // Return a response indicating that the job has been dispatched
                return response()->json([
                    'message' => 'Filtering started, please wait...',
                ]);
            }
        } else {
            // If the user is not logged in, redirect with an error message
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }


    public function downloadSubjectReport(Request $req)
    {
        if (Session::get('adminLoggedin') == true) {
            $examId = $req['rExam'];
            $classId = $req['rClass'];
            $regionId = $req['rRegion'];
            $districtId = $req['rDistrict'];
            $wardId = $req['rWard'];
            $startDate = $req['rStartDate'];
            $endDate = $req['rEndDate'];
            $borderLine = $req['rBorderline'];

            return Excel::download(new SubjectExport($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate, $borderLine), 'schoolSubjectReport(' . date('Y-m-d H:i:s') . ').xlsx');
        } else {
            return redirect('/')->with('accessDenied', 'Session Expired!');
        }
    }
}
