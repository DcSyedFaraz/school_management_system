<?php

namespace App\Exports;

use App\Models\Marks;
use App\Models\Ranks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Session;
use DB;

class SubjectUserExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
    protected $examId;
    protected $classId;
    protected $startDate;
    protected $endDate;
    protected $borderLine;
    protected $rank;

    public function __construct($examId, $classId, $startDate, $endDate, $borderLine){
        $this->examId = $examId;
        $this->classId = $classId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->borderLine =$borderLine;
        $this->rank=Ranks::select('rankName','rankRangeMin','rankRangeMax')->where([
            ['isActive','=','1'],
            ['isDeleted','=','0']
        ])->orderBy('rankName','asc')->get();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $classId=$this->classId;
        $examId=$this->examId;

        $examCondition=($examId=='')?['examId','!=',null]:['examId','=',$examId];
        $classCondition=($classId=='')?['classId','!=',null]:['classId','=',$classId];
        $startDate=($this->startDate=='')?date('Y-m-d', strtotime("2023-01-01")):$this->startDate;
        $endDate=($this->endDate=='')?date('Y-m-d'):$this->endDate;

        $markData = Marks::select('regionId','districtId','wardId','schoolId')->where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            $classCondition,
            $examCondition,
            ['userId','=',Session::get('userId')]
        ])->whereBetween('examDate', [$startDate, $endDate])->limit(1)->get();

        return $markData;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 20,
            'N' => 20,
            'O' => 20,
            'P' => 20,
            'Q' => 20,
            'R' => 20,
            'S' => 20,
            'T' => 20,
            'U' => 20,
            'V' => 20,
            'W' => 20,
            'X' => 20,
            'Y' => 20,
            'Z' => 20,
            'AA' => 20,
            'AB' => 20,
            'AC' => 20,
            'AD' => 20,
            'AE' => 20,
            'AF' => 20,
            'AG' => 20,
            'AH' => 20,
            'AI' => 20,
            'AJ' => 20,
            'AK' => 20,
            'AL' => 20,
            'AM' => 20,
            'AN' => 20,
            'AO' => 20,
            'AP' => 20,
            'AQ' => 20,
            'AR' => 20,
            'AS' => 20,
        ];
    }

    public function headings(): array
    {
        return [
            'Sr.No',
            'MKoa',
            'Wilaya',
            'Kata',
            'Shule',
            'Waliofanya(Wav)',
            'Waliofanya(Was)',
            'Waliofanya(Jml)',
            'Hisabati(A)',
            'Hisabati(B)',
            'Hisabati(C)',
            'Hisabati(D)',
            'Hisabati(E)',
            'Hisabati(Jml)',
            'Kiswahili(A)',
            'Kiswahili(B)',
            'Kiswahili(C)',
            'Kiswahili(D)',
            'Kiswahili(E)',
            'Kiswahili(Jml)',
            'Sayansi(A)',
            'Sayansi(B)',
            'Sayansi(C)',
            'Sayansi(D)',
            'Sayansi(E)',
            'Sayansi(Jml)',
            'English(A)',
            'English(B)',
            'English(C)',
            'English(D)',
            'English(E)',
            'English(Jml)',
            'Jamii(A)',
            'Jamii(B)',
            'Jamii(C)',
            'Jamii(D)',
            'Jamii(E)',
            'Jamii(Jml)',
            'Maadili(A)',
            'Maadili(B)',
            'Maadili(C)',
            'Maadili(D)',
            'Maadili(E)',
            'Maadili(Jml)',
        ]; 
    }

    public function map($markData): array
    {
        $regionData=\App\Models\Regions::find($markData['regionId']);
        $regionName=($regionData)?$regionData['regionName']:'Not Found!';

        $districtData=\App\Models\Districts::find($markData['districtId']);
        $districtName=($districtData)?$districtData['districtName']:'Not Found!';

        $wardData=\App\Models\Wards::find($markData['wardId']);
        $wardName=($wardData)?$wardData['wardName']:'Not Found!';

        $schoolData=\App\Models\Schools::find($markData['schoolId']);
        $schoolName=($schoolData)?$schoolData['schoolName']:'Not Found!';

        $examCondition=($this->examId=='')?['examId','!=',null]:['examId','=',$this->examId];
        $classCondition=($this->classId=='')?['classId','!=',null]:['classId','=',$this->classId];
        $startDate=($this->startDate=='')?date('Y-m-d', strtotime("2023-01-01")):$this->startDate;
        $endDate=($this->endDate=='')?date('Y-m-d'):$this->endDate;

        $marks = Marks::select('hisabati','kiswahili','sayansi','english','jamii','maadili')->where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            $classCondition,
            $examCondition,
            ['userId','=',Session::get('userId')]
        ])->whereBetween('examDate', [$startDate, $endDate])->get();

        $malePassed=\App\Models\Marks::where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            ['gender','=','M'],
            ['schoolId','=',$markData['schoolId']],
            ['classId','=',$this->classId],
            ['examId','=',$this->examId]
        ])->whereRaw('ROUND(((hisabati+kiswahili+sayansi+english+jamii+maadili) / 6), 2) != ?', [0])->whereBetween('examDate', [$startDate, $endDate])->count();

        $femalePassed=\App\Models\Marks::where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            ['gender','=','F'],
            ['schoolId','=',$markData['schoolId']],
            ['classId','=',$this->classId],
            ['examId','=',$this->examId]
        ])->whereRaw('ROUND(((hisabati+kiswahili+sayansi+english+jamii+maadili) / 6), 2) != ?', [0])->whereBetween('examDate', [$startDate, $endDate])->count();

        $gradeArray=[];
        $subList=['hisabati','kiswahili','sayansi','english','jamii','maadili'];

        foreach ($marks as $aMark) {
            if(($aMark['hisabati']+$aMark['kiswahili']+$aMark['sayansi']+$aMark['english']+$aMark['jamii']+$aMark['maadili'])!=0){
                foreach ($subList as $list) {
                    if($this->assignGrade($aMark[$list])=='A'){
                        array_push($gradeArray, ''.substr($list, 0, 1).'A');
                    }
                    else if($this->assignGrade($aMark[$list])=='B'){
                        array_push($gradeArray, ''.substr($list, 0, 1).'B');
                    }
                    else if($this->assignGrade($aMark[$list])=='C'){
                        array_push($gradeArray, ''.substr($list, 0, 1).'C');
                    }
                    else if($this->assignGrade($aMark[$list])=='D'){
                        array_push($gradeArray, ''.substr($list, 0, 1).'D');
                    }
                    else{
                        array_push($gradeArray, ''.substr($list, 0, 1).'E');
                    }
                }
            }
        }

        $groupArray = array_count_values($gradeArray); 
        static $serialNumber = 0;
        $serialNumber++;

        return [
            $serialNumber,
            $regionName,
            $districtName,
            $wardName,
            $schoolName,
            ($malePassed!=0)?$malePassed:"0",
            ($femalePassed!=0)?$femalePassed:"0",
            (($malePassed+$femalePassed)!=0)?($malePassed+$femalePassed):"0",
            (array_key_exists(''.substr($subList[0], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'A']:"0",
            (array_key_exists(''.substr($subList[0], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'B']:"0",
            (array_key_exists(''.substr($subList[0], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'C']:"0",
            (array_key_exists(''.substr($subList[0], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'D']:"0",
            (array_key_exists(''.substr($subList[0], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'E']:"0",
            (((array_key_exists(''.substr($subList[0], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'A']:"0")+((array_key_exists(''.substr($subList[0], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'B']:"0")+((array_key_exists(''.substr($subList[0], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'C']:"0")+((array_key_exists(''.substr($subList[0], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'D']:"0")+((array_key_exists(''.substr($subList[0], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[0], 0, 1).'E']:"0")),
            (array_key_exists(''.substr($subList[1], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'A']:"0",
            (array_key_exists(''.substr($subList[1], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'B']:"0",
            (array_key_exists(''.substr($subList[1], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'C']:"0",
            (array_key_exists(''.substr($subList[1], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'D']:"0",
            (array_key_exists(''.substr($subList[1], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'E']:"0",
            (((array_key_exists(''.substr($subList[1], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'A']:"0")+((array_key_exists(''.substr($subList[1], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'B']:"0")+((array_key_exists(''.substr($subList[1], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'C']:"0")+((array_key_exists(''.substr($subList[1], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'D']:"0")+((array_key_exists(''.substr($subList[1], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[1], 0, 1).'E']:"0")),
            (array_key_exists(''.substr($subList[2], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'A']:"0",
            (array_key_exists(''.substr($subList[2], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'B']:"0",
            (array_key_exists(''.substr($subList[2], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'C']:"0",
            (array_key_exists(''.substr($subList[2], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'D']:"0",
            (array_key_exists(''.substr($subList[2], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'E']:"0",
            (((array_key_exists(''.substr($subList[2], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'A']:"0")+((array_key_exists(''.substr($subList[2], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'B']:"0")+((array_key_exists(''.substr($subList[2], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'C']:"0")+((array_key_exists(''.substr($subList[2], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'D']:"0")+((array_key_exists(''.substr($subList[2], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[2], 0, 1).'E']:"0")),
            (array_key_exists(''.substr($subList[3], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'A']:"0",
            (array_key_exists(''.substr($subList[3], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'B']:"0",
            (array_key_exists(''.substr($subList[3], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'C']:"0",
            (array_key_exists(''.substr($subList[3], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'D']:"0",
            (array_key_exists(''.substr($subList[3], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'E']:"0",
            (((array_key_exists(''.substr($subList[3], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'A']:"0")+((array_key_exists(''.substr($subList[3], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'B']:"0")+((array_key_exists(''.substr($subList[3], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'C']:"0")+((array_key_exists(''.substr($subList[3], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'D']:"0")+((array_key_exists(''.substr($subList[3], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[3], 0, 1).'E']:"0")),
            (array_key_exists(''.substr($subList[4], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'A']:"0",
            (array_key_exists(''.substr($subList[4], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'B']:"0",
            (array_key_exists(''.substr($subList[4], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'C']:"0",
            (array_key_exists(''.substr($subList[4], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'D']:"0",
            (array_key_exists(''.substr($subList[4], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'E']:"0",
            (((array_key_exists(''.substr($subList[4], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'A']:"0")+((array_key_exists(''.substr($subList[4], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'B']:"0")+((array_key_exists(''.substr($subList[4], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'C']:"0")+((array_key_exists(''.substr($subList[4], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'D']:"0")+((array_key_exists(''.substr($subList[4], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[4], 0, 1).'E']:"0")),
            (array_key_exists(''.substr($subList[5], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'A']:"0",
            (array_key_exists(''.substr($subList[5], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'B']:"0",
            (array_key_exists(''.substr($subList[5], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'C']:"0",
            (array_key_exists(''.substr($subList[5], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'D']:"0",
            (array_key_exists(''.substr($subList[5], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'E']:"0",
            (((array_key_exists(''.substr($subList[5], 0, 1).'A', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'A']:"0")+((array_key_exists(''.substr($subList[5], 0, 1).'B', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'B']:"0")+((array_key_exists(''.substr($subList[5], 0, 1).'C', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'C']:"0")+((array_key_exists(''.substr($subList[5], 0, 1).'D', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'D']:"0")+((array_key_exists(''.substr($subList[5], 0, 1).'E', $groupArray))?$groupArray[''.substr($subList[5], 0, 1).'E']:"0")),
        ];
    }

    function assignGrade($marks){
        if($this->rank){
            if($this->rank[0]['rankRangeMin']<$marks && $this->rank[0]['rankRangeMax']>=$marks){
                return $this->rank[0]['rankName'];
            }
            else if($this->rank[1]['rankRangeMin']<$marks && $this->rank[1]['rankRangeMax']>=$marks){
                return $this->rank[1]['rankName'];
            }
            else if($this->rank[2]['rankRangeMin']<$marks && $this->rank[2]['rankRangeMax']>=$marks){
                return $this->rank[2]['rankName'];
            }
            else if($this->rank[3]['rankRangeMin']<$marks && $this->rank[3]['rankRangeMax']>=$marks){
                return $this->rank[3]['rankName'];
            }
            else{
                return $this->rank[4]['rankName'];
            }
        }
        else{
            return "Null";
        }
    } 
}
