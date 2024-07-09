<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Districts;
use Session;

class DistrictController extends Controller
{
    public function districts(){
        if(Session::get('adminLoggedin')==true){
            $regions=Districts::select('districtId','districtName','districtCode')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('districtName','asc')->get();

            session(['pageTitle'=>"Districts"]);
            $url1=url('/district/save');
            $url2=url('/district/update');
            $url3=url('/district/delete');
         
            $data=compact('regions','url1','url2','url3');
            return view('admin.regions')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function saveDistrict(Request $req){
        if(Session::get('adminLoggedin')==true){
            $req->validate(
                [
                    'districtName'=>'required|unique:districts,districtName',
                    'districtCode'=>'required|unique:districts,districtCode'
                ]
            ); 

            $region=new Districts;
            $region['districtName']=$req['districtName'];
            $region['districtCode']=$req['districtCode'];
            $region->save();

            return back()->with('success','District Saved Successfully!');
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }

    public function updateDistrict(Request $req){
        if(Session::get('adminLoggedin')==true){
            $id=$req['entryId'];
            $validRegion=Districts::find($id);

            if($validRegion){
                $req->validate(
                    [
                        'updatedDistrictName'=>'required|unique:districts,districtName,'.$id.',districtId',
                        'updatedDistrictCode'=>'required|unique:districts,districtCode,'.$id.',districtId'
                    ]
                ); 
    
                $validRegion['districtName']=$req['updatedDistrictName'];
                $validRegion['districtCode']=$req['updatedDistrictCode'];
                $validRegion->save();
    
                return back()->with('success','District Updated Successfully!');
            }
            else{
                return back()->with('error','District Not Found!');
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }

    public function districtInfo($id){
        if(Session::get('adminLoggedin')==true){
            $regionData=Districts::find($id);

            if($regionData){
                $data=[];
                $data['fieldName']=$regionData['districtName'];
                $data['fieldCode']=$regionData['districtCode'];

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

    public function deleteDistrict(Request $req){
        if(Session::get('adminLoggedin')==true){
            $id=$req['delEntryId'];
            $validRegion=Districts::find($id);

            if($validRegion){
                $validRegion['districtName']=$validRegion['districtName'].".";
                $validRegion['districtCode']='#';
                $validRegion['isDeleted']=1;
                $validRegion->save();
    
                return back()->with('success','District Deleted Successfully!');
            }
            else{
                return back()->with('error','District Not Found!');
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }
}
