<?php
namespace App\Exports;


use App\Facades\Grading;
use App\Models\Marks;
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
protected $subjects;


private $previousTotal = null;
private $previousRank = 0;
private $serial = 0;
protected $subjectPositionsCache = null;


public function __construct($examId, $classId, $startDate, $endDate)
{
$this->examId = $examId;
$this->classId = $classId;
$this->startDate = $startDate;
$this->endDate = $endDate;


$this->subjects = config('subjects.' . $classId, config('subjects.class_default'));
}


public function collection()
{
$classCondition = ($this->classId == '') ? ['classId', '!=', null] : ['classId', '=', $this->classId];
$examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];


$selectColumns = array_merge(['markId', 'studentName', 'gender', 'total', 'average'], $this->subjects);


$marks = Marks::select($selectColumns)
->where([
['isActive', '=', '1'],
['isDeleted', '=', '0'],
$classCondition,
$examCondition,
['userId', '=', Session::get('userId')]
])
->whereBetween('examDate', [$this->startDate, $this->endDate])
->orderByRaw('(average IS NULL), total DESC')
->get();


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

    // Ikiwa value ni null, weka blank (absent); 0 ni alama halisi
    $displayValue = ($value !== null) ? $value : '';

    $grade = ($value !== null) ? Grading::gradeSubject($value) : '';
    $position = ($value !== null) ? ($this->subjectPositionsCache[$subject][$marks->markId] ?? '-') : '';

    $gradesFlattened[] = $displayValue;
    $gradesFlattened[] = $grade;
    $gradesFlattened[] = $position;
}


    // Ranking — based on TOTAL, not average. average IS NULL is the sole
    // absence flag (a student who sat fewer subjects is not absent).
    $this->serial++;
    if ($marks->average !== null && $this->previousTotal === $marks->total) {
        $rank = $this->previousRank;
    } else {
        $rank = $this->serial;
    }

    $this->previousTotal = $marks->total;
    $this->previousRank = $rank;

    $gradeStatus = ($marks->average !== null) ? Grading::gradeTotal($marks->total) : Grading::absentGrade();
    $resultStatus = Grading::statusForTotal($marks->average !== null ? $marks->total : null, $this->classId);

    return array_merge([
        $this->serial,
        $marks->studentName
    ], $gradesFlattened, [
        $marks->total,
        $marks->average,
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
