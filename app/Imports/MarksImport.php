<?php

namespace App\Imports;

use App\Models\Marks;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class MarksImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $requestData;
    protected $userId;
    protected $userRegion;
    protected $userDistrict;
    protected $userWard;
    protected $userSchool;

    public function __construct($requestData, $userId, $userRegion, $userDistrict, $userWard, $userSchool){
        $this->requestData = $requestData;
        $this->userId = $userId;
        $this->userRegion = $userRegion;
        $this->userDistrict = $userDistrict;
        $this->userWard = $userWard;
        $this->userSchool = $userSchool;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows){
        foreach ($rows as $row){
            $gender=($row['gender']=='M' || $row['gender']=='1')?"M":"F";
            $firstGrade=($row['firstgrade']=='Y' || $row['firstgrade']=='1')?"1":"0";

            $markData=new Marks;
            $markData['examDate']=$this->requestData['examDate'];
            $markData['classId']=$this->requestData['class'];
            $markData['studentName']=$row['studentname'];
            $markData['gender']=$gender;
            $markData['firstGrade']=$firstGrade;
            $markData['hisabati']=$row['hisabati'];
            $markData['kiswahili']=$row['kiswahili'];
            $markData['sayansi']=$row['sayansi'];
            $markData['english']=$row['english'];
            $markData['jamii']=$row['jamii'];
            $markData['maadili']=$row['maadili'];
            $markData['total']=$row['hisabati']+$row['kiswahili']+$row['sayansi']+$row['english']+$row['jamii']+$row['maadili'];
            $markData['average']=number_format((($row['hisabati']+$row['kiswahili']+$row['sayansi']+$row['english']+$row['jamii']+$row['maadili'])/6), 2);
            $markData['examId']=$this->requestData['exam'];
            $markData['userId']=$this->userId;
            $markData['regionId']=$this->userRegion;
            $markData['districtId']=$this->userDistrict;
            $markData['wardId']=$this->userWard;
            $markData['schoolId']=$this->userSchool;
            $markData->save();
        }
    }

    public function rules(): array
    {
        return [
            'studentname' => 'required|string',
            'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
            'firstgrade' => ['required', Rule::in(['Y', 'N', '1', '2'])],
            'hisabati' => 'required|numeric|min:0|max:50',
            'kiswahili' => 'required|numeric|min:0|max:50',
            'sayansi' => 'required|numeric|min:0|max:50',
            'english' => 'required|numeric|min:0|max:50',
            'jamii' => 'required|numeric|min:0|max:50',
            'maadili' => 'required|numeric|min:0|max:50',
        ];
    }

    // public function onValidationFailure(Collection $failures)
    // {
    //     return Redirect::to('/dashboard/uploads')->withErrors($failures);
    // }
}
