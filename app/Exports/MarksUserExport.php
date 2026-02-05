<?php
namespace App\Exports;


use App\Models\Marks;
use App\Models\Ranks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Session;


class MarksUserExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
protected $examId;
protected $classId;
protected $startDate;
protected $endDate;
protected $rank;
protected $subjects;


private $previousAvg = null;
private $previousRank = 0;
private $serial = 0;
protected $subjectPositionsCache = null;


public function __construct($examId, $classId, $startDate, $endDate)
{
$this->examId = $examId;
$this->classId = $classId;
$this->startDate = $startDate;
$this->endDate = $endDate;


$this->rank = Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')
->where([
['isActive', '=', '1'],
['isDeleted', '=', '0']
])->orderBy('rankName', 'asc')->get();


$this->subjects = config('subjects.' . $classId, config('subjects.class_default'));
}


public function collection()
{
$classCondition = ($this->classId == '') ? ['classId', '!=', null] : ['classId', '=', $this->classId];
$examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];


$selectColumns = array_merge(['markId', 'studentName', 'gender'], $this->subjects);


$marks = Marks::select($selectColumns)
->where([
['isActive', '=', '1'],
['isDeleted', '=', '0'],
$classCondition,
$examCondition,
['userId', '=', Session::get('userId')]
])
->whereBetween('examDate', [$this->startDate, $this->endDate])
->get();


// Sort by computed average
$marks = $marks->sortByDesc(function ($m) {
$valid = 0;
$total = 0;
foreach ($this->subjects as $sub) {
if ($m->$sub > 0) {
$total += $m->$sub;
$valid++;
}
}
return $valid ? round($total / $valid, 2) : 0;
})->values();


return $marks;
}


public function columnWidths(): array
{
$widths = [];
foreach (range('A', 'Z') as $col) {
$widths[$col] = 20;
}
return $widths;
}


public function headings(): array
{
    $headings = ['Sr.No', 'Jina la Mwanafunzi'];

    foreach ($this->subjects as $subject) {
        $headings[] = ucfirst($subject);
        $headings[] = 'Grade';
        $headings[] = 'Nafasi';
    }

    $headings = array_merge($headings, ['Jumla', 'Wastani', 'Daraja', 'Nafasi', 'Ufaulu']);

    return $headings;
}

private function assignGrade($marks)
{
    if ($marks === null || $marks === '' || $marks < 0) {
        return '-';
    }

    if ($marks >= 41) return 'A';
    if ($marks >= 31) return 'B';
    if ($marks >= 21) return 'C';
    if ($marks >= 11) return 'D';
    
    return 'E';
}

private function finalStatus($average)
{
    if ($average === null || $average === '' || $average < 0) {
        return '-';
    }

    // Pass mark inategemea classId
    $passMark = ($this->classId > 4) ? 21 : 11;

    // Round average before checking
    $roundedAverage = round($average);

    return ($roundedAverage >= $passMark) ? 'FAULU' : 'FELI';
}

public function map($marks): array
{
    if ($this->subjectPositionsCache === null) {
        $this->subjectPositionsCache = [];
        foreach ($this->subjects as $subject) {
            $this->subjectPositionsCache[$subject] = $this->calculatePositions($subject);
        }
    }

    $gradesFlattened = [];
    foreach ($this->subjects as $subject) {
    $value = $marks->$subject;

    // Ikiwa value = 0, weka blank
    $displayValue = ($value > 0) ? $value : '';

    $grade = ($value > 0) ? $this->assignGrade($value) : '';
    $position = ($value > 0) ? ($this->subjectPositionsCache[$subject][$marks->markId] ?? '-') : '';

    $gradesFlattened[] = $displayValue;
    $gradesFlattened[] = $grade;
    $gradesFlattened[] = $position;
}


    // Calculate Average
    $valid = 0;
    $total = 0;
    foreach ($this->subjects as $subject) {
        if ($marks->$subject > 0) {
            $total += $marks->$subject;
            $valid++;
        }
    }
    $average = $valid ? round($total / $valid, 2) : 0;

    // Ranking
    $this->serial++;
    if ($this->previousAvg === $average) {
        $rank = $this->previousRank;
    } else {
        $rank = $this->serial;
    }

    $this->previousAvg = $average;
    $this->previousRank = $rank;

    $gradeStatus = $this->assignGrade($average);
    $resultStatus = $this->finalStatus($average);
    $totalMarks = $total;

    return array_merge([
        $this->serial,
        $marks->studentName
    ], $gradesFlattened, [
        $totalMarks,
        $average,
        $gradeStatus,
        $rank,
        $resultStatus
    ]);
}

private function calculatePositions($subject)
{
    $classCondition = ($this->classId == '') ? ['classId', '!=', null] : ['classId', '=', $this->classId];
    $examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];

    // Get student marks for this subject only
    $records = Marks::select('markId', $subject)
        ->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0'],
            $classCondition,
            $examCondition,
            ['userId', '=', Session::get('userId')]
        ])
        ->whereBetween('examDate', [$this->startDate, $this->endDate])
        ->orderBy($subject, 'desc')
        ->get();

    // Assign ranks for this subject
    $positions = [];
    $rank = 0;
    $previousValue = null;
    $counter = 0;

    foreach ($records as $rec) {
        $counter++;

        if ($rec->$subject === $previousValue) {
            // Same marks → same rank
            $positions[$rec->markId] = $rank;
        } else {
            // New marks → new rank
            $rank = $counter;
            $positions[$rec->markId] = $rank;
            $previousValue = $rec->$subject;
        }
    }

    return $positions;
}

}
