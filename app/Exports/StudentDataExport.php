<?php

namespace App\Exports;

use DB;
use App\Models\Marks;
use App\Models\Schools;
use App\Models\Ranks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Wards;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Config;

class StudentDataExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithChunkReading
{
    protected $examId;
    protected $classId;
    protected $regionId;
    protected $districtId;
    protected $wardId;
    protected $startDate;
    protected $endDate;
    protected $rank;

    protected $subjects;

    public function __construct($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate){
        $this->examId = $examId;
        $this->classId = $classId;
        $this->regionId = $regionId;
        $this->districtId = $districtId;
        $this->wardId = $wardId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
    public function collection(){
        $classCondition=($this->classId=='')?['classId','!=',null]:['classId','=',$this->classId];
        $examCondition=($this->examId=='')?['examId','!=',null]:['examId','=',$this->examId];
        $regionCondition=($this->regionId=='')?['regionId','!=',null]:['regionId','=',$this->regionId];
        $districtCondition=($this->districtId=='')?['districtId','!=',null]:['districtId','=',$this->districtId];
        $wardCondition=($this->wardId=='')?['wardId','!=',null]:['wardId','=',$this->wardId];

        $columns = array_merge(
            ['markId', 'gender', 'studentName', 'classId', 'examId', 'schoolId', 'regionId', 'districtId', 'wardId'],
            $this->subjects,
            ['total', 'average']
        );

        $marks = Marks::select($columns)->where([
            ['isActive','=','1'],
            ['isDeleted','=','0'],
            $classCondition,
            $examCondition,
            $regionCondition,
            $districtCondition,
            $wardCondition
        ])
        ->whereBetween('examDate', [$this->startDate, $this->endDate])
        ->orderBy('average', 'desc')
        ->get();

        return $marks;
    }


    public function chunkSize(): int
    {
        return 100; // Adjust chunk size as needed
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
        ];
    }

    public function headings(): array
{
    $headings = [
        'Sr.No',
        'Jina La Shule',
        'Darasa',
        'Mtihani',
        'Shule',
        'Mkoa',
        'Wilaya',
        'Kata',
    ];

    foreach ($this->subjects as $subject) {
        $headings[] = ucfirst($subject);
        $headings[] = 'Grade';
    }

    $headings = array_merge($headings, [
        'Jumla',
        'Wastani',
        'Daraja',
        'Nafasi',
        'Ufaulu'
    ]);

    return $headings;
}


public function map($marks): array
{
    $schoolData = Schools::find($marks->schoolId);
    $schoolName = ($schoolData) ? $schoolData['schoolName'] : "Not Found";

    $classData = Grades::find($marks->classId);
    $className = ($classData) ? $classData['gradeName'] : "Not Found";

    $examData = Exams::find($marks->examId);
    $examName = ($examData) ? $examData['examName'] : "Not Found";

    $regionData = Regions::find($marks->regionId);
    $regionName = ($regionData) ? $regionData['regionName'] : "Not Found";

    $districtData = Districts::find($marks->districtId);
    $districtName = ($districtData) ? $districtData['districtName'] : "Not Found";

    $wardData = Wards::find($marks->wardId);
    $wardName = ($wardData) ? $wardData['wardName'] : "Not Found";

    static $storedAvg = '';
    static $serialNumber = 0;
    static $j = 0;

    $serialNumber++;

    if ($storedAvg == $marks->average) {
        $j++;
        $rank = $serialNumber - $j;
        $storedAvg = $marks->average;
    } else {
        $j = 0;
        $rank = $serialNumber;
        $storedAvg = $marks->average;
    }

    $gradeVal = ($marks->average > 0) ? $this->assignGrade($marks->average) : "ABS";

    $mappedData = [
        $serialNumber,
        $marks->studentName,
        $className,
        $examName,
        $schoolName,
        $regionName,
        $districtName,
        $wardName,
    ];

    foreach ($this->subjects as $subject) {
        $subjectMarks = $marks->$subject > 0 ? $marks->$subject : "0";
        $mappedData[] = $subjectMarks;
        $mappedData[] = $this->assignGrade($subjectMarks);
    }

    $mappedData = array_merge($mappedData, [
        $marks->total > 0 ? $marks->total : "0",
        $marks->average > 0 ? $marks->average : "0",
        $gradeVal,
        $rank,
        $this->finalStatus($marks->average)
    ]);

    return $mappedData;
}


function assignGrade($marks){
    foreach ($this->rank as $rank) {
        if ($rank['rankRangeMin'] <= $marks && $rank['rankRangeMax'] >= $marks) {
            return $rank['rankName'];
        }
    }
    return "Null";
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
