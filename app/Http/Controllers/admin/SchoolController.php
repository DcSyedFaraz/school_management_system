<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schools;
use App\Models\User;
use App\Models\Marks;
use Session;

class SchoolController extends Controller
{
    public function schools(){
        if(Session::get('adminLoggedin')==true){
            $regions=Schools::select('schoolId','schoolName')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('schoolName','asc')->get();

            session(['pageTitle'=>"Schools"]);
            $url1=url('/school/save');
            $url2=url('/school/update');
            $url3=url('/school/delete');
         
            $data=compact('regions','url1','url2','url3');
            return view('admin.schools')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function saveSchool(Request $req){
        if(Session::get('adminLoggedin')==true){
            $req->validate(
                [
                    'schoolName'=>'required|unique:schools,schoolName'
                ]
            ); 

            $school=new Schools;
            $school['schoolName']=$req['schoolName'];
            $school->save();

            return back()->with('success','School Saved Successfully!');
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }

    public function updateSchool(Request $req){
        if(Session::get('adminLoggedin')==true){
            $id=$req['entryId'];
            $validSchool=Schools::find($id);

            if($validSchool){
                $req->validate(
                    [
                        'updatedSchoolName'=>'required|unique:schools,schoolName,'.$id.',schoolId'
                    ]
                ); 
    
                $validSchool['schoolName']=$req['updatedSchoolName'];
                $validSchool->save();
    
                return back()->with('success','School Updated Successfully!');
            }
            else{
                return back()->with('error','School Not Found!');
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }

    public function schoolInfo($id){
        if(Session::get('adminLoggedin')==true){
            $schoolData=Schools::find($id);

            if($schoolData){
                $data=[];
                $data['fieldName']=$schoolData['schoolName'];

                return response()->json([
                    'status'=>200,
                    'data'=>$data
                ]);
            } 
            else{
                return response()->json([
                    'status'=>404,
                    'data'=>"No Data Found"
                ]);
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function deleteSchool(Request $req){
        if(Session::get('adminLoggedin')==true){
            $id=$req['delEntryId'];
            $validSchool=Schools::find($id);

            if($validSchool){
                $schoolTeachers=User::where([
                    ['isDeleted','=','0'],
                    ['schoolId','=',$validSchool['schoolId']]
                ])->get();

                if(count($schoolTeachers)>0){
                    foreach ($schoolTeachers as $teacher) {
                        $teacher['email']=$teacher['email'].".";
                        $teacher['mobile']="#";
                        $teacher['isDeleted']=1;
                        $teacher->save();
                    }
                }

                $schoolMarks=Marks::where([
                    ['isDeleted','=','0'],
                    ['schoolId','=',$validSchool['schoolId']]
                ])->get();

                if(count($schoolMarks)>0){
                    foreach ($schoolMarks as $mark) {
                        $mark['isDeleted']=1;
                        $mark->save();
                    }
                }

                $validSchool['schoolName']=$validSchool['schoolName'].".";
                $validSchool['isDeleted']=1;
                $validSchool->save();
    
                return back()->with('success','School Deleted Successfully!');
            }
            else{
                return back()->with('error','School Not Found!');
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }
}
