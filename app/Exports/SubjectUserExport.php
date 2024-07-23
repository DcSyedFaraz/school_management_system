<?php

namespace App\Exports;

use App\Models\Marks;
use App\Models\Ranks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Support\Facades\Config;
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

    protected $subjects;

public function __construct($examId, $classId, $startDate, $endDate, $borderLine)
{
    $this->examId = $examId;
    $this->classId = $classId;
    $this->startDate = $startDate;
    $this->endDate = $endDate;
    $this->borderLine = $borderLine;
    $this->rank = Ranks::select('rankName','rankRangeMin','rankRangeMax')->where([
        ['isActive','=','1'],
        ['isDeleted','=','0']
    ])->orderBy('rankName','asc')->get();

    // Load subjects dynamically based on classId
    $this->subjects = Config::get("subjects.{$classId}", Config::get('subjects.class_default'));
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
        $headings = [
            'Sr.No',
            'MKoa',
            'Wilaya',
            'Kata',
            'Shule',
            'Waliofanya(Wav)',
            'Waliofanya(Was)',
            'Waliofanya(Jml)',
        ];

        foreach ($this->subjects as $subject) {
            $headings[] = ucfirst($subject) . '(A)';
            $headings[] = ucfirst($subject) . '(B)';
            $headings[] = ucfirst($subject) . '(C)';
            $headings[] = ucfirst($subject) . '(D)';
            $headings[] = ucfirst($subject) . '(E)';
            $headings[] = ucfirst($subject) . '(Jml)';
        }

        return $headings;
    }


    public function map($markData): array
{
    $regionData = \App\Models\Regions::find($markData['regionId']);
    $regionName = ($regionData) ? $regionData['regionName'] : 'Not Found!';

    $districtData = \App\Models\Districts::find($markData['districtId']);
    $districtName = ($districtData) ? $districtData['districtName'] : 'Not Found!';

    $wardData = \App\Models\Wards::find($markData['wardId']);
    $wardName = ($wardData) ? $wardData['wardName'] : 'Not Found!';

    $schoolData = \App\Models\Schools::find($markData['schoolId']);
    $schoolName = ($schoolData) ? $schoolData['schoolName'] : 'Not Found!';

    $examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];
    $classCondition = ($this->classId == '') ? ['classId', '!=', null] : ['classId', '=', $this->classId];
    $startDate = ($this->startDate == '') ? date('Y-m-d', strtotime("2023-01-01")) : $this->startDate;
    $endDate = ($this->endDate == '') ? date('Y-m-d') : $this->endDate;

    $marks = Marks::select($this->subjects)->where([
        ['isActive', '=', '1'],
        ['isDeleted', '=', '0'],
        $classCondition,
        $examCondition,
        ['userId', '=', Session::get('userId')]
    ])->whereBetween('examDate', [$startDate, $endDate])->get();

    $malePassed = \App\Models\Marks::where([
        ['isActive', '=', '1'],
        ['isDeleted', '=', '0'],
        ['gender', '=', 'M'],
        ['schoolId', '=', $markData['schoolId']],
        ['classId', '=', $this->classId],
        ['examId', '=', $this->examId]
    ])->whereRaw('ROUND(((' . implode('+', $this->subjects) . ') / ' . count($this->subjects) . '), 2) != ?', [0])
        ->whereBetween('examDate', [$startDate, $endDate])->count();

    $femalePassed = \App\Models\Marks::where([
        ['isActive', '=', '1'],
        ['isDeleted', '=', '0'],
        ['gender', '=', 'F'],
        ['schoolId', '=', $markData['schoolId']],
        ['classId', '=', $this->classId],
        ['examId', '=', $this->examId]
    ])->whereRaw('ROUND(((' . implode('+', $this->subjects) . ') / ' . count($this->subjects) . '), 2) != ?', [0])
        ->whereBetween('examDate', [$startDate, $endDate])->count();

    $gradeArray = [];
    foreach ($marks as $aMark) {
        if (array_sum($aMark->toArray()) != 0) {
            foreach ($this->subjects as $subject) {
                $grade = $this->assignGrade($aMark[$subject]);
                $gradeArray[] = $subject . $grade;
            }
        }
    }

    $groupArray = array_count_values($gradeArray);
    static $serialNumber = 0;
    $serialNumber++;

    $mappedData = [
        $serialNumber,
        $regionName,
        $districtName,
        $wardName,
        $schoolName,
        $malePassed != 0 ? $malePassed : "0",
        $femalePassed != 0 ? $femalePassed : "0",
        ($malePassed + $femalePassed) != 0 ? ($malePassed + $femalePassed) : "0",
    ];

    foreach ($this->subjects as $subject) {
        foreach (['A', 'B', 'C', 'D', 'E'] as $grade) {
            $mappedData[] = $groupArray[$subject . $grade] ?? "0";
        }
        $subjectTotal = 0;
        foreach (['A', 'B', 'C', 'D', 'E'] as $grade) {
            $subjectTotal += $groupArray[$subject . $grade] ?? 0;
        }
        $mappedData[] = $subjectTotal != 0 ? $subjectTotal : "0";
    }

    return $mappedData;
}


function assignGrade($marks)
{
    foreach ($this->rank as $rank) {
        if ($rank['rankRangeMin'] < $marks && $rank['rankRangeMax'] >= $marks) {
            return $rank['rankName'];
        }
    }
    return "Null";
}

}
