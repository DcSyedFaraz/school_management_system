<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Ranks;
use App\Models\Regions;
use App\Models\Wards;
use App\Models\Districts;
use App\Exports\SubjectExport;
use Session;
use Excel;
use DB;

class SubjectReportController extends Controller
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

            $marks = Marks::selectRaw('regionId, districtId, wardId, schoolId,
                ROUND(AVG(hisabati), 2) as hisabati,
                ROUND(AVG(kiswahili), 2) as kiswahili,
                ROUND(AVG(sayansi), 2) as sayansi,
                ROUND(AVG(english), 2) as english,
                ROUND(AVG(jamii), 2) as jamii,
                ROUND(AVG(maadili), 2) as maadili,
                ROUND(AVG(average), 2) as averageMarks')
            ->where([
                ['isActive','=','1'],
                ['isDeleted','=','0'],
                ['classId','=',$classId],
                ['examId','=',$examId]
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

            $rank=Ranks::select('rankRangeMin','rankRangeMax')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('rankName','asc')->get();

            $borderLine=$rank[3]['rankRangeMin'];

            session(['pageTitle'=>"Kimasomo Ripoti"]);
    
            $data=compact('borderLine','marks','classes','exams','regions','districts','wards','classId','examId','regionId','districtId','wardId','startDate','endDate');
            return view('admin.subjectReport')->with($data);
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
            $wardId=$req['ward'];
            $districtId=$req['district'];
            $examId=$req['exam'];

            $classCondition=($req['class']=='')?['classId','!=',null]:['classId','=',$classId];
            $examCondition=($req['exam']=='')?['examId','!=',null]:['examId','=',$examId];
            $regionCondition=($regionId=='')?['regionId','!=',null]:['regionId','=',$regionId];
            $wardCondition=($wardId=='')?['wardId','!=',null]:['wardId','=',$wardId];
            $districtCondition=($districtId=='')?['districtId','!=',null]:['districtId','=',$districtId];
            $startDate=($req['startDate']=='')?date('Y-m-d', strtotime("2023-01-01")):$req['startDate'];
            $endDate=($req['endDate']=='')?date('Y-m-d'):$req['endDate'];

            $marks = Marks::selectRaw('regionId, districtId, wardId, schoolId,
                ROUND(AVG(hisabati), 2) as hisabati,
                ROUND(AVG(kiswahili), 2) as kiswahili,
                ROUND(AVG(sayansi), 2) as sayansi,
                ROUND(AVG(english), 2) as english,
                ROUND(AVG(jamii), 2) as jamii,
                ROUND(AVG(maadili), 2) as maadili,
                ROUND(AVG(average), 2) as averageMarks')
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

            $rank=Ranks::select('rankRangeMin','rankRangeMax')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('rankName','asc')->get();

            if($classId>4){
                $borderLine=$rank[2]['rankRangeMin'];
            }
            else{
                $borderLine=$rank[3]['rankRangeMin'];
            }

            session(['pageTitle'=>"Kimasomo Ripoti"]);
    
            $data=compact('borderLine','marks','classes','exams','regions','districts','wards','classId','examId','regionId','districtId','wardId','startDate','endDate');
            return view('admin.subjectReport')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }
    
    public function downloadSubjectReport(Request $req){
        if(Session::get('adminLoggedin')==true){
            $examId=$req['rExam'];
            $classId=$req['rClass'];
            $regionId=$req['rRegion'];
            $districtId=$req['rDistrict'];
            $wardId=$req['rWard'];
            $startDate=$req['rStartDate'];
            $endDate=$req['rEndDate'];
            $borderLine=$req['rBorderline'];

            return Excel::download(new SubjectExport($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate, $borderLine), 'schoolSubjectReport('.date('Y-m-d H:i:s').').xlsx');
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');   
        }
    }
}
