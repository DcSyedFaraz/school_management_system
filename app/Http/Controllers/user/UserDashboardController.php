<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Models\Ranks;
use App\Models\Schools;
use DB;
use Session;

class UserDashboardController extends Controller
{
    public function adminDashboard(){
        if(Session::get('loggedin')==true){
            $classId=1;
            $regionId=Session::get('userRegion');
            $examId=1;
            $startDate=date('Y-m-d', strtotime(''.date('Y').'-'.date('m').'-01'));
            $endDate=date('Y-m-d');

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

            $rank=Ranks::select('rankRangeMin','rankRangeMax')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('rankName','asc')->get();

            $maleAveargeMarks = Marks::select(
                DB::raw('ROUND(((hisabati + kiswahili + sayansi + english + jamii + maadili) / 6), 2) as averageMarks')
            )->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'M'],
                ['userId','=',Session::get('userId')],
                ['classId','=',$classId],
                ['regionId','=',$regionId],
                ['examId','=',$examId]
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $femaleAveargeMarks = Marks::select('average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'F'],
                ['userId','=',Session::get('userId')],
                ['classId','=',$classId],
                ['regionId','=',$regionId],
                ['examId','=',$examId]
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $maleRanks=[0,0,0,0,0];
            $femaleRanks=[0,0,0,0,0];

            foreach ($maleAveargeMarks as $average) {
                if($average['average']!=0){
                    if($rank[0]['rankRangeMin']<$average['average'] && $rank[0]['rankRangeMax']>=$average['average']){
                        $maleRanks[0]=$maleRanks[0]+1;
                    }
                    else if($rank[1]['rankRangeMin']<$average['average'] && $rank[1]['rankRangeMax']>=$average['average']){
                        $maleRanks[1]=$maleRanks[1]+1;
                    }
                    else if($rank[2]['rankRangeMin']<$average['average'] && $rank[2]['rankRangeMax']>=$average['average']){
                        $maleRanks[2]=$maleRanks[2]+1;
                    }
                    else if($rank[3]['rankRangeMin']<$average['average'] && $rank[3]['rankRangeMax']>=$average['average']){
                        $maleRanks[3]=$maleRanks[3]+1;
                    }
                    else{
                        $maleRanks[4]=$maleRanks[4]+1;
                    }
                }
            }

            foreach ($femaleAveargeMarks as $average) {
                if($average['average']!=0){
                    if($rank[0]['rankRangeMin']<$average['average'] && $rank[0]['rankRangeMax']>=$average['average']){
                        $femaleRanks[0]=$femaleRanks[0]+1;
                    }
                    else if($rank[1]['rankRangeMin']<$average['average'] && $rank[1]['rankRangeMax']>=$average['average']){
                        $femaleRanks[1]=$femaleRanks[1]+1;
                    }
                    else if($rank[2]['rankRangeMin']<$average['average'] && $rank[2]['rankRangeMax']>=$average['average']){
                        $femaleRanks[2]=$femaleRanks[2]+1;
                    }
                    else if($rank[3]['rankRangeMin']<$average['average'] && $rank[3]['rankRangeMax']>=$average['average']){
                        $femaleRanks[3]=$femaleRanks[3]+1;
                    }
                    else{
                        $femaleRanks[4]=$femaleRanks[4]+1;
                    }
                }
            }

            $schoolRanks = Marks::select('studentName','average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['classId','=',$classId],
                ['regionId','=',$regionId],
                ['examId','=',$examId],
                ['userId','=',Session::get('userId')]
            ])->whereBetween('examDate', [$startDate, $endDate])->orderBy('average', 'desc')
            ->get();

            session(['pageTitle'=>"Ubao"]);
            $borderLine=$rank[3]['rankRangeMin'];

            $data=compact('classes','exams','regions','dates','classId','regionId','examId','startDate','endDate','maleRanks','femaleRanks','schoolRanks','borderLine');
            return view('user.dashboard')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function adminDashboardFilter(Request $req){
        if(Session::get('loggedin')==true){
            $classId=$req['class'];
            $regionId=Session::get('userRegion');
            $examId=$req['exam'];
            
            $startDate=($req['startDate']=='')?date('Y-m-d', strtotime("2023-01-01")):$req['startDate'];
            $endDate=($req['endDate']=='')?date('Y-m-d'):$req['endDate'];
            $classCondition=($req['class']=='')?['classId','!=',null]:['classId','=',$classId];
            $regionCondition=['regionId','=',$regionId];
            $examCondition=($req['exam']=='')?['examId','!=',null]:['examId','=',$examId];

            $classCondition2=($req['class']=='')?['marks.classId','!=',null]:['marks.classId','=',$classId];
            $regionCondition2=['marks.regionId','=',$regionId];
            $examCondition2=($req['exam']=='')?['marks.examId','!=',null]:['marks.examId','=',$examId];

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

            $rank=Ranks::select('rankRangeMin','rankRangeMax')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('rankName','asc')->get();

            $maleAveargeMarks = Marks::select('average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'M'],
                ['userId','=',Session::get('userId')],
                $classCondition,
                $regionCondition,
                $examCondition
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $femaleAveargeMarks = Marks::select('average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['gender', '=', 'F'],
                ['userId','=',Session::get('userId')],
                $classCondition,
                $regionCondition,
                $examCondition
            ])->whereBetween('examDate', [$startDate, $endDate])->get();

            $maleRanks=[0,0,0,0,0];
            $femaleRanks=[0,0,0,0,0];

            foreach ($maleAveargeMarks as $average) {
                if($average['average']!=0){
                    if($rank[0]['rankRangeMin']<$average['average'] && $rank[0]['rankRangeMax']>=$average['average']){
                        $maleRanks[0]=$maleRanks[0]+1;
                    }
                    else if($rank[1]['rankRangeMin']<$average['average'] && $rank[1]['rankRangeMax']>=$average['average']){
                        $maleRanks[1]=$maleRanks[1]+1;
                    }
                    else if($rank[2]['rankRangeMin']<$average['average'] && $rank[2]['rankRangeMax']>=$average['average']){
                        $maleRanks[2]=$maleRanks[2]+1;
                    }
                    else if($rank[3]['rankRangeMin']<$average['average'] && $rank[3]['rankRangeMax']>=$average['average']){
                        $maleRanks[3]=$maleRanks[3]+1;
                    }
                    else{
                        $maleRanks[4]=$maleRanks[4]+1;
                    }
                }
            }

            foreach ($femaleAveargeMarks as $average) {
                if($average['average']!=0){
                    if($rank[0]['rankRangeMin']<$average['average'] && $rank[0]['rankRangeMax']>=$average['average']){
                        $femaleRanks[0]=$femaleRanks[0]+1;
                    }
                    else if($rank[1]['rankRangeMin']<$average['average'] && $rank[1]['rankRangeMax']>=$average['average']){
                        $femaleRanks[1]=$femaleRanks[1]+1;
                    }
                    else if($rank[2]['rankRangeMin']<$average['average'] && $rank[2]['rankRangeMax']>=$average['average']){
                        $femaleRanks[2]=$femaleRanks[2]+1;
                    }
                    else if($rank[3]['rankRangeMin']<$average['average'] && $rank[3]['rankRangeMax']>=$average['average']){
                        $femaleRanks[3]=$femaleRanks[3]+1;
                    }
                    else{
                        $femaleRanks[4]=$femaleRanks[4]+1;
                    }
                }
            }

            $schoolRanks = Marks::select('studentName','average')->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['userId','=',Session::get('userId')],
                $classCondition2,
                $regionCondition2,
                $examCondition2
            ])->whereBetween('examDate', [$startDate, $endDate])
            ->orderBy('average', 'desc')
            ->get();

            session(['pageTitle'=>"Ubao"]);

            if($classId>4){
                $borderLine=$rank[2]['rankRangeMin'];
            }
            else{
                $borderLine=$rank[3]['rankRangeMin'];
            }

            $data=compact('classes','exams','regions','dates','classId','regionId','examId','startDate','endDate','maleRanks','femaleRanks','schoolRanks','borderLine');
            return view('user.dashboard')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }
}
