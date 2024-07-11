<?php

namespace App\Exports;

use App\Models\Marks;
use App\Models\Schools;
use App\Models\Ranks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class MarksExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
    protected $examId;
    protected $classId;
    protected $regionId;
    protected $districtId;
    protected $startDate;
    protected $endDate;
    protected $rank;

    public function __construct($examId, $classId, $regionId, $districtId, $startDate, $endDate){
        $this->examId = $examId;
        $this->classId = $classId;
        $this->regionId = $regionId;
        $this->districtId = $districtId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->rank=Ranks::select('rankName','rankRangeMin','rankRangeMax')->where([
            ['isActive','=','1'],
            ['isDeleted','=','0']
        ])->orderBy('rankName','asc')->get();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(){
        $classCondition=($this->classId=='')?['classId','!=',null]:['classId','=',$this->classId];
        $examCondition=($this->examId=='')?['examId','!=',null]:['examId','=',$this->examId];
        $regionCondition=($this->regionId=='')?['regionId','!=',null]:['regionId','=',$this->regionId];
        $districtCondition=($this->districtId=='')?['districtId','!=',null]:['districtId','=',$this->districtId];

        $marks = Marks::selectRaw('schoolId,
            ROUND(AVG(CASE WHEN hisabati > 0 THEN hisabati END), 2) as hisabati,
            ROUND(AVG(CASE WHEN kiswahili > 0 THEN kiswahili END), 2) as kiswahili,
            ROUND(AVG(CASE WHEN sayansi > 0 THEN sayansi END), 2) as sayansi,
            ROUND(AVG(CASE WHEN english > 0 THEN english END), 2) as english,
            ROUND(AVG(CASE WHEN jamii > 0 THEN jamii END), 2) as jamii,
            ROUND(AVG(CASE WHEN maadili > 0 THEN maadili END), 2) as maadili,
            ROUND(AVG(CASE WHEN average > 0 THEN average END), 2) as averageMarks')
        ->where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            $classCondition,
            $examCondition,
            $regionCondition,
            $districtCondition
        ])
        ->whereBetween('examDate', [$this->startDate, $this->endDate])
        ->groupBy('schoolId')
        ->orderBy('averageMarks', 'desc')
        ->get();

        return $marks;
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
        ];
    }

    public function headings(): array
    {
        return [
            'Sr.No',
            'Jina La Shule',
            'Hisabati',
            'Grade',
            'Kiswaili',
            'Grade',
            'Sayansi',
            'Grade',
            'English',
            'Grade',
            'Jamii',
            'Grade',
            'Maadili',
            'Grade',
            'Jumla',
            'Wastani',
            'Daraja',
            'Nafasi',
            'Ufaulu'
        ];
    }

    public function map($marks): array
    {
        $schoolData=Schools::find($marks['schoolId']);
        $schoolName=($schoolData)?$schoolData['schoolName']:"Not Found";

        $total=$marks->hisabati+$marks->kiswahili+$marks->sayansi+$marks->english+$marks->jamii+$marks->maadili;

        static $storedAvg='';
        static $serialNumber = 0;
        static $j = 0;

        $serialNumber++;

        if($storedAvg==$marks->averageMarks){
            $j++;
            $rank=$serialNumber-$j;
            $storedAvg=$marks->averageMarks;
        }
        else{
            $j=0;
            $rank=$serialNumber;
            $storedAvg=$marks->averageMarks;
        }

        return [
            $serialNumber,
            $schoolName,
            $marks->hisabati,
            $this->assignGrade($marks->hisabati),
            $marks->kiswahili,
            $this->assignGrade($marks->kiswahili),
            $marks->sayansi,
            $this->assignGrade($marks->sayansi),
            $marks->english,
            $this->assignGrade($marks->english),
            $marks->jamii,
            $this->assignGrade($marks->jamii),
            $marks->maadili,
            $this->assignGrade($marks->maadili),
            $total,
            $marks->averageMarks,
            $this->assignGrade($marks->averageMarks),
            $rank,
            $this->finalStatus($marks->averageMarks)
        ];
    }

    function assignGrade($marks){
        if($this->rank){
            if($this->rank[0]['rankRangeMin']<=$marks && $this->rank[0]['rankRangeMax']>=$marks){
                return $this->rank[0]['rankName'];
            }
            else if($this->rank[1]['rankRangeMin']<=$marks && $this->rank[1]['rankRangeMax']>=$marks){
                return $this->rank[1]['rankName'];
            }
            else if($this->rank[2]['rankRangeMin']<=$marks && $this->rank[2]['rankRangeMax']>=$marks){
                return $this->rank[2]['rankName'];
            }
            else if($this->rank[3]['rankRangeMin']<=$marks && $this->rank[3]['rankRangeMax']>=$marks){
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

    function finalStatus($average){
        if($this->classId>4){
            if($average<$this->rank[3]['rankRangeMax']){
                return "FAIL";
            }
            else{
                return "PASS";
            }
        }
        else{
            if($average<$this->rank[4]['rankRangeMax']){
                return "FAIL";
            }
            else{
                return "PASS";
            }
        }
    }
}
