<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report PDF (English)</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
        }

        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-5 { margin-top: 1.25rem; }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 10pt;
            margin-left: auto;
            margin-right: auto;
        }

        th, td {
            border: 1px solid black;
            padding: 5px 8px;
            text-align: center;
            vertical-align: middle;
        }

        table.small th, table.small td {
            padding: 2px 4px;
            font-size: 10px;
        }

        .student-name {
            width: 120px;
            max-width: 120px;
            white-space: normal;
        }

        .tiny-col {
            width: 25px;
            max-width: 25px;
        }

        .small-col {
            width: 40px;
            max-width: 40px;
        }

        table.small tbody tr:nth-child(odd) { background-color: #ffffff; }
        table.small tbody tr:nth-child(even) { background-color: #f2f2f2; }

        table.small thead th { background-color: #d9d9d9; }
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
    $ranks = $reportData['ranks'];
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
        'MWISHO MUHULA WA I' => 'TERMINAL EXAM',
        'MWISHO MUHULA WA II' => 'ANNUAL EXAM',
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

    function assignGrade($marks, $ranks) {
        foreach ($ranks as $rank) {
            if ($rank['rankRangeMin'] < $marks && $rank['rankRangeMax'] >= $marks) return $rank['rankName'];
        }
        return 'Null';
    }

    function finalStatus($average, $ranks, $classId) {
        $failThreshold = $classId > 4 ? $ranks[3]['rankRangeMax'] : $ranks[4]['rankRangeMax'];
        return $average <= $failThreshold ? 'FAIL' : 'PASS';
    }

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
    <div style="text-transform: uppercase;">EXAMINATION ASSESSMENT OF {{ $examMapping[strtoupper($reportData['examName'] ?? '')] ?? $reportData['examName'] ?? '_____________________' }} CLASS
        {{ $reportData['className'] ?? '_________' }} HELD ON {{ $formattedDates ?? '_________________' }}</div>
</div>

<!-- School average and achievement -->
<div class="mb-2">
    <table>
        <thead>
            <tr><th>SCHOOL AVERAGE</th><th>GRADE</th></tr>
        </thead>
        <tbody><tr><td>{{ number_format($schoolAverage,2) }}</td><td>{{ $schoolGrade }}</td></tr></tbody>
    </table>
</div>

<div class="mb-3">
    <table>
        <thead>
            <tr><th>ACHIEVEMENT AVERAGE</th><th>GRADE</th></tr>
        </thead>
        <tbody><tr><td>{{ number_format($achievementAverage,2) }}</td><td>{{ $schoolGrade }}</td></tr></tbody>
    </table>
</div>

<!-- Performance summary -->
<div class="mt-3">
    <table>
        <tr>
            <th rowspan="2">PERFORMANCE SUMMARY</th>
            <th colspan="7">GRADE</th>
        </tr>
        <tr>
            <th>A</th><th>B</th><th>C</th><th>D</th><th>E</th><th>ABS</th><th>TOTAL</th>
        </tr>
        <tr class="bg-white">
            <td>BOYS</td>
            <td>{{ $amCount }}</td><td>{{ $bmCount }}</td><td>{{ $cmCount }}</td>
            <td>{{ $dmCount }}</td><td>{{ $emCount }}</td><td>{{ $maleAbsent }}</td>
            <td>{{ $gradeMaleCount + $maleAbsent }}</td>
        </tr>
        <tr class="bg-gray-200">
            <td>GIRLS</td>
            <td>{{ $afCount }}</td><td>{{ $bfCount }}</td><td>{{ $cfCount }}</td>
            <td>{{ $dfCount }}</td><td>{{ $efCount }}</td><td>{{ $femaleAbsent }}</td>
            <td>{{ $gradeFemaleCount + $femaleAbsent }}</td>
        </tr>
        <tr class="bg-white">
            <td>TOTAL</td>
            <td>{{ $amCount+$afCount }}</td><td>{{ $bmCount+$bfCount }}</td><td>{{ $cmCount+$cfCount }}</td>
            <td>{{ $dmCount+$dfCount }}</td><td>{{ $emCount+$efCount }}</td><td>{{ $maleAbsent+$femaleAbsent }}</td>
            <td>{{ $gradeMaleCount+$gradeFemaleCount+$maleAbsent+$femaleAbsent }}</td>
        </tr>
    </table>
</div>

<!-- Subject Grade Breakdown -->
<div class="page-break mt-5">
    <h3 class="font-bold">SUBJECT GRADE BREAKDOWN</h3>
    <table class="small">
        <thead>
            <tr>
                <th rowspan="2">SUBJECT</th>
                @foreach(['A','B','C','D','E'] as $grade)<th colspan="3">{{ $grade }}</th>@endforeach
                <th rowspan="2">SUBJECT AVERAGE</th><th rowspan="2">PASSED</th><th rowspan="2">%</th><th rowspan="2">FAILED</th><th rowspan="2">%</th>
            </tr>
            <tr>
                @foreach(range(1,5) as $dummy)<th>BOY</th><th>GIRL</th><th>TOT</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @if(count($subjects)>0)
                @php $g=0; @endphp
                @foreach($subjects as $name)
                    @php
                        $totalGradeCount = array_sum($gradeArray[$name]);
                        $failedCount = $classId>4 ? $gradeArray[$name]['D']+$gradeArray[$name]['E'] : $gradeArray[$name]['E'];
                    @endphp
                    <tr class="{{ $g%2==0?'bg-white':'bg-gray-200' }}">
                        <td>{{ strtoupper($subjectMapping[strtolower($name)] ?? $name) }}</td>
                        @foreach(['A','B','C','D','E'] as $grade)
                            <td>{{ $gradeMaleArray[$name][$grade] }}</td>
                            <td>{{ $gradeFemaleArray[$name][$grade] }}</td>
                            <td>{{ $gradeArray[$name][$grade] }}</td>
                        @endforeach
                        <td>{{ number_format($gAverage[$g]/(count($marks)-$maleAbsent-$femaleAbsent),2) }}</td>
                        <td>{{ $totalGradeCount - $failedCount }}</td>
                        <td>{{ $totalGradeCount>0 ? number_format((($totalGradeCount-$failedCount)*100)/$totalGradeCount,2) : 0 }}</td>
                        <td>{{ $failedCount }}</td>
                        <td>{{ $totalGradeCount>0 ? number_format(($failedCount*100)/$totalGradeCount,2) : 0 }}</td>
                    </tr>
                    @php $g++; @endphp
                @endforeach
            @else
                <tr><td colspan="21" class="text-red-500">No Data Found!</td></tr>
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
                @foreach($subjects as $subject)
                    <th colspan="3" class="subject_headings">{{ strtoupper($subjectMapping[strtolower($subject)] ?? $subject) }}</th>
                @endforeach
                <th rowspan="2" class="small-col">TOTAL</th>
                <th rowspan="2" class="small-col">AVERAGE</th>
                <th rowspan="2" class="small-col">GRADE</th>
                <th rowspan="2" class="small-col">POSITION</th>
                <th rowspan="2" class="small-col">STATUS</th>
            </tr>
            <tr>
                @foreach($subjects as $subject)
                    <th class="tiny-col">MRK</th>
                    <th class="tiny-col">GRD</th>
                    <th class="tiny-col">POS</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $i=1; $j=0; $storedAvg=''; @endphp
            @foreach($marks as $mark)
                @php
                    if($storedAvg == $mark['average']) { $j++; $storedAvg = $mark['average']; $position = $i - $j; }
                    else { $j = 0; $storedAvg = $mark['average']; $position = $i; }
                @endphp
                <tr class="{{ $i%2==0?'bg-gray-200':'bg-white' }}">
                    <td class="tiny-col">{{ $i }}</td>
                    <td class="student-name">{{ $mark['studentName'] }}</td>
                    @foreach($subjects as $subject)
                        @php
                            $subjectScores = collect($marks)->pluck($subject)->sortDesc()->values()->all();
                            $subjectPosition = array_search($mark[$subject], $subjectScores)+1;
                        @endphp
                        <td class="tiny-col">{{ $mark[$subject] }}</td>
                        <td class="tiny-col">{{ assignGrade($mark[$subject], $ranks) }}</td>
                        <td class="tiny-col">{{ $subjectPosition }}</td>
                    @endforeach
                    <td class="small-col">{{ $mark['total'] }}</td>
                    <td class="small-col">{{ number_format($mark['average'],2) }}</td>
                    <td class="small-col">{{ $mark['average']>0 ? assignGrade($mark['average'],$ranks) : 'HYP' }}</td>
                    <td class="small-col">{{ $position }}</td>
                    <td class="small-col">{{ $mark['average']>0 ? finalStatus($mark['average'],$ranks,$classId) : '' }}</td>
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
