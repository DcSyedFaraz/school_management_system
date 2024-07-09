<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Wards;
use App\Models\Schools;
use App\Mail\NewUserMail;
use Hash;
use Session;
use Mail;

class UserController extends Controller
{
    public function teachers(){
        if(Session::get('adminLoggedin')==true){
            $userData=User::leftJoin('regions','regions.regionId','=','users.regionId')
            ->leftJoin('districts','districts.districtId','=','users.districtId')
            ->leftJoin('wards','wards.wardId','=','users.wardId')
            ->leftJoin('schools','schools.schoolId','=','users.schoolId')
            ->select('users.userId','users.userName','users.email','users.mobile','users.registrationNumber','users.isActive','regions.regionName','districts.districtName','wards.wardName','schools.schoolName')->where([
                ['users.isDeleted','=','0'],
                ['users.userType','=','T']
            ])->orderby('users.userId','desc')->get();

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

            $schools=Schools::select('schoolId','schoolName')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('schoolName','asc')->get();
    
            session(['pageTitle'=>"Teachers"]);
            $url1=url('/user/save');
            $url2=url('/user/update');
            $url3=url('/changeAdminActivity');
            $data=compact('userData','url1','url2','url3','regions','districts','wards','schools');
            return view('admin.users')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function admins(){
        if(Session::get('adminLoggedin')==true){
            $userData=User::leftJoin('regions','regions.regionId','=','users.regionId')
            ->leftJoin('districts','districts.districtId','=','users.districtId')
            ->leftJoin('wards','wards.wardId','=','users.wardId')
            ->leftJoin('schools','schools.schoolId','=','users.schoolId')
            ->select('users.userId','users.userName','users.email','users.mobile','users.registrationNumber','users.isActive','regions.regionName','districts.districtName','wards.wardName','schools.schoolName')->where([
                ['users.isDeleted','=','0'],
                ['users.userType','=','A']
            ])->orderby('users.userId','desc')->get();

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
    
            session(['pageTitle'=>"Admins"]);
            $url1=url('/user/save');
            $url2=url('/user/update');
            $url3=url('/changeAdminActivity');
            $data=compact('userData','url1','url2','url3','regions','districts','wards');
            return view('admin.users')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function saveAdmin(Request $req){
        if(Session::get('adminLoggedin')==true){
            Session::put('newAdminForm', true);
            $req->validate(
                [
                    'username'=>'required',
                    'email'=>'required|email:rfc,dns|unique:users,email',
                    'contactNumber'=>'required|unique:users,mobile',
                    'region'=>'required|integer|min:1',
                    'district'=>'required|integer|min:1',
                    'ward'=>'required|integer|min:1',
                    'school'=>'nullable|integer|min:1|unique:users,schoolId',
                    'password'=>'required|confirmed',
                    'password_confirmation'=>'required'
                ]
            );
            
            $token=bin2hex(random_bytes(15));

            $regionData=Regions::find($req['region']);
            $districtData=Districts::find($req['district']);
            $wardData=Wards::find($req['ward']);
            $regNumber="PS".$regionData['regionCode'].$districtData['districtCode'].$wardData['wardCode'];

            $admin=new User;
            $admin['userName']=$req['username'];
            $admin['email']=$req['email'];
            $admin['mobile']=$req['contactNumber'];
            $admin['password']=Hash::make($req['password']);
            $admin['userType']=$req['userType'];
            $admin['token']=$token;
            $admin['regionId']=$regionData['regionId'];
            $admin['districtId']=$districtData['districtId'];
            $admin['wardId']=$wardData['wardId'];
            $admin['schoolId']=$req['school'];
            $admin['registrationNumber']=$regNumber;
            $admin->save();

            $mailData=[
                "userName"=>$req['username'],
                "email"=>$req['email'],
                "password"=>$req['password']
            ];
    
            Mail::to($req['email'])->send(new NewUserMail($mailData));

            return back()->with('success','User Created Successfully!');
        } 
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }

    public function adminActivity(Request $req){
        if(Session::get('adminLoggedin')==true){
            $validUser=User::find($req['truckId']);

            if($validUser){
                $validUser['isActive']=!$validUser['isActive'];
                $validUser->save();

                return back()->with('success','Activity Changed Successfully!');
            }
            else{
                return back()->with('error','User Not Found!');
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function adminInfo($id){
        if(Session::get('adminLoggedin')==true){
            $adminData=User::find($id);

            if($adminData){
                $data=[];
                $data['fullname']=$adminData['userName'];
                $data['email']=$adminData['email'];
                $data['mobile']=$adminData['mobile'];
                $data['regionId']=$adminData['regionId'];
                $data['districtId']=$adminData['districtId'];
                $data['wardId']=$adminData['wardId'];
                $data['schoolId']=$adminData['schoolId'];

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

    public function updateAdmin(Request $req){
        if(Session::get('adminLoggedin')==true){
            Session::put('newAdminForm', false);
            $req->validate(
                [
                    'updatedUsername'=>'required',
                    'updatedEmail'=>'required|email:rfc,dns|unique:users,email,'.$req['adminId'].',userId',
                    'updatedContactNumber'=>'required|unique:users,mobile,'.$req['adminId'].',userId'
                ]
            ); 

            $validUser=User::find($req['adminId']);

            if($validUser){
                if($req['updatedPassword']!="" && $req['updatedConfirmPassword']!=""){
                    if($req['updatedPassword']!=$req['updatedConfirmPassword']){
                        return back()->with('error','Updated Password Mismatch');
                    }
                    else{
                        $validUser['password']=Hash::make($req['updatedPassword']);
                    }
                }

                $validUser['userName']=$req['updatedUsername'];
                $validUser['email']=$req['updatedEmail'];
                $validUser['mobile']=$req['updatedContactNumber'];
                $validUser->save();

                return back()->with('success','User Data Updated Successfully!');
            }
            else{
                return back()->with('error','User Data Not Found!');
            }
        } 
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }
}
