<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wards;
use Session;

class WardController extends Controller
{
    public function wards(){
        if(Session::get('adminLoggedin')==true){
            $regions=Wards::select('wardId','wardName','wardCode')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('wardName','asc')->get();

            session(['pageTitle'=>"Wards"]);
            $url1=url('/ward/save');
            $url2=url('/ward/update');
            $url3=url('/ward/delete');
         
            $data=compact('regions','url1','url2','url3');
            return view('admin.regions')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function saveWard(Request $req){
        if(Session::get('adminLoggedin')==true){
            $req->validate(
                [
                    'wardName'=>'required|unique:wards,wardName',
                    'wardCode'=>'required|unique:wards,wardCode'
                ]
            ); 

            $region=new Wards;
            $region['wardName']=$req['wardName'];
            $region['wardCode']=$req['wardCode'];
            $region->save();

            return back()->with('success','Ward Saved Successfully!');
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }

    public function updateWard(Request $req){
        if(Session::get('adminLoggedin')==true){
            $id=$req['entryId'];
            $validRegion=Wards::find($id);

            if($validRegion){
                $req->validate(
                    [
                        'updatedWardName'=>'required|unique:wards,wardName,'.$id.',wardId',
                        'updatedWardCode'=>'required|unique:wards,wardCode,'.$id.',wardId'
                    ]
                ); 
    
                $validRegion['wardName']=$req['updatedWardName'];
                $validRegion['wardCode']=$req['updatedWardCode'];
                $validRegion->save();
    
                return back()->with('success','Ward Updated Successfully!');
            }
            else{
                return back()->with('error','Ward Not Found!');
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }

    public function wardInfo($id){
        if(Session::get('adminLoggedin')==true){
            $regionData=Wards::find($id);

            if($regionData){
                $data=[];
                $data['fieldName']=$regionData['wardName'];
                $data['fieldCode']=$regionData['wardCode'];

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

    public function deleteWard(Request $req){
        if(Session::get('adminLoggedin')==true){
            $id=$req['delEntryId'];
            $validRegion=Wards::find($id);

            if($validRegion){
                $validRegion['wardName']=$validRegion['wardName'].".";
                $validRegion['wardCode']='#';
                $validRegion['isDeleted']=1;
                $validRegion->save();
    
                return back()->with('success','Ward Deleted Successfully!');
            }
            else{
                return back()->with('error','Ward Not Found!');
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }
}
