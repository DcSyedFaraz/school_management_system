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
    protected $subjects;

    public function __construct($examId, $classId, $startDate, $endDate)
    {
        $this->examId = $examId;
        $this->classId = $classId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->rank = Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0']
        ])->orderBy('rankName', 'asc')->get();

        // Get subjects based on class
        $this->subjects = config('subjects.' . $classId, config('subjects.class_default'));
    }

    public function collection()
    {
        $classCondition = ($this->classId == '') ? ['classId', '!=', null] : ['classId', '=', $this->classId];
        $examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];

        $selectColumns = array_merge(['markId', 'studentName', 'gender', 'total'], $this->subjects, [
            DB::raw('ROUND(((' . implode(' + ', $this->subjects) . ') / ' . count($this->subjects) . '), 2) as averageMarks')
        ]);

        $marks = Marks::select($selectColumns)
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                $classCondition,
                $examCondition,
                ['userId', '=', Session::get('userId')]
            ])->whereBetween('examDate', [$this->startDate, $this->endDate])->orderBy('averageMarks', 'desc')->get();

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
        $headings = ['Sr.No', 'Jinala Mwanafunzi'];
        foreach ($this->subjects as $subject) {
            $headings[] = ucfirst($subject);
            $headings[] = 'Grade';
        }
        $headings = array_merge($headings, ['Jumla', 'Wastani', 'Daraja', 'Nafasi', 'Ufaulu']);
        return $headings;
    }

    public function map($marks): array
    {
        $total = array_reduce($this->subjects, function ($carry, $subject) use ($marks) {
            return $carry + $marks->$subject;
        }, 0);

        $grades = array_map(function ($subject) use ($marks) {
            return [$marks->$subject, $this->assignGrade($marks->$subject)];
        }, $this->subjects);

        $gradesFlattened = [];
        foreach ($grades as $grade) {
            $gradesFlattened[] = $grade[0];
            $gradesFlattened[] = $grade[1];
        }

        $gradeStatus = ($marks->averageMarks > 0) ? $this->assignGrade($marks->averageMarks) : "ABS";
        $resultStatus = ($marks->averageMarks > 0) ? $this->finalStatus($marks->averageMarks) : "";

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

        return array_merge([
            $serialNumber,
            $marks->studentName
        ], $gradesFlattened, [
            $total,
            $marks->averageMarks,
            $gradeStatus,
            $rank,
            $resultStatus
        ]);
    }

    function assignGrade($marks)
    {
        if ($this->rank) {
            foreach ($this->rank as $r) {
                if ($marks >= $r['rankRangeMin'] && $marks <= $r['rankRangeMax']) {
                    return $r['rankName'];
                }
            }
        }
        return "Null";
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
