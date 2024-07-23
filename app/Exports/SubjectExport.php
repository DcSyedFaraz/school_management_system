<?php

namespace App\Exports;

use App\Models\Marks;
use App\Models\Ranks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SubjectExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
    protected $examId;
    protected $classId;
    protected $regionId;
    protected $districtId;
    protected $wardId;
    protected $startDate;
    protected $endDate;
    protected $borderLine;
    protected $rank;
    protected $subjects;

    public function __construct($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate, $borderLine)
    {
        $this->examId = $examId;
        $this->classId = $classId;
        $this->regionId = $regionId;
        $this->districtId = $districtId;
        $this->wardId = $wardId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->borderLine = $borderLine;
        $this->rank = Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0']
        ])->orderBy('rankName', 'asc')->get();

        // Load subjects dynamically based on classId
        $this->subjects = Config::get("subjects.{$classId}", Config::get('subjects.class_default'));
    }

    public function collection()
    {
        $classId = $this->classId;
        $examId = $this->examId;
        $regionId = $this->regionId;
        $districtId = $this->districtId;
        $wardId = $this->wardId;

        $examCondition = $examId ? ['examId', '=', $examId] : ['examId', '!=', null];
        $classCondition = $classId ? ['classId', '=', $classId] : ['classId', '!=', null];
        $regionCondition = $regionId ? ['regionId', '=', $regionId] : ['regionId', '!=', null];
        $districtCondition = $districtId ? ['districtId', '=', $districtId] : ['districtId', '!=', null];
        $wardCondition = $wardId ? ['wardId', '=', $wardId] : ['wardId', '!=', null];
        $startDate = $this->startDate ?: date('Y-m-d', strtotime("2023-01-01"));
        $endDate = $this->endDate ?: date('Y-m-d');

        $subjects = $this->subjects;
        $subjectSelect = [];
        foreach ($subjects as $subject) {
            $subjectSelect[] = "ROUND(AVG($subject), 2) as $subject";
        }
        $subjectSelect = implode(', ', $subjectSelect);

        $markData = Marks::selectRaw("regionId, districtId, wardId, schoolId, $subjectSelect")
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                $classCondition,
                $regionCondition,
                $districtCondition,
                $wardCondition,
                $examCondition
            ])
            ->whereBetween('examDate', [$startDate, $endDate])
            ->groupBy('schoolId', 'regionId', 'districtId', 'wardId')
            ->orderBy('average', 'desc')
            ->get();

        return $markData;
    }


    public function columnWidths(): array
    {
        // Define dynamic column widths based on the subjects
        $columns = range('A', 'Z');
        $columnWidths = [];
        foreach ($columns as $column) {
            $columnWidths[$column] = 20;
        }

        return $columnWidths;
    }

    public function headings(): array
    {
        // Define dynamic headings based on the subjects
        $headings = [
            'Sr.No',
            'Mkoa',
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
        // Define dynamic mapping based on the subjects
        $regionData = \App\Models\Regions::find($markData['regionId']);
        $regionName = $regionData ? $regionData['regionName'] : 'Not Found!';

        $districtData = \App\Models\Districts::find($markData['districtId']);
        $districtName = $districtData ? $districtData['districtName'] : 'Not Found!';

        $wardData = \App\Models\Wards::find($markData['wardId']);
        $wardName = $wardData ? $wardData['wardName'] : 'Not Found!';

        $schoolData = \App\Models\Schools::find($markData['schoolId']);
        $schoolName = $schoolData ? $schoolData['schoolName'] : 'Not Found!';

        $examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];
        $classCondition = ($this->classId == '') ? ['classId', '!=', null] : ['classId', '=', $this->classId];
        $regionCondition = ($this->regionId == '') ? ['regionId', '!=', null] : ['regionId', '=', $this->regionId];
        $districtCondition = ($this->districtId == '') ? ['districtId', '!=', null] : ['districtId', '=', $this->districtId];
        $wardCondition = ($this->wardId == '') ? ['wardId', '!=', null] : ['wardId', '=', $this->wardId];


        $marks = Marks::select($this->subjects)
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['schoolId', '=', $markData['schoolId']],
                $classCondition,
                $examCondition,
                $regionCondition,
                $districtCondition,
                $wardCondition,
            ])
            ->whereBetween('examDate', [$this->startDate, $this->endDate])
            ->get();

        $malePassed = Marks::where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0'],
            ['gender', '=', 'M'],
            $classCondition,
            $examCondition,
            $regionCondition,
            $districtCondition,
            $wardCondition,
            ['schoolId', '=', $markData['schoolId']],
        ])
            ->whereRaw('ROUND(((' . implode('+', $this->subjects) . ') / ' . count($this->subjects) . '), 2) != ?', [0])
            ->whereBetween('examDate', [$this->startDate, $this->endDate])
            ->count();

        $femalePassed = Marks::where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0'],
            ['gender', '=', 'F'],
            $classCondition,
            $examCondition,
            $regionCondition,
            $districtCondition,
            $wardCondition,
            ['schoolId', '=', $markData['schoolId']],
        ])
            ->whereRaw('ROUND(((' . implode('+', $this->subjects) . ') / ' . count($this->subjects) . '), 2) != ?', [0])
            ->whereBetween('examDate', [$this->startDate, $this->endDate])
            ->count();

        $gradeArray = [];
        foreach ($marks as $aMark) {
            $totalMarks = array_sum($aMark->toArray());
            if ($totalMarks != 0) {
                foreach ($this->subjects as $subject) {
                    $grade = $this->assignGrade($aMark[$subject]);
                    // dump($subject . $grade);
                    $gradeArray[] = $subject . $grade;
                }
                // die;
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
            // Calculate the total number of students who have a grade for each subject
            $subjectTotal = 0;
            foreach (['A', 'B', 'C', 'D', 'E'] as $grade) {
                $subjectTotal += $groupArray[$subject . $grade] ?? 0;
            }
            $mappedData[] = $subjectTotal != 0 ? $subjectTotal : "0";
        }

        // dd($mappedData);
        // dd($gradeArray,$marks,$femalePassed,$malePassed);
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
