<?php

namespace App\Exports;

use App\Facades\Grading;
use App\Models\Marks;
use App\Models\Schools;
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
    protected $subjects;

    /** Tie-state for map(); instance property so it doesn't leak across exports sharing a process. */
    private $storedAvg = '';
    private $serialNumber = 0;
    private $tieOffset = 0;

    public function __construct($examId, $classId, $regionId, $districtId, $startDate, $endDate)
    {
        $this->examId = $examId;
        $this->classId = $classId;
        $this->regionId = $regionId;
        $this->districtId = $districtId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
            return "ROUND(AVG(CASE WHEN $subject IS NOT NULL THEN $subject END), 2) as $subject";
        }, $this->subjects)) . ', ROUND(AVG(CASE WHEN average IS NOT NULL THEN total END), 2) as avgTotal')
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
            ->orderBy('avgTotal', 'desc')
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

        $this->serialNumber++;

        if ($this->storedAvg == $marks->avgTotal) {
            $this->tieOffset++;
            $rank = $this->serialNumber - $this->tieOffset;
        } else {
            $this->tieOffset = 0;
            $rank = $this->serialNumber;
        }
        $this->storedAvg = $marks->avgTotal;

        $data = [
            $this->serialNumber,
            $schoolName,
        ];

        foreach ($this->subjects as $subject) {
            $data[] = $marks->$subject;
            $data[] = Grading::gradeSubject($marks->$subject);
        }

        $data[] = $total;
        $data[] = $marks->avgTotal;
        $data[] = Grading::gradeTotal($marks->avgTotal);
        $data[] = $rank;
        $data[] = Grading::statusForTotal($marks->avgTotal, $this->classId);

        return $data;
    }

    function getSubjectsByClassId($classId)
    {
        return config('subjects.' . $classId, config('subjects.class_default'));
    }
}
