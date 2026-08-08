<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report PDF</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 8mm 8mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mt-5 {
            margin-top: 1.25rem;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 9pt;
            margin-left: auto;
            margin-right: auto;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid black;
            padding: 3px 5px;
            text-align: center;
            vertical-align: middle;
            word-break: break-word;
        }

        table.small td {
            padding: 1px 2px;
            font-size: 7.5pt;
        }

        table.small thead th {
            padding: 1px 2px;
            font-size: 6pt;
            background-color: #d9d9d9;
        }

        .student-name {
            width: 15%;
            white-space: normal;
            text-align: left;
        }

        table.small td.student-name {
            font-size: 7.5pt;
        }

        .tiny-col {
            width: 2.8%;
        }

        .small-col {
            width: 3.5%;
        }

        .subject-col {
            width: 7%;
            text-align: left;
        }

        table.small th.subject-col,
        table.small td.subject-col {
            font-size: 6pt;
        }

        table.small tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        table.small tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    @php
        $classes = $reportData['classes'];
        $exams = $reportData['exams'];
        $classId = $reportData['classId'];
        $startDate = $reportData['startDate'];
        $endDate = $reportData['endDate'];
        $subjects = $reportData['subjects'];
        $marks = $reportData['marks'];
        $gAverage = $reportData['gAverage'];
        $maleAbsent = $reportData['maleAbsent'];
        $femaleAbsent = $reportData['femaleAbsent'];
        $schoolAverage = $reportData['schoolAverage'];
        $achievementAverage = $reportData['achievementAverage'];
        $schoolGrade = $reportData['schoolGrade'];
        $amCount = $reportData['amCount'];
        $bmCount = $reportData['bmCount'];
        $cmCount = $reportData['cmCount'];
        $dmCount = $reportData['dmCount'];
        $emCount = $reportData['emCount'];
        $afCount = $reportData['afCount'];
        $bfCount = $reportData['bfCount'];
        $cfCount = $reportData['cfCount'];
        $dfCount = $reportData['dfCount'];
        $efCount = $reportData['efCount'];
        $gradeMaleCount = $reportData['gradeMaleCount'];
        $gradeFemaleCount = $reportData['gradeFemaleCount'];
        $gradeCount = $reportData['gradeCount'];
        $failCount = $reportData['failCount'];
        $failMaleCount = $reportData['failMaleCount'];
        $failFemaleCount = $reportData['failFemaleCount'];
        $gradeArray = $reportData['gradeArray'];
        $gradeMaleArray = $reportData['gradeMaleArray'];
        $gradeFemaleArray = $reportData['gradeFemaleArray'];

        use Carbon\Carbon;
        // Get exam date from the first mark record
        $examDate = null;
        if (count($marks) > 0) {
            $firstMarkId = $marks[0]['markId'] ?? null;
            if ($firstMarkId) {
                $markRecord = \App\Models\Marks::where('markId', $firstMarkId)->first();
                $examDate = $markRecord->examDate ?? null;
            }
        }
        // Fallback to endDate if examDate not found
        $dateToUse = $examDate ?? $endDate;
        $dateCarbon = Carbon::parse($dateToUse);

        $monthMap = [
            'January' => 'JANUARI',
            'February' => 'FEBRUARI',
            'March' => 'MACHI',
            'April' => 'APRILI',
            'May' => 'MEI',
            'June' => 'JUNI',
            'July' => 'JULAI',
            'August' => 'AGOSTI',
            'September' => 'SEPTEMBER',
            'October' => 'OKTOBA',
            'November' => 'NOVEMBER',
            'December' => 'DECEMBER',
        ];
        $formattedMonth = $monthMap[$dateCarbon->format('F')];
        $formattedDates = $dateCarbon->format('d') . ' ' . $formattedMonth . ', ' . $dateCarbon->format('Y');
    @endphp

    <div class="text-center font-bold mb-2 uppercase">
        <div>OFISI YA WAZIRI MKUU - TAMISEMI</div>
        <div>HALMASHAURI YA {{ $reportData['districtName'] ?? '_____________________' }}</div>
        <div>SHULE YA MSINGI {{ $reportData['schoolName'] ?? '_____________________' }}</div>
        <div style="text-transform: uppercase;">TATHIMINI YA MTIHANI WA
            {{ $reportData['examName'] ?? '_____________________' }} DARASA LA
            {{ $reportData['className'] ?? '_________' }} ULIOFANYIKA {{ $formattedDates ?? '_________________' }}</div>
    </div>

    <!-- Wastani wa shule na ufaulu -->
    <div class="mb-2">
        <table>
            <thead>
                <tr>
                    <th>WASTANI WA SHULE</th>
                    <th>DARAJA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($schoolAverage, 2) }}</td>
                    <td>{{ $schoolGrade }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mb-3">
        <table>
            <thead>
                <tr>
                    <th>WASTANI WA UFAULU</th>
                    <th>DARAJA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($achievementAverage, 2) }}</td>
                    <td>{{ $schoolGrade }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tathmini ya ufaulu -->
    <div class="mt-3">
        <table>
            <tr>
                <th rowspan="2">TATHIMINI YA UFAULU</th>
                <th colspan="7">DARAJA</th>
            </tr>
            <tr>
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>D</th>
                <th>E</th>
                <th>ABS</th>
                <th>JUMLA</th>
            </tr>
            <tr class="bg-white">
                <td>WAV</td>
                <td>{{ $amCount }}</td>
                <td>{{ $bmCount }}</td>
                <td>{{ $cmCount }}</td>
                <td>{{ $dmCount }}</td>
                <td>{{ $emCount }}</td>
                <td>{{ $maleAbsent }}</td>
                <td>{{ $gradeMaleCount + $maleAbsent }}</td>
            </tr>
            <tr class="bg-gray-200">
                <td>WAS</td>
                <td>{{ $afCount }}</td>
                <td>{{ $bfCount }}</td>
                <td>{{ $cfCount }}</td>
                <td>{{ $dfCount }}</td>
                <td>{{ $efCount }}</td>
                <td>{{ $femaleAbsent }}</td>
                <td>{{ $gradeFemaleCount + $femaleAbsent }}</td>
            </tr>
            <tr class="bg-white">
                <td>Jumla</td>
                <td>{{ $amCount + $afCount }}</td>
                <td>{{ $bmCount + $bfCount }}</td>
                <td>{{ $cmCount + $cfCount }}</td>
                <td>{{ $dmCount + $dfCount }}</td>
                <td>{{ $emCount + $efCount }}</td>
                <td>{{ $maleAbsent + $femaleAbsent }}</td>
                <td>{{ $gradeMaleCount + $gradeFemaleCount + $maleAbsent + $femaleAbsent }}</td>
            </tr>
        </table>
    </div>

    <!-- Subject Grade Breakdown -->
    <div class="page-break mt-5">
        <h3 class="font-bold">TATHIMINI YA MADARAJA YA KILA SOMO</h3>
        <table class="small">
            <thead>
                <tr>
                    <th rowspan="2" class="subject-col">SOMO</th>
                    @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                        <th colspan="3">{{ $grade }}</th>
                    @endforeach
                    <th rowspan="2">WASTANI WA SOMO</th>
                    <th rowspan="2">WALIO FAULU</th>
                    <th rowspan="2">%</th>
                    <th rowspan="2">WASIO FAULU</th>
                    <th rowspan="2">%</th>
                </tr>
                <tr>
                    @foreach (range(1, 5) as $dummy)
                        <th>WAV</th>
                        <th>WAS</th>
                        <th>JML</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if (count($subjects) > 0)
                    @php $g=0; @endphp
                    @foreach ($subjects as $name)
                        @php
                            $totalGradeCount = array_sum($gradeArray[$name]);
                            $failedCount =
                                $classId > 4
                                    ? $gradeArray[$name]['D'] + $gradeArray[$name]['E']
                                    : $gradeArray[$name]['E'];
                        @endphp
                        <tr class="{{ $g % 2 == 0 ? 'bg-white' : 'bg-gray-200' }}">
                            <td class="subject-col">{{ strtoupper($name) }}</td>
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                                <td>{{ $gradeMaleArray[$name][$grade] }}</td>
                                <td>{{ $gradeFemaleArray[$name][$grade] }}</td>
                                <td>{{ $gradeArray[$name][$grade] }}</td>
                            @endforeach
                            <td>{{ number_format($gAverage[$g] / (count($marks) - $maleAbsent - $femaleAbsent), 2) }}
                            </td>
                            <td>{{ $totalGradeCount - $failedCount }}</td>
                            <td>{{ $totalGradeCount > 0 ? number_format((($totalGradeCount - $failedCount) * 100) / $totalGradeCount, 2) : 0 }}
                            </td>
                            <td>{{ $failedCount }}</td>
                            <td>{{ $totalGradeCount > 0 ? number_format(($failedCount * 100) / $totalGradeCount, 2) : 0 }}
                            </td>
                        </tr>
                        @php $g++; @endphp
                    @endforeach
                @else
                    <tr>
                        <td colspan="21" class="text-red-500">No Data Found!</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <br>
    <br>
    <!-- Marks Table -->
    <div class="mt-5">
        <h3 class="font-bold">MATOKEO KWA MPANGILIO WA WANAFUNZI WOTE</h3>
        <table class="small">
            <thead>
                <tr>
                    <th rowspan="2" class="tiny-col">S/N</th>
                    <th rowspan="2" class="student-name">JINA LA MWANAFUNZI</th>
                    @foreach ($subjects as $subject)
                        <th colspan="3" class="subject_headings">{{ strtoupper($subject) }}</th>
                    @endforeach
                    <th rowspan="2" class="small-col">Jumla</th>
                    <th rowspan="2" class="small-col">Wastani</th>
                    <th rowspan="2" class="small-col">Daraja</th>
                    <th rowspan="2" class="small-col">Nafasi</th>
                    <th rowspan="2" class="small-col">Ufaulu</th>
                </tr>
                <tr>
                    @foreach ($subjects as $subject)
                        <th class="tiny-col">AL</th>
                        <th class="tiny-col">DRJ</th>
                        <th class="tiny-col">NFS</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 1;
                    $j = 0;
                    $storedAvg = '';
                @endphp
                @foreach ($marks as $mark)
                    @php
                        if ($mark['average'] !== null && $storedAvg === $mark['total']) {
                            $j++;
                            $storedAvg = $mark['total'];
                            $position = $i - $j;
                        } else {
                            $j = 0;
                            $storedAvg = $mark['total'];
                            $position = $i;
                        }
                    @endphp
                    <tr class="{{ $i % 2 == 0 ? 'bg-gray-200' : 'bg-white' }}">
                        <td class="tiny-col">{{ $i }}</td>
                        <td class="student-name">{{ $mark['studentName'] }}</td>
                        @foreach ($subjects as $subject)
                            @php
                                $subjectScores = collect($marks)->pluck($subject)->filter(fn($v) => $v !== null)->sortDesc()->values()->all();
                                $subjectPosition = $mark[$subject] !== null ? (array_search($mark[$subject], $subjectScores) + 1) : '-';
                            @endphp
                            @if ($mark[$subject] === null)
                                <td class="tiny-col" style="color:#999;font-style:italic">ABS</td>
                                <td class="tiny-col" style="color:#999;font-style:italic">ABS</td>
                                <td class="tiny-col">-</td>
                            @else
                                <td class="tiny-col">{{ $mark[$subject] }}</td>
                                <td class="tiny-col">{{ Grading::gradeSubject($mark[$subject]) }}</td>
                                <td class="tiny-col">{{ $subjectPosition }}</td>
                            @endif
                        @endforeach
                        <td class="small-col">{{ $mark['total'] }}</td>
                        <td class="small-col">{{ number_format($mark['average'], 2) }}</td>
                        <td class="small-col">
                            {{ $mark['average'] !== null ? Grading::gradeTotal($mark['total']) : Grading::absentGrade() }}
                        </td>
                        <td class="small-col">{{ $position }}</td>
                        <td class="small-col">
                            {{ $mark['average'] !== null ? Grading::statusForTotal($mark['total'], $classId) : '' }}</td>
                    </tr>
                    @php $i++; @endphp
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="text-center mt-5">
        <small>RMS TECHNOLOGY - rmstechnology.co.tz</small>
    </div>

</body>

</html>
