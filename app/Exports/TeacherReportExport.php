<?php

namespace App\Exports;

use App\Models\Marks;
use App\Models\Schools;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Wards;
use App\Models\Ranks;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class TeacherReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
    protected $examId;
    protected $classId;
    protected $schoolId;
    protected $startDate;
    protected $endDate;
    protected $subjects;
    protected $rank;

    public function __construct($examId, $classId, $schoolId, $startDate, $endDate)
    {
        $this->examId = $examId;
        $this->classId = $classId;
        $this->schoolId = $schoolId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->rank = Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0']
        ])->orderBy('rankName', 'asc')->get();

        // Dynamically set subjects based on the class
        $this->subjects = $this->getSubjectsByClass($classId);
    }

    protected function getSubjectsByClass($classId)
    {
        $subjects = Config::get('subjects');

        return $subjects[$classId] ?? $subjects['class_default'];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $examCondition = ($this->examId == '') ? ['examId', '!=', null] : ['examId', '=', $this->examId];

        // Creating the raw SQL for subject columns
        $subjectColumns = implode(' + ', array_map(function ($subject) {
            return "AVG($subject)";
        }, $this->subjects));

        $marks = Marks::selectRaw("schoolId, regionId, districtId, wardId,
        ROUND(SUM(total), 2) as averageMarks")
            ->where([
                ['isActive', '=', '1'],
                ['isDeleted', '=', '0'],
                ['classId', '=', $this->classId],
                ['schoolId', '=', $this->schoolId],
                $examCondition
            ])
            ->whereBetween('examDate', [$this->startDate, $this->endDate])
            ->groupBy('schoolId', 'regionId', 'districtId', 'wardId')
            ->orderBy('averageMarks', 'desc')
            ->get();
        // $marks = Marks::selectRaw("schoolId, regionId, districtId, wardId,
        //     ROUND(($subjectColumns) / " . count($this->subjects) . ", 2) as averageMarks")
        //     ->where([
        //         ['isActive', '=', '1'],
        //         ['isDeleted', '=', '0'],
        //         ['classId', '=', $this->classId],
        //         ['schoolId', '=', $this->schoolId],
        //         $examCondition
        //     ])
        //     ->whereBetween('examDate', [$this->startDate, $this->endDate])
        //     ->groupBy('schoolId', 'regionId', 'districtId', 'wardId')
        //     ->orderBy('averageMarks', 'desc')
        //     ->get();
// dd($marks);
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
            'Shule',
            'Wav(' . (date('Y') - $this->classId) . ')',
            'Was(' . (date('Y') - $this->classId) . ')',
            'Jml',
            'Wav(Waliosaijliwa)',
            'Was(Waliosaijliwa)',
            'Jml',
            'Wav(WaliofanyaMtihani)',
            'Was(WaliofanyaMtihani)',
            'Jml',
            'Percent',
            'Wav(Waliofanya)',
            'Was(Waliofanya)',
            'Jml',
            'Percent',
            'Wav(Grade A)',
            'Was(Grade A)',
            'Jml',
            'Wav(Grade B)',
            'Was(Grade B)',
            'Jml',
            'Wav(Grade C)',
            'Was(Grade C)',
            'Jml'
        ];

        if ($this->classId > 4) {
            $headings = array_merge($headings, [
                'Wav(Grade A-C)',
                'Was(Grade A-C)',
                'Jml',
                'Percent',
                'Wav(Grade D)',
                'Was(Grade D)',
                'Jml',
                'Wav(Grade E)',
                'Was(Grade E)',
                'Jml',
                'Wav(Grade D-E)',
                'Was(Grade D-E)',
                'Jml',
                'Percent'
            ]);
        } else {
            $headings = array_merge($headings, [
                'Wav(Grade D)',
                'Was(Grade D)',
                'Jml',
                'Wav(Grade A-D)',
                'Was(Grade A-D)',
                'Jml',
                'Percent',
                'Wav(Grade E)',
                'Was(Grade E)',
                'Jml',
                'Wav(Grade E)',
                'Was(Grade E)',
                'Jml',
                'Percent'
            ]);
        }

        $headings = array_merge($headings, ['Wastani ya ufaulu', 'Daraja']);

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

        $fgMale = Marks::where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0'],
            ['classId', '=', $this->classId],
            ['gender', '=', 'M'],
            ['schoolId', '=', $marks['schoolId']],
            $examCondition
        ])->whereBetween('examDate', [$this->startDate, $this->endDate])->count();

        $fgFemale = Marks::where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0'],
            ['classId', '=', $this->classId],
            ['gender', '=', 'F'],
            ['schoolId', '=', $marks['schoolId']],
            $examCondition
        ])->whereBetween('examDate', [$this->startDate, $this->endDate])->count();

        $avgMarks = Marks::selectRaw('gender, ROUND((' . implode(' + ', $this->subjects) . ') / ' . count($this->subjects) . ', 2) as averageMarks')->where([
            ['isActive', '=', '1'],
            ['isDeleted', '=', '0'],
            ['classId', '=', $this->classId],
            ['schoolId', '=', $marks['schoolId']],
            $examCondition
        ])->whereBetween('examDate', [$this->startDate, $this->endDate])->get();

        $totalMale = 0;
        $totalFemale = 0;
        $aGradeMale = 0;
        $aGradeFemale = 0;
        $bGradeMale = 0;
        $bGradeFemale = 0;
        $cGradeMale = 0;
        $cGradeFemale = 0;
        $dGradeMale = 0;
        $dGradeFemale = 0;
        $eGradeMale = 0;
        $eGradeFemale = 0;
        $maleAbsent = 0;
        $femaleAbsent = 0;

        foreach ($avgMarks as $avg) {
            ($avg['gender'] == 'M') ? $totalMale++ : $totalFemale++;

            if ($avg['averageMarks'] == 0) {
                if ($avg['gender'] == 'M') {
                    $maleAbsent++;
                } else {
                    $femaleAbsent++;
                }
            } else {
                $grade = $this->assignGrade($avg['averageMarks']);
                switch ($grade) {
                    case 'A':
                        ($avg['gender'] == 'M') ? $aGradeMale++ : $aGradeFemale++;
                        break;
                    case 'B':
                        ($avg['gender'] == 'M') ? $bGradeMale++ : $bGradeFemale++;
                        break;
                    case 'C':
                        ($avg['gender'] == 'M') ? $cGradeMale++ : $cGradeFemale++;
                        break;
                    case 'D':
                        ($avg['gender'] == 'M') ? $dGradeMale++ : $dGradeFemale++;
                        break;
                    default:
                        ($avg['gender'] == 'M') ? $eGradeMale++ : $eGradeFemale++;
                        break;
                }
            }
        }

        if ($this->classId > 4) {
            $totalPassMale = $aGradeMale + $bGradeMale + $cGradeMale;
            $totalPassFemale = $aGradeFemale + $bGradeFemale + $cGradeFemale;
            $totalFailMale = $dGradeMale + $eGradeMale;
            $totalFailFemale = $dGradeFemale + $eGradeFemale;
            $totalPass = $totalPassMale + $totalPassFemale;
            $totalFail = $totalFailMale + $totalFailFemale;
        } else {
            $totalPassMale = $aGradeMale + $bGradeMale + $cGradeMale + $dGradeMale;
            $totalPassFemale = $aGradeFemale + $bGradeFemale + $cGradeFemale + $dGradeFemale;
            $totalFailMale = $eGradeMale;
            $totalFailFemale = $eGradeFemale;
            $totalPass = $totalPassMale + $totalPassFemale;
            $totalFail = $totalFailMale + $totalFailFemale;
        }

        static $serialNumber = 0;
        $serialNumber++;
        // dd(count($this->subjects));
        // $averageMarks = count($avgMarks) - $maleAbsent - $femaleAbsent > 0 ? $marks['averageMarks'] / (count($avgMarks) - $maleAbsent - $femaleAbsent) : 0;
        $averageMarks = number_format($marks['averageMarks'] / (count($avgMarks) - $maleAbsent - $femaleAbsent), 2);
        $grade = $this->assignGrade($averageMarks / count($this->subjects));

        if ($this->classId > 4) {
            return [
                $serialNumber,
                $regionName,
                $districtName,
                $wardName,
                $schoolName,
                $fgMale ?: "0",
                $fgFemale ?: "0",
                $fgMale + $fgFemale ?: "0",
                $totalMale ?: "0",
                $totalFemale ?: "0",
                $totalMale + $totalFemale ?: "0",
                $totalPassMale ?: "0",
                $totalPassFemale ?: "0",
                $totalPassMale + $totalPassFemale ?: "0",
                number_format(($totalPassMale + $totalPassFemale) / ($totalMale + $totalFemale) * 100, 2),
                $maleAbsent ?: "0",
                $femaleAbsent ?: "0",
                $maleAbsent + $femaleAbsent ?: "0",
                number_format(($maleAbsent + $femaleAbsent) / ($totalMale + $totalFemale) * 100, 2),
                $aGradeMale ?: "0",
                $aGradeFemale ?: "0",
                $aGradeMale + $aGradeFemale ?: "0",
                $bGradeMale ?: "0",
                $bGradeFemale ?: "0",
                $bGradeMale + $bGradeFemale ?: "0",
                $cGradeMale ?: "0",
                $cGradeFemale ?: "0",
                $cGradeMale + $cGradeFemale ?: "0",
                $aGradeMale + $bGradeMale + $cGradeMale ?: "0",
                $aGradeFemale + $bGradeFemale + $cGradeFemale ?: "0",
                $aGradeMale + $bGradeMale + $cGradeMale + $aGradeFemale + $bGradeFemale + $cGradeFemale ?: "0",
                number_format(($aGradeMale + $bGradeMale + $cGradeMale + $aGradeFemale + $bGradeFemale + $cGradeFemale) / ($totalMale + $totalFemale) * 100, 2),
                $dGradeMale ?: "0",
                $dGradeFemale ?: "0",
                $dGradeMale + $dGradeFemale ?: "0",
                $eGradeMale ?: "0",
                $eGradeFemale ?: "0",
                $eGradeMale + $eGradeFemale ?: "0",
                $eGradeMale + $dGradeMale ?: "0",
                $eGradeFemale + $dGradeFemale ?: "0",
                $eGradeMale + $dGradeMale + $eGradeFemale + $dGradeFemale ?: "0",
                number_format(($eGradeMale + $dGradeMale + $eGradeFemale + $dGradeFemale) / ($totalMale + $totalFemale) * 100, 2),
                number_format($averageMarks, 2) ?: "0",
                $grade
            ];
        } else {
            return [
                $serialNumber,
                $regionName,
                $districtName,
                $wardName,
                $schoolName,
                $fgMale ?: "0",
                $fgFemale ?: "0",
                $fgMale + $fgFemale ?: "0",
                $totalMale ?: "0",
                $totalFemale ?: "0",
                $totalMale + $totalFemale ?: "0",
                $totalPassMale ?: "0",
                $totalPassFemale ?: "0",
                $totalPassMale + $totalPassFemale ?: "0",
                number_format(($totalPassMale + $totalPassFemale) / ($totalMale + $totalFemale) * 100, 2),
                $maleAbsent ?: "0",
                $femaleAbsent ?: "0",
                $maleAbsent + $femaleAbsent ?: "0",
                number_format(($maleAbsent + $femaleAbsent) / ($totalMale + $totalFemale) * 100, 2),
                $aGradeMale ?: "0",
                $aGradeFemale ?: "0",
                $aGradeMale + $aGradeFemale ?: "0",
                $bGradeMale ?: "0",
                $bGradeFemale ?: "0",
                $bGradeMale + $bGradeFemale ?: "0",
                $cGradeMale ?: "0",
                $cGradeFemale ?: "0",
                $cGradeMale + $cGradeFemale ?: "0",
                $dGradeMale ?: "0",
                $dGradeFemale ?: "0",
                $dGradeMale + $dGradeFemale ?: "0",
                $aGradeMale + $bGradeMale + $cGradeMale + $dGradeMale ?: "0",
                $aGradeFemale + $bGradeFemale + $cGradeFemale + $dGradeFemale ?: "0",
                $aGradeMale + $bGradeMale + $cGradeMale + $dGradeMale + $aGradeFemale + $bGradeFemale + $cGradeFemale + $dGradeFemale ?: "0",
                number_format(($aGradeMale + $bGradeMale + $cGradeMale + $dGradeMale + $aGradeFemale + $bGradeFemale + $cGradeFemale + $dGradeFemale) / ($totalMale + $totalFemale) * 100, 2),
                $eGradeMale ?: "0",
                $eGradeFemale ?: "0",
                $eGradeMale + $eGradeFemale ?: "0",
                $eGradeMale ?: "0",
                $eGradeFemale ?: "0",
                $eGradeMale + $eGradeFemale ?: "0",
                number_format(($eGradeMale + $eGradeFemale) / ($totalMale + $totalFemale) * 100, 2),
                number_format($averageMarks, 2) ?: "0",
                $grade
            ];
        }
    }

    function assignGrade($marks)
    {
        foreach ($this->rank as $rank) {
            if ($marks >= $rank['rankRangeMin'] && $marks <= $rank['rankRangeMax']) {
                return $rank['rankName'];
            }
        }

        return "Null";
    }
}
