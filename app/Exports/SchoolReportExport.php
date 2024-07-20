<?php

namespace App\Exports;

use App\Models\Marks;
use App\Models\Schools;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Wards;
use App\Models\Ranks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class SchoolReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
    protected $examId;
    protected $classId;
    protected $regionId;
    protected $districtId;
    protected $wardId;
    protected $startDate;
    protected $endDate;
    protected $subjects;
    protected $rank;

    public function __construct($examId, $classId, $regionId, $districtId, $wardId, $startDate, $endDate)
    {
        $this->examId = $examId;
        $this->classId = $classId;
        $this->regionId = $regionId;
        $this->districtId = $districtId;
        $this->wardId = $wardId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->rank = Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0']
        ])->orderBy('rankName', 'asc')->get();

        $this->subjects = config('subjects.' . $classId) ?: config('subjects.class_default');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];
        $regionCondition = ($this->regionId == '') ? ['regionId', '!=', null] : ['regionId', '=', $this->regionId];
        $districtCondition = ($this->districtId == '') ? ['districtId', '!=', null] : ['districtId', '=', $this->districtId];
        $wardCondition = ($this->wardId == '') ? ['wardId', '!=', null] : ['wardId', '=', $this->wardId];

        $subjectsSelect = implode(', ', array_map(function ($subject) {
            return "ROUND(SUM($subject), 2) as $subject";
        }, $this->subjects));

        $marks = Marks::selectRaw("schoolId, regionId, districtId, wardId, $subjectsSelect, ROUND((ROUND(SUM(hisabati), 2) + ROUND(SUM(kiswahili), 2) + ROUND(SUM(sayansi), 2) + ROUND(SUM(english), 2) + ROUND(SUM(jamii), 2) + ROUND(SUM(maadili), 2)), 2) as averageMarks")
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['classId', '=', $this->classId],
                $examCondition,
                $regionCondition,
                $districtCondition,
                $wardCondition
            ])
            ->whereBetween('examDate', [$this->startDate, $this->endDate])
            ->groupBy('schoolId', 'regionId', 'districtId', 'wardId')
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
            // Adjust the widths for dynamic subjects
            // Assuming each subject will take 6 columns
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
        ];
    }

    public function headings(): array
    {
        $headings = [
            'Sr.No',
            'Mkoa',
            'Wilaya',
            'Kata',
            'Shule'
        ];

        foreach ($this->subjects as $subject) {
            $headings = array_merge($headings, [
                "$subject Grade A",
                "$subject Grade B",
                "$subject Grade C",
                "$subject Grade D",
                "$subject Grade E",
                "$subject Total"
            ]);
        }

        $headings = array_merge($headings, [
            'Wastani ya ufaulu',
            'Daraja'
        ]);

        return $headings;
    }


    public function map($marks): array
    {
        $schoolData = Schools::find($marks['schoolId']);
        $schoolName = ($schoolData) ? $schoolData['schoolName'] : "Not Found";

        $regionData = Regions::find($marks['regionId']);
        $regionName = ($regionData) ? $regionData['regionName'] : 'Not Found';

        $districtData = Districts::find($marks['districtId']);
        $districtName = ($districtData) ? $districtData['districtName'] : 'Not Found';

        $wardData = Wards::find($marks['wardId']);
        $wardName = ($wardData) ? $wardData['wardName'] : 'Not Found';

        $examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];
        $regionCondition = ($this->regionId == '') ? ['regionId', '!=', null] : ['regionId', '=', $this->regionId];
        $districtCondition = ($this->districtId == '') ? ['districtId', '!=', null] : ['districtId', '=', $this->districtId];
        $wardCondition = ($this->wardId == '') ? ['wardId', '!=', null] : ['wardId', '=', $this->wardId];

        $avgMarks = Marks::selectRaw('gender, ' . implode(', ', array_map(function ($subject) {
            return "ROUND($subject, 2) as $subject";
        }, $this->subjects)))
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['classId', '=', $this->classId],
                ['schoolId', '=', $marks['schoolId']],
                $examCondition,
                $regionCondition,
                $districtCondition,
                $wardCondition
            ])->whereBetween('examDate', [$this->startDate, $this->endDate])->get();

        $totalMale = 0;
        $totalFemale = 0;
        $gradeCounts = [];
        foreach ($this->subjects as $subject) {
            $gradeCounts[$subject] = [
                'A' => 0,
                'B' => 0,
                'C' => 0,
                'D' => 0,
                'E' => 0
            ];
        }

        foreach ($avgMarks as $avg) {
            ($avg['gender'] == 'M') ? $totalMale++ : $totalFemale++;
            foreach ($this->subjects as $subject) {
                $grade = $this->assignGrade($avg[$subject]);
                if (isset($gradeCounts[$subject][$grade])) {
                    $gradeCounts[$subject][$grade]++;
                }
            }
        }

        static $serialNumber = 0;
        $serialNumber++;

        $row = [
            $serialNumber,
            $regionName,
            $districtName,
            $wardName,
            $schoolName
        ];

        foreach ($this->subjects as $subject) {
            $row = array_merge($row, [
                $gradeCounts[$subject]['A'],
                $gradeCounts[$subject]['B'],
                $gradeCounts[$subject]['C'],
                $gradeCounts[$subject]['D'],
                $gradeCounts[$subject]['E'],
                array_sum($gradeCounts[$subject])
            ]);
        }

        $averageMarks = $marks['averageMarks'] == 0 ? 0 : number_format(($marks['averageMarks'] / count($avgMarks)), 5);
        $row[] = $averageMarks;
        $row[] = $this->assignGrade($averageMarks / 6);

        return $row;
    }





    function assignGrade($marks)
    {
        foreach ($this->rank as $r) {
            if ($r['rankRangeMin'] < $marks && $r['rankRangeMax'] >= $marks) {
                return $r['rankName'];
            }
        }
        return "F"; // Returning "F" as a default grade if no valid grade is found
    }


}
