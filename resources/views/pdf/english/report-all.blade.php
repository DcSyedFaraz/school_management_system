<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report PDF (English)</title>
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

        .bg-white {
            background-color: #ffffff;
        }

        .bg-gray-200 {
            background-color: #e5e7eb;
        }

        /* Fixed grade colors — shared with the on-screen report pages. */
        .grade-a {
            background-color: #dcfce7;
            color: #166534;
        }

        .grade-b {
            background-color: #ecfccb;
            color: #3f6212;
        }

        .grade-c {
            background-color: #fef9c3;
            color: #854d0e;
        }

        .grade-d {
            background-color: #ffedd5;
            color: #9a3412;
        }

        .grade-e {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .grade-abs {
            background-color: #e5e7eb;
            color: #6b7280;
            font-style: italic;
        }

        .bg-pass {
            background-color: #dcfce7;
            color: #166534;
        }

        .bg-fail {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Value-conditional percentage colors (text only, no background). */
        .pct-high {
            color: #16a34a;
            font-weight: bold;
        }

        .pct-mid {
            color: #d97706;
            font-weight: bold;
        }

        .pct-low {
            color: #dc2626;
            font-weight: bold;
        }

        .status-pass {
            color: #16a34a;
            font-weight: bold;
        }

        .status-fail {
            color: #dc2626;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        // Fixed grade -> CSS class mapping, shared by every grade cell/header in this PDF.
        $gradeColorMap = [
            'A' => 'grade-a',
            'B' => 'grade-b',
            'C' => 'grade-c',
            'D' => 'grade-d',
            'E' => 'grade-e',
            'ABS' => 'grade-abs',
        ];
        // Value-conditional tier for a "higher is better" percentage (0-100).
        $passPctColor = fn($pct) => $pct >= 80 ? 'pct-high' : ($pct >= 50 ? 'pct-mid' : 'pct-low');

        $classes = $reportData['classes'];
        $exams = $reportData['exams'];
        $classId = $reportData['classId'];
        $startDate = $reportData['startDate'];
        $endDate = $reportData['endDate'];
        $subjects = $reportData['subjects'];
        $marks = $reportData['marks'];
        $gAverage = $reportData['gAverage'];

        // Subject mapping from Swahili to English
        $subjectMapping = [
            'hisabati' => 'Mathematics',
            'sayansi' => 'Science',
            'jiographia' => 'Geography',
            'historia' => 'History of Tanzania',
            'mazingira' => 'Health and Environment',
            'michezo' => 'Arts and Sports',
            'utamaduni' => 'Culture, Arts and Sports',
            'jamii' => 'Social Studies',
            'maadili' => 'Civics and Morals',
            's_kazi' => 'Vocational Skills',
        ];

        // Exam mapping from Swahili to English
        $examMapping = [
            'NUSU MUHULA' => 'MID-TERM EXAM',
            'MWISHO MUHULA WA I' => 'TERMINAL EXAM',
            'MWISHO MUHULA WA II' => 'ANNUAL EXAM',
            'WIKI' => 'WEEKLY EXAM',
        ];

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
        $formattedDates = Carbon::parse($dateToUse)->format('d F, Y');
    @endphp

    <div class="text-center font-bold mb-2 uppercase">
        <div>PRIME MINISTER'S OFFICE - TAMISEMI</div>
        <div>{{ $reportData['districtName'] ?? '_____________________' }} DISTRICT COUNCIL</div>
        <div>{{ $reportData['schoolName'] ?? '_____________________' }} PRIMARY SCHOOL</div>
        <div style="text-transform: uppercase;">EXAMINATION ASSESSMENT OF
            {{ $examMapping[strtoupper($reportData['examName'] ?? '')] ?? ($reportData['examName'] ?? '_____________________') }}
            CLASS
            {{ $reportData['className'] ?? '_________' }} HELD ON {{ $formattedDates ?? '_________________' }}</div>
    </div>

    <!-- School average and achievement -->
    <div class="mb-2">
        <table>
            <thead>
                <tr>
                    <th>SCHOOL AVERAGE</th>
                    <th>GRADE</th>
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
                    <th>ACHIEVEMENT AVERAGE</th>
                    <th>GRADE</th>
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

    <!-- Performance summary -->
    <div class="mt-3">
        <table style="table-layout: fixed;">
            <tr>
                <td style="width: 60%; border: none; padding: 0; vertical-align: top;">
                    <table>
                        <tr>
                            <th rowspan="2">PERFORMANCE SUMMARY</th>
                            <th colspan="7">GRADE</th>
                        </tr>
                        <tr>
                            <th class="grade-a">A</th>
                            <th class="grade-b">B</th>
                            <th class="grade-c">C</th>
                            <th class="grade-d">D</th>
                            <th class="grade-e">E</th>
                            <th class="grade-abs">ABS</th>
                            <th>TOTAL</th>
                        </tr>
                        <tr class="bg-white">
                            <td>BOYS</td>
                            <td>{{ $amCount }}</td>
                            <td>{{ $bmCount }}</td>
                            <td>{{ $cmCount }}</td>
                            <td>{{ $dmCount }}</td>
                            <td>{{ $emCount }}</td>
                            <td>{{ $maleAbsent }}</td>
                            <td>{{ $gradeMaleCount + $maleAbsent }}</td>
                        </tr>
                        <tr class="bg-gray-200">
                            <td>GIRLS</td>
                            <td>{{ $afCount }}</td>
                            <td>{{ $bfCount }}</td>
                            <td>{{ $cfCount }}</td>
                            <td>{{ $dfCount }}</td>
                            <td>{{ $efCount }}</td>
                            <td>{{ $femaleAbsent }}</td>
                            <td>{{ $gradeFemaleCount + $femaleAbsent }}</td>
                        </tr>
                        <tr class="bg-white">
                            <td>TOTAL</td>
                            <td>{{ $amCount + $afCount }}</td>
                            <td>{{ $bmCount + $bfCount }}</td>
                            <td>{{ $cmCount + $cfCount }}</td>
                            <td>{{ $dmCount + $dfCount }}</td>
                            <td>{{ $emCount + $efCount }}</td>
                            <td>{{ $maleAbsent + $femaleAbsent }}</td>
                            <td>{{ $gradeMaleCount + $gradeFemaleCount + $maleAbsent + $femaleAbsent }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 40%; border: none; padding: 0 0 0 6px; vertical-align: top;">
                    <table>
                        <thead>
                            <tr>
                                <th colspan="2">TOOK EXAM</th>
                                <th class="bg-pass">PASSED</th>
                                <th class="bg-fail">FAILED</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white">
                                <td>BOYS</td>
                                <td>{{ $gradeMaleCount }}</td>
                                <td>{{ $gradeMaleCount - $failMaleCount }}</td>
                                <td>{{ $failMaleCount }}</td>
                            </tr>
                            <tr class="bg-gray-200">
                                <td>GIRLS</td>
                                <td>{{ $gradeFemaleCount }}</td>
                                <td>{{ $gradeFemaleCount - $failFemaleCount }}</td>
                                <td>{{ $failFemaleCount }}</td>
                            </tr>
                            <tr class="bg-white">
                                <td rowspan="2">TOTAL</td>
                                <td rowspan="2">{{ $gradeCount }}</td>
                                <td>{{ $gradeCount - $failCount }}</td>
                                <td>{{ $failCount }}</td>
                            </tr>
                            <tr class="bg-gray-200">
                                @php
                                    $passTitle = $classId > 4 ? '% Pass(A-C)' : '% Pass(A-D)';
                                    $failTitle = $classId > 4 ? '% Fail(D-E)' : '% Fail(E)';
                                    $waliofanyaPassPct = $gradeCount > 0 ? (($gradeCount - $failCount) * 100) / $gradeCount : 0;
                                @endphp
                                <td class="{{ $passPctColor($waliofanyaPassPct) }}">{{ $passTitle }}: {{ number_format($waliofanyaPassPct, 2) }}</td>
                                <td class="{{ $passPctColor($waliofanyaPassPct) }}">{{ $failTitle }}: {{ $gradeCount > 0 ? number_format(($failCount * 100) / $gradeCount, 2) : 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Subject Grade Breakdown -->
    <div class="page-break mt-5">
        <h3 class="font-bold">SUBJECT GRADE BREAKDOWN</h3>
        <table class="small">
            <thead>
                <tr>
                    <th rowspan="2" class="subject-col">SUBJECT</th>
                    @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                        <th colspan="3" class="{{ $gradeColorMap[$grade] }}">{{ $grade }}</th>
                    @endforeach
                    <th rowspan="2">SUBJECT AVERAGE</th>
                    <th rowspan="2">PASSED</th>
                    <th rowspan="2">%</th>
                    <th rowspan="2">FAILED</th>
                    <th rowspan="2">%</th>
                </tr>
                <tr>
                    @foreach (range(1, 5) as $dummy)
                        <th>BOY</th>
                        <th>GIRL</th>
                        <th>TOT</th>
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
                            $subjectPassPct = $totalGradeCount > 0 ? (($totalGradeCount - $failedCount) * 100) / $totalGradeCount : 0;
                        @endphp
                        <tr class="{{ $g % 2 == 0 ? 'bg-white' : 'bg-gray-200' }}">
                            <td class="subject-col">{{ strtoupper($subjectMapping[strtolower($name)] ?? $name) }}</td>
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                                <td>{{ $gradeMaleArray[$name][$grade] }}</td>
                                <td>{{ $gradeFemaleArray[$name][$grade] }}</td>
                                <td>{{ $gradeArray[$name][$grade] }}</td>
                            @endforeach
                            <td>{{ number_format($gAverage[$g] / (count($marks) - $maleAbsent - $femaleAbsent), 2) }}</td>
                            <td>{{ $totalGradeCount - $failedCount }}</td>
                            <td class="{{ $passPctColor($subjectPassPct) }}">{{ number_format($subjectPassPct, 2) }}
                            </td>
                            <td>{{ $failedCount }}</td>
                            <td class="{{ $passPctColor($subjectPassPct) }}">{{ $totalGradeCount > 0 ? number_format(($failedCount * 100) / $totalGradeCount, 2) : 0 }}
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
        <h3 class="font-bold">RESULTS IN STUDENT RANKING ORDER</h3>
        <table class="small">
            <thead>
                <tr>
                    <th rowspan="2" class="tiny-col">S/N</th>
                    <th rowspan="2" class="student-name">STUDENT NAME</th>
                    @foreach ($subjects as $subject)
                        <th colspan="3" class="subject_headings">
                            {{ strtoupper($subjectMapping[strtolower($subject)] ?? $subject) }}</th>
                    @endforeach
                    <th rowspan="2" class="small-col">TOTAL</th>
                    <th rowspan="2" class="small-col">AVERAGE</th>
                    <th rowspan="2" class="small-col">GRADE</th>
                    <th rowspan="2" class="small-col">POSITION</th>
                    <th rowspan="2" class="small-col">STATUS</th>
                </tr>
                <tr>
                    @foreach ($subjects as $subject)
                        <th class="tiny-col">MRK</th>
                        <th class="tiny-col">GRD</th>
                        <th class="tiny-col">POS</th>
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
                                <td class="tiny-col grade-abs">ABS</td>
                                <td class="tiny-col grade-abs">ABS</td>
                                <td class="tiny-col">-</td>
                            @else
                                @php $subjectGrade = Grading::gradeSubject($mark[$subject]); @endphp
                                <td class="tiny-col">{{ $mark[$subject] }}</td>
                                <td class="tiny-col {{ $gradeColorMap[$subjectGrade] ?? '' }}">{{ $subjectGrade }}</td>
                                <td class="tiny-col">{{ $subjectPosition }}</td>
                            @endif
                        @endforeach
                        <td class="small-col">{{ $mark['total'] }}</td>
                        <td class="small-col">{{ number_format($mark['average'], 2) }}</td>
                        @php $overallGrade = $mark['average'] !== null ? Grading::gradeTotal($mark['total']) : Grading::absentGrade(); @endphp
                        <td class="small-col {{ $gradeColorMap[$overallGrade] ?? '' }}">{{ $overallGrade }}
                        </td>
                        <td class="small-col">{{ $position }}</td>
                        @php $status = $mark['average'] !== null ? Grading::statusForTotal($mark['total'], $classId, 'en') : ''; @endphp
                        <td class="small-col {{ $status === 'PASS' ? 'status-pass' : ($status === 'FAIL' ? 'status-fail' : '') }}">
                            {{ $status }}</td>
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
