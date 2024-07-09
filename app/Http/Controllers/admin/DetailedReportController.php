<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Wards;
use App\Exports\MarksExport;
use App\Exports\SchoolReportExport;
use Session;
use Excel;
use DB;

class DetailedReportController extends Controller
{
    public function reports(){
        set_time_limit(300);

        if(Session::get('adminLoggedin')==true){
            $classId=1;
            $examId=1;
            $regionId='';
            $districtId='';
            $wardId='';
            $startDate=date('Y-m-d', strtotime(''.date('Y').'-'.date('m').'-01'));
            $endDate=date('Y-m-d');

            $marks = Marks::selectRaw('schoolId, regionId, districtId, wardId, ROUND(SUM(total), 5) as averageMarks')
            ->where([
                ['isActive','=','1'],
                ['isDeleted','=','0'],
                ['classId','=',$classId],
                ['examId','=',$examId],
                ['regionId','!=',null],
                ['districtId','!=',null],
                ['wardId','!=',null]
            ])
            ->groupBy('schoolId','regionId','districtId','wardId')
            ->whereBetween('examDate', [$startDate, $endDate])->orderBy('averageMarks', 'desc')
            ->get();

            $classes=Grades::select('gradeId','gradeName')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->get();

            $exams=Exams::select('examId','examName')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->get();

            $regions=Regions::select('regionId','regionName','regionCode')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('regionName','asc')->get();

            $districts=Districts::select('districtId','districtName','districtCode')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('districtName','asc')->get();

            $wards=Wards::select('wardId','wardName','wardCode')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('wardName','asc')->get();

            $dates=Marks::select('examDate')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('examDate','desc')->distinct()->pluck('examDate');

            session(['pageTitle'=>"PSLE/SFNA Ripoti"]);
    
            $data=compact('marks','classes','exams','regions','districts','wards','dates','classId','examId','regionId','districtId','wardId','startDate','endDate');
            return view('admin.detailedReport')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function filterReport(Request $req){
        set_time_limit(300);

        if(Session::get('adminLoggedin')==true){
            $classId=$req['class'];
            $regionId=$req['region'];
            $districtId=$req['district'];
            $wardId=$req['ward'];
            $examId=$req['exam'];

            $classCondition=($req['class']=='')?['classId','!=',null]:['classId','=',$classId];
            $examCondition=($req['exam']=='')?['examId','!=',null]:['examId','=',$examId];
            $regionCondition=($regionId=='')?['regionId','!=',null]:['regionId','=',$regionId];
            $districtCondition=($districtId=='')?['districtId','!=',null]:['districtId','=',$districtId];
            $wardCondition=($wardId=='')?['wardId','!=',null]:['wardId','=',$wardId];
            $startDate=($req['startDate']=='')?date('Y-m-d', strtotime("2023-01-01")):$req['startDate'];
            $endDate=($req['endDate']=='')?date('Y-m-d'):$req['endDate'];

            $marks = Marks::selectRaw('schoolId, regionId, districtId, wardId, ROUND(SUM(total), 5) as averageMarks')
            ->where([
                ['isActive','=','1'],
                ['isDeleted','=','0'],
                $classCondition,
                $regionCondition,
                $districtCondition,
                $wardCondition,
                $examCondition
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

            $regions=Regions::select('regionId','regionName','regionCode')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('regionName','asc')->get();

            $districts=Districts::select('districtId','districtName','districtCode')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('districtName','asc')->get();

            $wards=Wards::select('wardId','wardName','wardCode')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('wardName','asc')->get();

            $dates=Marks::select('examDate')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('examDate','desc')->distinct()->pluck('examDate');;

            session(['pageTitle'=>"PSLE/SFNA Ripoti"]);
            $url3=url('/reports/delete');
    
            $data=compact('marks','classes','exams','regions','districts','wards','dates','url3','classId','examId','regionId','districtId','wardId','startDate','endDate');
            return view('admin.detailedReport')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function downloadAdminReport(Request $req){
        set_time_limit(300);
        
        if(Session::get('adminLoggedin')==true){
            $examId=$req['rExam'];
            $classId=$req['rClass'];
            $regionId=$req['rRegion'];
            $districtId=$req['rDistrict'];
            $wardId=$req['rWard'];
            $startDate=$req['rStartDate'];
            $endDate=$req['rEndDate'];

            return Excel::download(new SchoolReportExport($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate), 'schoolDetailedReport('.date('Y-m-d H:i:s').').xlsx');
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');   
        }
    }
}
