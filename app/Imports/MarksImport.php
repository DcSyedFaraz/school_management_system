<?php

namespace App\Imports;

use App\Models\Marks;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class MarksImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $requestData;
    protected $userId;
    protected $userRegion;
    protected $userDistrict;
    protected $userWard;
    protected $userSchool;

    public function __construct($requestData, $userId, $userRegion, $userDistrict, $userWard, $userSchool)
    {
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
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $gender = ($row['gender'] == 'M' || $row['gender'] == '1') ? "M" : "F";

            $markData = new Marks;
            $markData['examDate'] = $this->requestData['examDate'];
            $markData['classId'] = $this->requestData['class'];
            $markData['studentName'] = $row['studentname'];
            $markData['gender'] = $gender;

            $subjects = [];

            $subjects = config('subjects.' . $this->requestData['class'], config('subjects.class_default'));
            ;

            $total = 0;
            foreach ($subjects as $subject) {
                $markData[$subject] = $row[$subject] ?? 0;
                $total += $markData[$subject];
            }

            $markData['total'] = $total;
            $markData['average'] = number_format($total / count($subjects), 2);
            $markData['examId'] = $this->requestData['exam'];
            $markData['userId'] = $this->userId;
            $markData['regionId'] = $this->userRegion;
            $markData['districtId'] = $this->userDistrict;
            $markData['wardId'] = $this->userWard;
            $markData['schoolId'] = $this->userSchool;
            $markData->save();
        }
    }

    public function rules(): array
    {
        // Define common rules that are the same for all classes.
        $rules = [
            'studentname' => 'required|string',
            'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
        ];

        // Retrieve the list of subjects for the given class.
        // If the class is not found, fallback to the default subjects.
        $classId = $this->requestData['class'];
        $subjects = config("subjects.{$classId}", config('subjects.class_default'));

        // Loop through each subject and add the same numeric validation rule.
        foreach ($subjects as $subject) {
            $rules[$subject] = 'required|numeric|min:0|max:50';
        }

        return $rules;
    }


    // public function rules(): array
    // {
    //     return match ($this->requestData['class']) {
    //         '1' => [
    //             'studentname' => 'required|string',
    //             'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
    //             'firstgrade' => ['required', Rule::in(['Y', 'N', '1', '2'])],
    //             'kuhesabu' => 'required|numeric|min:0|max:50',
    //             'kusoma' => 'required|numeric|min:0|max:50',
    //             'kuandika' => 'required|numeric|min:0|max:50',
    //             'english' => 'required|numeric|min:0|max:50',
    //             'mazingira' => 'required|numeric|min:0|max:50',
    //             'michezo' => 'required|numeric|min:0|max:50',
    //         ],
    //         '2' => [
    //             'studentname' => 'required|string',
    //             'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
    //             'firstgrade' => ['required', Rule::in(['Y', 'N', '1', '2'])],
    //             'kuhesabu' => 'required|numeric|min:0|max:50',
    //             'kusoma' => 'required|numeric|min:0|max:50',
    //             'kuandika' => 'required|numeric|min:0|max:50',
    //             'english' => 'required|numeric|min:0|max:50',
    //             'mazingira' => 'required|numeric|min:0|max:50',
    //             'utamaduni' => 'required|numeric|min:0|max:50',
    //         ],
    //         '3' => [
    //             'studentname' => 'required|string',
    //             'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
    //             'firstgrade' => ['required', Rule::in(['Y', 'N', '1', '2'])],
    //             'hisabati' => 'required|numeric|min:0|max:50',
    //             'kiswahili' => 'required|numeric|min:0|max:50',
    //             'sayansi' => 'required|numeric|min:0|max:50',
    //             'english' => 'required|numeric|min:0|max:50',
    //             'maadili' => 'required|numeric|min:0|max:50',
    //             'jiographia' => 'required|numeric|min:0|max:50',
    //             'smichezo' => 'required|numeric|min:0|max:50',
    //         ],
    //         default => [
    //             'studentname' => 'required|string',
    //             'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
    //             'firstgrade' => ['required', Rule::in(['Y', 'N', '1', '2'])],
    //             'hisabati' => 'required|numeric|min:0|max:50',
    //             'kiswahili' => 'required|numeric|min:0|max:50',
    //             'sayansi' => 'required|numeric|min:0|max:50',
    //             'english' => 'required|numeric|min:0|max:50',
    //             'jamii' => 'required|numeric|min:0|max:50',
    //             'maadili' => 'required|numeric|min:0|max:50',
    //         ],
    //     };
    // }
}
