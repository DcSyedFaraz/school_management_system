<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Wards;
use App\Exports\MarksExport;
use App\Exports\TeacherReportExport;
use Session;
use Excel;
use DB;

class UserDetailedReportController extends Controller
{
    public function reports(){
        set_time_limit(300);

        if(Session::get('loggedin')==true){
            $classId=1;
            $examId=1;
            // $regionId='';
            // $districtId='';
            // $wardId='';
            $startDate=date('Y-m-d', strtotime(''.date('Y').'-'.date('m').'-01'));
            $endDate=date('Y-m-d');

            $marks = Marks::selectRaw('schoolId, regionId, districtId, wardId, ROUND(SUM(total), 2) as averageMarks')
            ->where([
                ['isActive','=','1'],
                ['isDeleted','=','0'],
                ['classId','=',$classId],
                ['examId','=',$examId],
                // ['regionId','=',Session::get('userRegion')],
                // ['districtId','=',Session::get('userDistrict')],
                // ['wardId','=',Session::get('userWard')],
                ['schoolId','=',Session::get('userSchool')]
            ])
            ->whereBetween('examDate', [$startDate, $endDate])
            ->groupBy('schoolId','regionId','districtId','wardId')
            ->orderBy('averageMarks', 'desc')
            ->get();

            $classes=Grades::select('gradeId','gradeName')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->get();

            $exams=Exams::select('examId','examName')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->get();

            // $regions=Regions::select('regionId','regionName','regionCode')->where([
            //     ['isActive','=','1'],
            //     ['isDeleted','=','0']
            // ])->orderBy('regionName','asc')->get();

            // $districts=Districts::select('districtId','districtName','districtCode')->where([
            //     ['isActive','=','1'],
            //     ['isDeleted','=','0']
            // ])->orderBy('districtName','asc')->get();

            // $wards=Wards::select('wardId','wardName','wardCode')->where([
            //     ['isActive','=','1'],
            //     ['isDeleted','=','0']
            // ])->orderBy('wardName','asc')->get();

            // $dates=Marks::select('examDate')->where([
            //     ['isActive','=','1'],
            //     ['isDeleted','=','0']
            // ])->orderBy('examDate','desc')->distinct()->pluck('examDate');

            session(['pageTitle'=>"PSLE/SFNA Ripoti"]);

            $data=compact('marks','classes','exams','classId','examId','startDate','endDate');
            return view('user.detailedReport')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function filterReport(Request $req){
        set_time_limit(300);

        if(Session::get('loggedin')==true){
            $classId=$req['class'];
            // $regionId=$req['region'];
            // $districtId=$req['district'];
            // $wardId=$req['ward'];
            $examId=$req['exam'];

            $classCondition=($req['class']=='')?['classId','!=',null]:['classId','=',$classId];
            $examCondition=($req['exam']=='')?['examId','!=',null]:['examId','=',$examId];
            // $regionCondition=($regionId=='')?['regionId','!=',null]:['regionId','=',$regionId];
            // $districtCondition=($districtId=='')?['districtId','!=',null]:['districtId','=',$districtId];
            // $wardCondition=($wardId=='')?['wardId','!=',null]:['wardId','=',$wardId];
            $startDate=($req['startDate']=='')?date('Y-m-d', strtotime("2023-01-01")):$req['startDate'];
            $endDate=($req['endDate']=='')?date('Y-m-d'):$req['endDate'];

            $marks = Marks::selectRaw('schoolId, regionId, districtId, wardId, ROUND(SUM(total), 2) as averageMarks')
            ->where([
                ['isActive','=','1'],
                ['isDeleted','=','0'],
                $classCondition,
                // $regionCondition,
                // $districtCondition,
                // $wardCondition,
                $examCondition,
                ['schoolId','=',Session::get('userSchool')]
            ])
            ->whereBetween('examDate', [$startDate, $endDate])
            ->groupBy('schoolId','regionId','districtId','wardId')
            ->orderBy('averageMarks', 'desc')
            ->get();

            $classes=Grades::select('gradeId','gradeName')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->get();

            $exams=Exams::select('examId','examName')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->get();

            // $regions=Regions::select('regionId','regionName','regionCode')->where([
            //     ['isActive','=','1'],
            //     ['isDeleted','=','0']
            // ])->orderBy('regionName','asc')->get();

            // $districts=Districts::select('districtId','districtName','districtCode')->where([
            //     ['isActive','=','1'],
            //     ['isDeleted','=','0']
            // ])->orderBy('districtName','asc')->get();

            // $wards=Wards::select('wardId','wardName','wardCode')->where([
            //     ['isActive','=','1'],
            //     ['isDeleted','=','0']
            // ])->orderBy('wardName','asc')->get();

            // $dates=Marks::select('examDate')->where([
            //     ['isActive','=','1'],
            //     ['isDeleted','=','0']
            // ])->orderBy('examDate','desc')->distinct()->pluck('examDate');

            session(['pageTitle'=>"PSLE/SFNA Ripoti"]);
            // $url3=url('/reports/delete');

            $data=compact('marks','classes','exams','classId','examId','startDate','endDate');
            // return $data;
            return view('user.detailedReport')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function downloadTeacherReport(Request $req){
        set_time_limit(300);

        if(Session::get('loggedin')==true){
            $examId=$req['rExam'];
            $classId=$req['rClass'];
            // $regionId=$req['rRegion'];
            // $districtId=$req['rDistrict'];
            // $wardId=$req['rWard'];
            $schoolId=$req['rSchool'];
            $startDate=$req['rStartDate'];
            $endDate=$req['rEndDate'];

            return Excel::download(new TeacherReportExport($examId, $classId, $schoolId, $startDate, $endDate), 'teacherDetailedReport('.date('Y-m-d H:i:s').').xlsx');
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }
}
