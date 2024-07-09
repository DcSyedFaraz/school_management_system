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

class MarksUserExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
    protected $examId;
    protected $classId;
    protected $startDate;
    protected $endDate;
    protected $rank;

    public function __construct($examId, $classId, $startDate, $endDate){
        $this->examId = $examId;
        $this->classId = $classId;
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
    public function collection()
    {
        $classCondition=($this->classId=='')?['classId','!=',null]:['classId','=',$this->classId];
        $examCondition=($this->examId=='')?['examId','!=',null]:['examId','=',$this->examId];

        $marks=Marks::select('markId','studentName','hisabati','kiswahili','sayansi','english','jamii','maadili',
            DB::raw('ROUND(((hisabati + kiswahili + sayansi + english + jamii + maadili) / 6), 2) as averageMarks')
        )->where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            $classCondition,
            $examCondition,
            ['userId','=',Session::get('userId')]
        ])->whereBetween('examDate', [$this->startDate, $this->endDate])->orderBy('averageMarks','desc')->get();

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
            'Jinala Mwanafunzi',
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
        $total=$marks->hisabati+$marks->kiswahili+$marks->sayansi+$marks->english+$marks->jamii+$marks->maadili;

        $gradeStatus=($marks->averageMarks>0)?$this->assignGrade($marks->averageMarks):"ABS";
        $resultStatus=($marks->averageMarks>0)?$this->finalStatus($marks->averageMarks):"";

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
            $marks->studentName,
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
            $gradeStatus,
            $rank,
            $resultStatus
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
