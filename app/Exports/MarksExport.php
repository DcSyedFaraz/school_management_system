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
    protected $subjects;

    public function __construct($examId, $classId, $regionId, $districtId, $startDate, $endDate)
    {
        $this->examId = $examId;
        $this->classId = $classId;
        $this->regionId = $regionId;
        $this->districtId = $districtId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->rank = Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0']
        ])->orderBy('rankName', 'asc')->get();
        $this->subjects = $this->getSubjectsByClassId($classId);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $classCondition = ($this->classId == '') ? ['classId', '!=', null] : ['classId', '=', $this->classId];
        $examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];
        $regionCondition = ($this->regionId == '') ? ['regionId', '!=', null] : ['regionId', '=', $this->regionId];
        $districtCondition = ($this->districtId == '') ? ['districtId', '!=', null] : ['districtId', '=', $this->districtId];

        $marks = Marks::selectRaw('schoolId, ' . implode(', ', array_map(function ($subject) {
            return "ROUND(AVG(CASE WHEN $subject > 0 THEN $subject END), 2) as $subject";
        }, $this->subjects)) . ', ROUND(AVG(CASE WHEN average > 0 THEN average END), 2) as averageMarks')
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
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
        $headings = [
            'Sr.No',
            'Jina La Shule',
        ];

        foreach ($this->subjects as $subject) {
            $headings[] = $subject;
            $headings[] = "$subject Grade";
        }

        $headings[] = 'Jumla';
        $headings[] = 'Wastani';
        $headings[] = 'Daraja';
        $headings[] = 'Nafasi';
        $headings[] = 'Ufaulu';

        return $headings;
    }

    public function map($marks): array
    {
        $schoolData = Schools::find($marks['schoolId']);
        $schoolName = $schoolData ? $schoolData['schoolName'] : "Not Found";

        $total = array_sum(array_intersect_key($marks->toArray(), array_flip($this->subjects)));

        static $storedAvg = '';
        static $serialNumber = 0;
        static $j = 0;

        $serialNumber++;

        if ($storedAvg == $marks->averageMarks) {
            $j++;
            $rank = $serialNumber - $j;
            $storedAvg = $marks->averageMarks;
        } else {
            $j = 0;
            $rank = $serialNumber;
            $storedAvg = $marks->averageMarks;
        }

        $data = [
            $serialNumber,
            $schoolName,
        ];

        foreach ($this->subjects as $subject) {
            $data[] = $marks->$subject;
            $data[] = $this->assignGrade($marks->$subject);
        }

        $data[] = $total;
        $data[] = $marks->averageMarks;
        $data[] = $this->assignGrade($marks->averageMarks);
        $data[] = $rank;
        $data[] = $this->finalStatus($marks->averageMarks);

        return $data;
    }

    function getSubjectsByClassId($classId)
    {
        return config('subjects.' . $classId, config('subjects.class_default'));
    }

    function assignGrade($marks)
    {
        if ($this->rank) {
            if ($this->rank[0]['rankRangeMin'] <= $marks && $this->rank[0]['rankRangeMax'] >= $marks) {
                return $this->rank[0]['rankName'];
            } else if ($this->rank[1]['rankRangeMin'] <= $marks && $this->rank[1]['rankRangeMax'] >= $marks) {
                return $this->rank[1]['rankName'];
            } else if ($this->rank[2]['rankRangeMin'] <= $marks && $this->rank[2]['rankRangeMax'] >= $marks) {
                return $this->rank[2]['rankName'];
            } else if ($this->rank[3]['rankRangeMin'] <= $marks && $this->rank[3]['rankRangeMax'] >= $marks) {
                return $this->rank[3]['rankName'];
            } else {
                return $this->rank[4]['rankName'];
            }
        } else {
            return "Null";
        }
    }

    function finalStatus($average)
    {
        if ($this->classId > 4) {
            if ($average < $this->rank[3]['rankRangeMax']) {
                return "FAIL";
            } else {
                return "PASS";
            }
        } else {
            if ($average < $this->rank[4]['rankRangeMax']) {
                return "FAIL";
            } else {
                return "PASS";
            }
        }
    }
}
