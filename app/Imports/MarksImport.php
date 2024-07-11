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
            $firstGrade = ($row['firstgrade'] == 'Y' || $row['firstgrade'] == '1') ? "1" : "0";

            $markData = new Marks;
            $markData['examDate'] = $this->requestData['examDate'];
            $markData['classId'] = $this->requestData['class'];
            $markData['studentName'] = $row['studentname'];
            $markData['gender'] = $gender;
            $markData['firstGrade'] = $firstGrade;

            if ($this->requestData['class'] == 1) {
                $markData['kuhesabu'] = $row['kuhesabu'] ?? 0;
                $markData['kusoma'] = $row['kusoma'] ?? 0;
                $markData['kuandika'] = $row['kuandika'] ?? 0;
                $markData['english'] = $row['english'] ?? 0;
                $markData['mazingira'] = $row['mazingira'] ?? 0;
                $markData['michezo'] = $row['michezo'] ?? 0;
                $total = $row['kuhesabu'] + $row['kusoma'] + $row['kuandika'] + $row['english'] + $row['mazingira'] + $row['michezo'];
            } elseif ($this->requestData['class'] == 2) {
                $markData['kuhesabu'] = $row['kuhesabu'] ?? 0;
                $markData['kusoma'] = $row['kusoma'] ?? 0;
                $markData['kuandika'] = $row['kuandika'] ?? 0;
                $markData['english'] = $row['english'] ?? 0;
                $markData['mazingira'] = $row['mazingira'] ?? 0;
                $markData['utamaduni'] = $row['utamaduni'] ?? 0;
                $total = $row['kuhesabu'] + $row['kusoma'] + $row['kuandika'] + $row['english'] + $row['mazingira'] + $row['utamaduni'];
            } elseif ($this->requestData['class'] == 3) {
                $markData['hisabati'] = $row['hisabati'] ?? 0;
                $markData['kiswahili'] = $row['kiswahili'] ?? 0;
                $markData['sayansi'] = $row['sayansi'] ?? 0;
                $markData['english'] = $row['english'] ?? 0;
                $markData['maadili'] = $row['maadili'] ?? 0;
                $markData['jiographia'] = $row['jiographia'] ?? 0;
                $markData['smichezo'] = $row['smichezo'] ?? 0;
                $total = $row['hisabati'] + $row['kiswahili'] + $row['sayansi'] + $row['english'] + $row['maadili'] + $row['jiographia'] + $row['smichezo'];
            } else { // classes 4 to 7
                $markData['hisabati'] = $row['hisabati'] ?? 0;
                $markData['kiswahili'] = $row['kiswahili'] ?? 0;
                $markData['sayansi'] = $row['sayansi'] ?? 0;
                $markData['english'] = $row['english'] ?? 0;
                $markData['jamii'] = $row['jamii'] ?? 0;
                $markData['maadili'] = $row['maadili'] ?? 0;
                $total = $row['hisabati'] + $row['kiswahili'] + $row['sayansi'] + $row['english'] + $row['jamii'] + $row['maadili'];
            }

            $markData['total'] = $total;
            $markData['average'] = number_format($total / count(array_filter($row->toArray(), 'is_numeric')), 2);
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
        return match ($this->requestData['class']) {
          1 => [
                    'studentname' => 'required|string',
                    'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
                    'firstgrade' => ['required', Rule::in(['Y', 'N', '1', '2'])],
                    'kuhesabu' => 'required|numeric|min:0|max:50',
                    'kusoma' => 'required|numeric|min:0|max:50',
                    'kuandika' => 'required|numeric|min:0|max:50',
                    'english' => 'required|numeric|min:0|max:50',
                    'mazingira' => 'required|numeric|min:0|max:50',
                    'michezo' => 'required|numeric|min:0|max:50',
                ],
          2 => [
                    'studentname' => 'required|string',
                    'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
                    'firstgrade' => ['required', Rule::in(['Y', 'N', '1', '2'])],
                    'kuhesabu' => 'required|numeric|min:0|max:50',
                    'kusoma' => 'required|numeric|min:0|max:50',
                    'kuandika' => 'required|numeric|min:0|max:50',
                    'english' => 'required|numeric|min:0|max:50',
                    'mazingira' => 'required|numeric|min:0|max:50',
                    'utamaduni' => 'required|numeric|min:0|max:50',
                ],
          3 => [
                    'studentname' => 'required|string',
                    'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
                    'firstgrade' => ['required', Rule::in(['Y', 'N', '1', '2'])],
                    'hisabati' => 'required|numeric|min:0|max:50',
                    'kiswahili' => 'required|numeric|min:0|max:50',
                    'sayansi' => 'required|numeric|min:0|max:50',
                    'english' => 'required|numeric|min:0|max:50',
                    'maadili' => 'required|numeric|min:0|max:50',
                    'jiographia' => 'required|numeric|min:0|max:50',
                    'smichezo' => 'required|numeric|min:0|max:50',
                ],
          default => [
                    'studentname' => 'required|string',
                    'gender' => ['required', Rule::in(['M', 'F', '1', '2'])],
                    'firstgrade' => ['required', Rule::in(['Y', 'N', '1', '2'])],
                    'hisabati' => 'required|numeric|min:0|max:50',
                    'kiswahili' => 'required|numeric|min:0|max:50',
                    'sayansi' => 'required|numeric|min:0|max:50',
                    'english' => 'required|numeric|min:0|max:50',
                    'jamii' => 'required|numeric|min:0|max:50',
                    'maadili' => 'required|numeric|min:0|max:50',
                ],
        };
    }
}
