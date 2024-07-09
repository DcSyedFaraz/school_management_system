<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Exports\MarksUserExport;
use Session;
use Excel;
use DB;

class UserReportController extends Controller
{
    public function reports(){
        if(Session::get('loggedin')==true){
            $classId=1;
            $examId=1;
            $startDate=date('Y-m-d', strtotime(''.date('Y').'-'.date('m').'-01'));
            $endDate=date('Y-m-d');

            $marks=Marks::select('markId','studentName','gender','hisabati','kiswahili','sayansi','english','jamii','maadili','total','average')
            ->where([
                ['isActive','=','1'],
                ['isDeleted','=','0'],
                ['classId','=',$classId],
                ['examId','=',$examId],
                ['userId','=',Session::get('userId')]
            ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('average','desc')->get();

            $allMarks = Marks::select('hisabati','kiswahili','sayansi','english','jamii','maadili','total','gender')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0'],
                ['classId','=',$classId],
                ['examId','=',$examId],
                ['userId','=',Session::get('userId')]
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

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

            $dates=Marks::select('examDate')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('examDate','desc')->distinct()->pluck('examDate');

            session(['pageTitle'=>"Ripoti"]);
            $url3=url('/reports/delete');
    
            $data=compact('marks','allMarks','classes','exams','regions','dates','url3','classId','examId','startDate','endDate');
            return view('user.reports')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function filterReport(Request $req){
        if(Session::get('loggedin')==true){
            $classId=$req['class'];
            $examId=$req['exam'];

            $examCondition=($examId=='')?['examId','!=',null]:['examId','=',$examId];
            $classCondition=($classId=='')?['classId','!=',null]:['classId','=',$classId];
            $startDate=($req['startDate']=='')?date('Y-m-d', strtotime("2023-01-01")):$req['startDate'];
            $endDate=($req['endDate']=='')?date('Y-m-d'):$req['endDate'];

            $marks=Marks::select('markId','gender','studentName','hisabati','kiswahili','sayansi','english','jamii','maadili','total','average')
            ->where([
                ['isActive','=','1'],
                ['isDeleted','=','0'],
                $classCondition,
                $examCondition,
                ['userId','=',Session::get('userId')]
            ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('average','desc')->get();

            $allMarks = Marks::select('hisabati','kiswahili','sayansi','english','jamii','maadili','total','gender')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0'],
                $classCondition,
                $examCondition,
                ['userId','=',Session::get('userId')]
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

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

            $dates=Marks::select('examDate')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('examDate','desc')->distinct()->pluck('examDate');;

            session(['pageTitle'=>"Ripoti"]);
            $url3=url('/reports/delete');
    
            $data=compact('marks','allMarks','classes','exams','regions','dates','url3','classId','examId','startDate','endDate');
            return view('user.reports')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function downloadTeacherReport(Request $req){
        if(Session::get('loggedin')==true){
            $examId=$req['rExam'];
            $classId=$req['rClass'];
            $startDate=$req['rStartDate'];
            $endDate=$req['rEndDate'];

            return Excel::download(new MarksUserExport($examId, $classId, $startDate, $endDate), 'studentReport('.date('Y-m-d H:i:s').').xlsx');
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');   
        }
    }
}
