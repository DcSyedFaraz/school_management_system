<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report PDF</title>
    <style>
        /* Base font and layout */
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }

        .p-3 {
            padding: 1rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .font-bold {
            font-weight: bold;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mt-5 {
            margin-top: 1.25rem;
        }

        .text-center {
            text-align: center;
        }

        .small {
            font-size: 8pt;
        }

        .subject_hradings {
            font-size: 6pt;
        }

        /* Table styling */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 2px;
            text-align: center;
        }

        .bg-white {
            background-color: white;
        }

        .bg-gray-200 {
            background-color: #e5e7eb;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .capitalize {
            text-transform: capitalize;
        }
    </style>
</head>

<body class="p-3">
    <!-- Header -->

    @php
        // Unpack precomputed data from $reportData
        $classes = $reportData['classes'];
        $exams = $reportData['exams'];
        $classId = $reportData['classId'];
        $examId = $reportData['examId'];
        $startDate = $reportData['startDate'];
        $endDate = $reportData['endDate'];
        $subjects = $reportData['subjects'];
        $marks = $reportData['marks'];
        $allMarks = $reportData['allMarks'];
        $ranks = $reportData['ranks'];
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

        // Helper functions for grade and status display.
        function assignGrade($marks, $ranks)
        {
            foreach ($ranks as $rank) {
                if ($rank['rankRangeMin'] < $marks && $rank['rankRangeMax'] >= $marks) {
                    return $rank['rankName'];
                }
            }
            return 'Null';
        }
        function finalStatus($average, $ranks, $classId)
        {
            $failThreshold = $classId > 4 ? $ranks[3]['rankRangeMax'] : $ranks[4]['rankRangeMax'];
            return $average <= $failThreshold ? 'FAIL' : 'PASS';
        }

        // Parse start and end dates (assuming they are in Y-m-d format)
        use Carbon\Carbon;
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        // Map English month names to Swahili (adjust as needed)
        $monthMap = [
            'January' => 'JANUARI',
            'February' => 'FEBRUARI',
            'March' => 'MACHI',
            'April' => 'APRILI',
            'May' => 'MEI',
            'June' => 'JUNI',
            'July' => 'JULAI',
            'August' => 'AGOSTO',
            'September' => 'SEPTEMBER',
            'October' => 'OKTOBA',
            'November' => 'NOVEMBER',
            'December' => 'DECEMBER',
        ];
        $formattedMonth = $monthMap[$end->format('F')];
        $formattedDates =
            $start->format('d') . ' - ' . $end->format('d') . ' ' . $formattedMonth . ', ' . $end->format('Y');
    @endphp

    <div class="text-center font-bold mb-2">
        <div>SHULE YA MARTIN LUTHER</div>
        <div>MARTOKEO DRS {{ $reportData['classId'] ?? '' }}</div>
        <div>TATHIMINI YA MTIHANI WA {{ $reportData['examName'] ?? '' }} WILAYA {{ $formattedDates }}</div>
    </div>




    <!-- Filter/Summary Information -->
    <div>
        <p>Darasa: {{ $classId }}</p>
        <p>Mtihani: {{ $examId }}</p>
        <p>Tarehe: {{ $startDate }} hadi {{ $endDate }}</p>
    </div>

    <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-2 mt-5">
        <div>
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="border border-black p-1 uppercase">Wastani Wa Shule</th>
                        <th class="border border-black p-1 uppercase">Daraja</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white">
                        <td class="border border-black p-1 text-center">{{ number_format($schoolAverage, 2) }}</td>
                        <td class="border border-black p-1 text-center">{{ $schoolGrade }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="border border-black p-1 uppercase">Wastani Wa Ufaulu</th>
                        <th class="border border-black p-1 uppercase">Daraja</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white">
                        <td class="border border-black p-1 text-center">{{ number_format($achievementAverage, 2) }}
                        </td>
                        <td class="border border-black p-1 text-center">{{ $schoolGrade }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TATHIMINI YA UFAULU Summary Table -->
    <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-2 mt-5">
        <div>
            <table class="w-full">
                <tr>
                    <th rowspan="2" class="text-center border border-black">TATHIMINI YA UFAULU</th>
                    <th colspan="7" class="text-center border border-black uppercase">Daraja</th>
                </tr>
                <tr>
                    <th class="border border-black">A</th>
                    <th class="border border-black">B</th>
                    <th class="border border-black">C</th>
                    <th class="border border-black">D</th>
                    <th class="border border-black">E</th>
                    <th class="border border-black">ABS</th>
                    <th class="border border-black uppercase">Jumla</th>
                </tr>
                <tr class="bg-white">
                    <td class="border border-black text-center">WAV</td>
                    <td class="border border-black text-center">{{ $amCount }}</td>
                    <td class="border border-black text-center">{{ $bmCount }}</td>
                    <td class="border border-black text-center">{{ $cmCount }}</td>
                    <td class="border border-black text-center">{{ $dmCount }}</td>
                    <td class="border border-black text-center">{{ $emCount }}</td>
                    <td class="border border-black text-center">{{ $maleAbsent }}</td>
                    <td class="border border-black text-center">{{ $gradeMaleCount + $maleAbsent }}</td>
                </tr>
                <tr class="bg-gray-200">
                    <td class="border border-black text-center">WAS</td>
                    <td class="border border-black text-center">{{ $afCount }}</td>
                    <td class="border border-black text-center">{{ $bfCount }}</td>
                    <td class="border border-black text-center">{{ $cfCount }}</td>
                    <td class="border border-black text-center">{{ $dfCount }}</td>
                    <td class="border border-black text-center">{{ $efCount }}</td>
                    <td class="border border-black text-center">{{ $femaleAbsent }}</td>
                    <td class="border border-black text-center">{{ $gradeFemaleCount + $femaleAbsent }}</td>
                </tr>
                <tr class="bg-white">
                    <td class="border border-black text-center">Jumla</td>
                    <td class="border border-black text-center">{{ $amCount + $afCount }}</td>
                    <td class="border border-black text-center">{{ $bmCount + $bfCount }}</td>
                    <td class="border border-black text-center">{{ $cmCount + $cfCount }}</td>
                    <td class="border border-black text-center">{{ $dmCount + $dfCount }}</td>
                    <td class="border border-black text-center">{{ $emCount + $efCount }}</td>
                    <td class="border border-black text-center">{{ $maleAbsent + $femaleAbsent }}</td>
                    <td class="border border-black text-center">
                        {{ $gradeMaleCount + $gradeFemaleCount + $maleAbsent + $femaleAbsent }}
                    </td>
                </tr>
            </table>
        </div>
        <div>
            <table class="w-full">
                <thead>
                    <tr>
                        <th colspan="2" class="border border-black px-2 text-center">WALIOSAJILIWA</th>
                        <th class="border border-black px-2 text-center uppercase">Pass</th>
                        <th class="border border-black px-2 text-center uppercase">Fail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white text-center">
                        <td class="border border-black px-2">WAV</td>
                        <td class="border border-black px-2">{{ $gradeMaleCount }}</td>
                        <td class="border border-black px-2">{{ $gradeMaleCount - $failMaleCount }}</td>
                        <td class="border border-black px-2">{{ $failMaleCount }}</td>
                    </tr>
                    <tr class="bg-gray-200 text-center">
                        <td class="border border-black px-2">WAS</td>
                        <td class="border border-black px-2">{{ $gradeFemaleCount }}</td>
                        <td class="border border-black px-2">{{ $gradeFemaleCount - $failFemaleCount }}</td>
                        <td class="border border-black px-2">{{ $failFemaleCount }}</td>
                    </tr>
                    <tr class="bg-white text-center">
                        <td class="border border-black px-2" rowspan="2">Jumla</td>
                        <td class="border border-black px-2" rowspan="2">{{ $gradeCount }}</td>
                        <td class="border border-black px-2">{{ $gradeCount - $failCount }}</td>
                        <td class="border border-black px-2">{{ $failCount }}</td>
                    </tr>
                    <tr class="bg-gray-200 text-center">
                        <td class="border border-black px-2">
                            @php $passTitle = $classId > 4 ? '% Pass(A-C)' : '% Pass(A-D)'; @endphp
                            <span>{{ $passTitle }}:</span>
                            {{ number_format((($gradeCount - $failCount) * 100) / $gradeCount, 2) }}
                        </td>
                        <td class="border border-black px-2">
                            @php $failTitle = $classId > 4 ? '% Fail(D-E)' : '% Fail(E)'; @endphp
                            <span>{{ $failTitle }}:</span>
                            {{ number_format(($failCount * 100) / $gradeCount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Subject Grade Breakdown (TATHIMINI YA MADARAJA YA KILA SOMO) -->
    <div class="mt-5">
        <h3 class="font-bold">TATHIMINI YA MADARAJA YA KILA SOMO</h3>
        <table class="small">
            <thead>
                <tr>
                    <th rowspan="2">Somo</th>
                    @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                        <th colspan="3">{{ $grade }}</th>
                    @endforeach
                    <th rowspan="2">Wastani Ya Somo</th>
                    <th rowspan="2">Walio Faulu</th>
                    <th rowspan="2">%</th>
                    <th rowspan="2">Wasio Faulu</th>
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
                    @php $g = 0; @endphp
                    @foreach ($subjects as $name)
                        @php
                            $totalGradeCount = array_sum($gradeArray[$name]);
                            $failedCount =
                                $classId > 4
                                    ? $gradeArray[$name]['D'] + $gradeArray[$name]['E']
                                    : $gradeArray[$name]['E'];
                        @endphp
                        <tr class="{{ $g % 2 == 0 ? 'bg-white' : 'bg-gray-200' }}">
                            <td class="capitalize">{{ $name }}</td>
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                                <td>{{ $gradeMaleArray[$name][$grade] }}</td>
                                <td>{{ $gradeFemaleArray[$name][$grade] }}</td>
                                <td>{{ $gradeArray[$name][$grade] }}</td>
                            @endforeach
                            <td>{{ number_format($gAverage[$g] / (count($marks) - $maleAbsent - $femaleAbsent), 2) }}
                            </td>
                            <td>{{ $totalGradeCount - $failedCount }}</td>
                            <td>
                                @if ($totalGradeCount > 0)
                                    {{ number_format((($totalGradeCount - $failedCount) * 100) / $totalGradeCount, 2) }}
                                @else
                                    0
                                @endif
                            </td>
                            <td>{{ $failedCount }}</td>
                            <td>
                                @if ($totalGradeCount > 0)
                                    {{ number_format(($failedCount * 100) / $totalGradeCount, 2) }}
                                @else
                                    0
                                @endif
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

    <!-- Marks Table -->
    <div class="mt-5">
        <h3 class="font-bold">MATOKEO KWA MPANGILIO WA WANAFUNZI WOTE</h3>
        <table class="small">
            <thead>
                <tr>
                    <th rowspan="2">S/N</th>
                    <th rowspan="2">Jina la Mwanafunzi</th>
                    @foreach ($subjects as $subject)
                        <th colspan="2" class="uppercase subject_hradings">{{ $subject }}</th>
                    @endforeach
                    <th rowspan="2">Jumla</th>
                    <th rowspan="2">Wastani</th>
                    <th rowspan="2">Daraja</th>
                    <th rowspan="2">Nafasi</th>
                    <th rowspan="2">Ufaulu</th>
                </tr>
                <tr>
                    @foreach ($subjects as $subject)
                        <th>AL</th>
                        <th>DRJ</th>
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
                        if ($storedAvg == $mark['average']) {
                            $j++;
                            $storedAvg = $mark['average'];
                            $position = $i - $j;
                        } else {
                            $j = 0;
                            $storedAvg = $mark['average'];
                            $position = $i;
                        }
                    @endphp
                    <tr class="{{ $i % 2 == 0 ? 'bg-gray-200' : 'bg-white' }}">
                        <td>{{ $i }}</td>
                        <td>{{ $mark['studentName'] }}</td>
                        @foreach ($subjects as $subject)
                            <td>{{ $mark[$subject] }}</td>
                            <td>{{ assignGrade($mark[$subject], $ranks) }}</td>
                        @endforeach
                        <td>{{ $mark['total'] }}</td>
                        <td>{{ number_format($mark['average'], 2) }}</td>
                        @if ($mark['average'] > 0)
                            <td>{{ assignGrade($mark['average'], $ranks) }}</td>
                        @else
                            <td>ABS</td>
                        @endif
                        <td>{{ $position }}</td>
                        @if ($mark['average'] > 0)
                            <td>{{ finalStatus($mark['average'], $ranks, $classId) }}</td>
                        @else
                            <td></td>
                        @endif
                    </tr>
                    @php $i++; @endphp
                @endforeach
            </tbody>
        </table>
    </div>



    <!-- Footer -->
    <div class="text-center mt-5">
        <small>RMS TECHNOLOGY - rmstechnology.co.tz</small>
    </div>
</body>

</html>
