<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Regions;
use Session;

class RegionController extends Controller
{
    public function regions(){
        if(Session::get('adminLoggedin')==true){
            $regions=Regions::select('regionId','regionName','regionCode')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('regionName','asc')->get();

            session(['pageTitle'=>"Regions"]);
            $url1=url('/region/save');
            $url2=url('/region/update');
            $url3=url('/region/delete');
         
            $data=compact('regions','url1','url2','url3');
            return view('admin.regions')->with($data);
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');
        }
    }

    public function saveRegion(Request $req){
        if(Session::get('adminLoggedin')==true){
            $req->validate(
                [
                    'regionName'=>'required|unique:regions,regionName',
                    'regionCode'=>'required|unique:regions,regionCode'
                ]
            ); 

            $region=new Regions;
            $region['regionName']=$req['regionName'];
            $region['regionCode']=$req['regionCode'];
            $region->save();

            return back()->with('success','Region Saved Successfully!');
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }

    public function updateRegion(Request $req){
        if(Session::get('adminLoggedin')==true){
            $id=$req['entryId'];
            $validRegion=Regions::find($id);

            if($validRegion){
                $req->validate(
                    [
                        'updatedRegionName'=>'required|unique:regions,regionName,'.$id.',regionId',
                        'updatedRegionCode'=>'required|unique:regions,regionCode,'.$id.',regionId'
                    ]
                ); 
    
                $validRegion['regionName']=$req['updatedRegionName'];
                $validRegion['regionCode']=$req['updatedRegionCode'];
                $validRegion->save();
    
                return back()->with('success','Region Updated Successfully!');
            }
            else{
                return back()->with('error','Region Not Found!');
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }

    public function regionInfo($id){
        if(Session::get('adminLoggedin')==true){
            $regionData=Regions::find($id);

            if($regionData){
                $data=[];
                $data['fieldName']=$regionData['regionName'];
                $data['fieldCode']=$regionData['regionCode'];

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

    public function deleteRegion(Request $req){
        if(Session::get('adminLoggedin')==true){
            $id=$req['delEntryId'];
            $validRegion=Regions::find($id);

            if($validRegion){
                $validRegion['regionName']=$validRegion['regionName'].".";
                $validRegion['regionCode']='#';
                $validRegion['isDeleted']=1;
                $validRegion->save();
    
                return back()->with('success','Region Deleted Successfully!');
            }
            else{
                return back()->with('error','Region Not Found!');
            }
        }
        else{
            return redirect('/')->with('accessDenied','Session Expired!');  
        }
    }
}
