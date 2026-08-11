@extends('admin.layout')

@section('content')
    @php
        // --- Assume these variables are provided from the controller ---
        // $classId, $examId, $startDate, $endDate, $classes, $exams, $subjects, $marks, $allMarks
        // --- PRE-COMPUTE CALCULATED DATA ---

        // Fixed grade -> color mapping, shared by every grade cell/header on this page.
        $gradeColorMap = [
            'A' => 'bg-green-100 text-green-800',
            'B' => 'bg-lime-100 text-lime-800',
            'C' => 'bg-yellow-100 text-yellow-800',
            'D' => 'bg-orange-100 text-orange-800',
            'E' => 'bg-red-100 text-red-800',
            'ABS' => 'bg-gray-200 text-gray-500 italic',
        ];
        // Value-conditional tier for a pass percentage (0-100): high is green, mid is amber, low is red.
        $passPctColor = fn($pct) => $pct >= 80 ? 'text-green-600' : ($pct >= 50 ? 'text-amber-600' : 'text-red-600');

        $gAverage = array_fill(0, count($subjects), 0);
        $amCount = 0;
        $bmCount = 0;
        $cmCount = 0;
        $dmCount = 0;
        $emCount = 0;
        $afCount = 0;
        $bfCount = 0;
        $cfCount = 0;
        $dfCount = 0;
        $efCount = 0;
        $maleAbsent = 0;
        $femaleAbsent = 0;

        foreach ($marks as &$mark) {
            $totalMarks = 0;
            $validSubjectsCount = 0;

            foreach ($subjects as $index => $subject) {
                if ($mark[$subject] !== null) {
                    // Hesabu tu masomo yaliyo na alama (0 ni alama halisi)
                    $totalMarks += $mark[$subject];
                    $validSubjectsCount++;

                    // Jumlisha kwa gAverage ya somo (subject-wise total)
                    $gAverage[$index] += $mark[$subject];
                }
            }

            // Hesabu average halisi kwa kuzingatia masomo yaliyo na alama tu
            $mark['average'] = $validSubjectsCount > 0 ? $totalMarks / $validSubjectsCount : null;
            // Recompute total from the same loop so the displayed total and
            // the graded total can never diverge from marks.total.
            $mark['total'] = $totalMarks;

            if ($mark['average'] === null) {
                $mark['gender'] == 'M' ? $maleAbsent++ : $femaleAbsent++;
            } else {
                // Daraja is based on TOTAL, not average.
                $grade = Grading::gradeTotal($mark['total']);
                switch ($grade) {
                    case 'A':
                        $mark['gender'] == 'M' ? $amCount++ : $afCount++;
                        break;
                    case 'B':
                        $mark['gender'] == 'M' ? $bmCount++ : $bfCount++;
                        break;
                    case 'C':
                        $mark['gender'] == 'M' ? $cmCount++ : $cfCount++;
                        break;
                    case 'D':
                        $mark['gender'] == 'M' ? $dmCount++ : $dfCount++;
                        break;
                    case 'E':
                        $mark['gender'] == 'M' ? $emCount++ : $efCount++;
                        break;
                }
            }
        }
        unset($mark);

        // Compute overall summary counts
        $gradeMaleCount = $amCount + $bmCount + $cmCount + $dmCount + $emCount;
        $gradeFemaleCount = $afCount + $bfCount + $cfCount + $dfCount + $efCount;
        $gradeCount = $gradeMaleCount + $gradeFemaleCount;

        if ($classId > 4) {
            $failCount = $dmCount + $emCount + $dfCount + $efCount;
            $failMaleCount = $dmCount + $emCount;
            $failFemaleCount = $dfCount + $efCount;
        } else {
            $failCount = $emCount + $efCount;
            $failMaleCount = $emCount;
            $failFemaleCount = $efCount;
        }

        $gATotal = array_sum($gAverage);
        $totalStudentsCount = count($marks) - $maleAbsent - $femaleAbsent;
        $schoolAverage = $totalStudentsCount > 0 ? $gATotal / (count($subjects) * $totalStudentsCount) : 0;
        $achievementAverage = $totalStudentsCount > 0 ? $gATotal / $totalStudentsCount : 0;
        // schoolGrade is derived from the mean TOTAL (achievementAverage),
        // so it is correct for BOTH summary boxes below — they display the
        // same underlying figure on two different scales (mean subject mark
        // vs mean total) but must show the same Daraja.
        $schoolGrade = Grading::gradeTotal($achievementAverage);
        // Prepare grade arrays per subject for detailed summary
        $gradeArray = array_fill_keys($subjects, ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0]);
        $gradeMaleArray = array_fill_keys($subjects, ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0]);
        $gradeFemaleArray = array_fill_keys($subjects, ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0]);

        foreach ($allMarks as $aMark) {
            if ($aMark['total'] != 0) {
                foreach ($subjects as $list) {
                    $grade = Grading::gradeSubject($aMark[$list]);
                    if ($grade != Grading::absentGrade()) {
                        if ($aMark['gender'] == 'M') {
                            $gradeMaleArray[$list][$grade]++;
                        } else {
                            $gradeFemaleArray[$list][$grade]++;
                        }
                        $gradeArray[$list][$grade]++;
                    }
                }
            }
        }

        // Retrieve the grade name based on selected classId
        $selectedClass = collect($classes)->firstWhere('gradeId', $classId);
        $selectedGradeName = $selectedClass ? $selectedClass['gradeName'] : 'Unknown Grade';
        $selectedexam = collect($exams)->firstWhere('examId', $examId);
        $selectedexamName = $selectedexam ? $selectedexam['examName'] : 'Unknown Exam';

        // --- PACK ALL CALCULATED DATA INTO A SINGLE ARRAY FOR THE PDF ---
        $reportData = [
            'classes' => $classes,
            'exams' => $exams,
            'classId' => $classId,
            'className' => $selectedGradeName,
            'examName' => $selectedexamName,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'subjects' => $subjects,
            'marks' => $marks,
            'allMarks' => $allMarks,
            'gAverage' => $gAverage,
            'maleAbsent' => $maleAbsent,
            'femaleAbsent' => $femaleAbsent,
            'schoolAverage' => $schoolAverage,
            'achievementAverage' => $achievementAverage,
            'schoolGrade' => $schoolGrade,
            'amCount' => $amCount,
            'bmCount' => $bmCount,
            'cmCount' => $cmCount,
            'dmCount' => $dmCount,
            'emCount' => $emCount,
            'afCount' => $afCount,
            'bfCount' => $bfCount,
            'cfCount' => $cfCount,
            'dfCount' => $dfCount,
            'efCount' => $efCount,
            'gradeMaleCount' => $gradeMaleCount,
            'gradeFemaleCount' => $gradeFemaleCount,
            'gradeCount' => $gradeCount,
            'failCount' => $failCount,
            'failMaleCount' => $failMaleCount,
            'failFemaleCount' => $failFemaleCount,
            'gradeArray' => $gradeArray,
            'gradeMaleArray' => $gradeMaleArray,
            'gradeFemaleArray' => $gradeFemaleArray,
        ];

    @endphp
    <div class="p-3">
        <div id="toast-container" class="fixed top-0 right-0 z-50 space-y-4 p-4">
            <div id="toast-message" class="hidden bg-red-500 text-white p-4 rounded-lg shadow-md">
                <p id="toast-text"></p>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <!-- English Print Report -->
            <form id="printReportFormEnglish" action="{{ url('/report.student.english') }}" method="post" target="_blank">
                @csrf
                <input type="hidden" name="openingDate" id="openingDateEnglish">
                <input type="hidden" name="closingDate" id="closingDateEnglish">
                <input type="hidden" name="selectedStudents" id="selectedStudentsEnglish">
                <button type="button" onclick="openModalEnglish()"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md disabled:bg-gray-300 disabled:text-gray-600 disabled:cursor-not-allowed"
                    disabled>
                    <i class="material-symbols-outlined text-sm">print</i> <span>Print Report ENG</span>
                </button>
            </form>


            <form id="printAllReportFormEnglish" action="{{ url('/report.school.english') }}" method="post"
                target="_blank">
                @csrf
                <input type="hidden" name="reportData" value='{!! json_encode($reportData, JSON_HEX_APOS) !!}'>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md">
                    <i class="material-symbols-outlined text-sm">print</i> <span>Print Results PDF-ENG</span>
                </button>
            </form>

            <form id="printReportForm" action="{{ url('/printReport') }}" method="post" target="_blank">
                @csrf
                <input type="hidden" name="openingDate" id="openingDate">
                <input type="hidden" name="closingDate" id="closingDate">
                <input type="hidden" name="selectedStudents" id="selectedStudents">
                <button type="button" onclick="openModal()"
                    class="bg-cyan-500 hover:bg-cyan-600 text-white py-1 px-2 rounded-md disabled:bg-gray-300 disabled:text-gray-600 disabled:cursor-not-allowed">
                    <i class="material-symbols-outlined text-sm">print</i> <span>Chapisha Ripoti</span>
                </button>
            </form>

            <form id="printAllReportForm" action="{{ url('/printAllReport') }}" method="post" target="_blank">
                @csrf
                <input type="hidden" name="reportData" value='{!! json_encode($reportData, JSON_HEX_APOS) !!}'>
                <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 text-white py-1 px-2 rounded-md">
                    <i class="material-symbols-outlined text-sm">print</i> <span>Chapisha Matokeo PDF</span>
                </button>
            </form>

            <form action="{{ url('/downloadTeacherReport') }}" method="post">
                @csrf
                <input type="hidden" name="rClass" id="rClass" value="{{ $classId }}">
                <input type="hidden" name="rExam" id="rExam" value="{{ $examId }}">
                <input type="hidden" name="rStartDate" id="rStartDate" value="{{ $startDate }}">
                <input type="hidden" name="rEndDate" id="rEndDate" value="{{ $endDate }}">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded-md">
                    <i class="material-symbols-outlined text-sm">download</i> <span>Pakua Matokeo</span>
                </button>
            </form>
        </div>

        <!-- FILTER FORM & TABLES (exactly as in your original code) -->
        <div class="my-3">
            <h2 class="text-2xl font-bold">Kichujio:</h2>
            <form action="{{ url('/filterUserReport') }}" method="post" id="filterForm">
                @csrf
                <div class="grid lg:grid-cols-4 md:grid-cols-4 grid-cols-1 gap-2">
                    <div>
                        <label for="class">Chagua Darasa:<span class="text-red-500">*</span></label>
                        <select class="block w-full p-2 rounded-md border border-black" name="class" id="class"
                            required>
                            <option value="">-- CHAGUA DARASA --</option>
                            @if (count($classes) > 0)
                                @foreach ($classes as $class)
                                    <option value="{{ $class['gradeId'] }}" @selected($classId == $class['gradeId'])>
                                        {{ $class['gradeName'] }}</option>
                                @endforeach
                            @else
                                <option value="" class="text-red-500">No Data Found!</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label for="exam">Chagua Mtihani:</label>
                        <select class="block w-full p-2 rounded-md border border-black" name="exam" id="exam">
                            <option value="">-- CHAGUA MTIHANI --</option>
                            @if (count($exams) > 0)
                                @foreach ($exams as $exam)
                                    <option value="{{ $exam['examId'] }}" @selected($examId == $exam['examId'])>
                                        {{ $exam['examName'] }}</option>
                                @endforeach
                            @else
                                <option value="" class="text-red-500">No Data Found!</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label for="startDate">Tarehe ya Kuanza:</label>
                        <input type="date" class="block w-full p-2 rounded-md border border-black"
                            min="{{ date('Y-m-d', strtotime('2023-01-01')) }}" max="{{ date('Y-m-d') }}"
                            name="startDate" id="startDate" value="{{ date('Y-m-d', strtotime($startDate)) }}"
                            onchange="setEndDate()">
                    </div>
                    <div>
                        <label for="endDate">Tarehe ya Mwisho:</label>
                        <input type="date" class="block w-full p-2 rounded-md border border-black"
                            min="{{ date('Y-m-d', strtotime('2023-01-01')) }}" max="{{ date('Y-m-d') }}" name="endDate"
                            id="endDate" value="{{ date('Y-m-d', strtotime($endDate)) }}">
                    </div>
                </div>
            </form>
            <div class="flex justify-end">
                <a href="{{ url('/dashboard/reports') }}">
                    <button type="button" form="filterForm"
                        class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">
                        Onesha Upya
                    </button>
                </a>
                <button type="submit" form="filterForm"
                    class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">
                    Kichujio
                </button>
            </div>
        </div>

        <!-- The complete marks table, summaries, and detailed grade breakdown exactly as above -->
        <div class="overflow-x-auto">
            <h2 class="text-2xl font-bold mb-2">MATOKEO KWA MPANGILIO WA WANAFUNZI WOTE:</h2>
            <input type="checkbox" id="selectAll"> Chagua Wote
            <table class="myTable bg-white text-xs">
                <thead>
                    <tr>
                        <th rowspan="2" class="border border-black text-center">S/N</th>
                        <th rowspan="2" class="border border-black uppercase text-center">Jina la Mwanafunzi</th>
                        @foreach ($subjects as $subject)
                            <th colspan="3" class="border border-black uppercase text-center">{{ $subject }}</th>
                        @endforeach
                        <th rowspan="2" class="border border-black uppercase text-center">Jumla</th>
                        <th rowspan="2" class="border border-black uppercase text-center">Wastani</th>
                        <th rowspan="2" class="border border-black uppercase text-center">Daraja</th>
                        <th rowspan="2" class="border border-black uppercase text-center">Nafasi</th>
                        <th rowspan="2" class="border border-black uppercase text-center">Ufaulu</th>
                    </tr>
                    <tr>
                        @foreach ($subjects as $subject)
                            <th class="border border-black text-center">AL</th>
                            <th class="border border-black text-center">DRJ</th>
                            <th class="border border-black text-center">NFS</th>
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
                        <tr class="odd:bg-gray-200">
                            <td class="border border-black text-center">
                                <input type="checkbox" class="studentCheckbox"
                                    value="{{ json_encode([
                                        'id' => $mark['markId'],
                                        'studentName' => $mark['studentName'],
                                        'subjects' => collect($subjects)->map(function ($subject) use ($mark, $marks) {
                                                $subjectScores = collect($marks)->pluck($subject)->sortDesc()->values()->all();
                                                $position = array_search($mark[$subject], $subjectScores) + 1;
                                                return [
                                                    'name' => $subject,
                                                    'mark' => $mark[$subject],
                                                    'grade' => Grading::gradeSubject($mark[$subject]),
                                                    'position' => $position,
                                                ];
                                            })->all(),
                                        'totalMarks' => $mark['total'],
                                        'average' => $mark['average'],
                                        'grade' => $mark['average'] !== null ? Grading::gradeTotal($mark['total']) : Grading::absentGrade(),
                                        'position' => $loop->index + 1,
                                    ]) }}">
                                {{ $i }}
                            </td>
                            <td class="capitalize border border-black text-center">{{ $mark['studentName'] }}</td>

                            @foreach ($subjects as $subject)
                                @php
                                    $subjectScores = collect($marks)->pluck($subject)->filter(fn($v) => $v !== null)->sortDesc()->values()->all();
                                    $subjectPosition = $mark[$subject] !== null ? (array_search($mark[$subject], $subjectScores) + 1) : '-';
                                @endphp
                                @if ($mark[$subject] === null)
                                    <td class="border border-black text-center {{ $gradeColorMap['ABS'] }}">ABS</td>
                                    <td class="border border-black text-center {{ $gradeColorMap['ABS'] }}">ABS</td>
                                    <td class="border border-black text-center">-</td>
                                @else
                                    @php $subjectGrade = Grading::gradeSubject($mark[$subject]); @endphp
                                    <td class="border border-black text-center">{{ $mark[$subject] }}</td>
                                    <td class="border border-black text-center {{ $gradeColorMap[$subjectGrade] ?? '' }}">{{ $subjectGrade }}</td>
                                    <td class="border border-black text-center">{{ $subjectPosition }}</td>
                                @endif
                            @endforeach

                            <td class="border border-black text-center">{{ $mark['total'] }}</td>
                            <td class="border border-black text-center">{{ number_format($mark['average'], 2) }}</td>
                            @if ($mark['average'] !== null)
                                @php $overallGrade = Grading::gradeTotal($mark['total']); @endphp
                                <td class="border border-black text-center {{ $gradeColorMap[$overallGrade] ?? '' }}">{{ $overallGrade }}
                                </td>
                            @else
                                <td class="border border-black text-center {{ $gradeColorMap['ABS'] }}">ABS</td>
                            @endif

                            @php
                                if ($mark['average'] !== null && $storedAvg === $mark['total']) {
                                    $j++;
                                    $storedAvg = $mark['total'];
                                    $overallPosition = $i - $j;
                                } else {
                                    $j = 0;
                                    $storedAvg = $mark['total'];
                                    $overallPosition = $i;
                                }
                            @endphp
                            <td class="border border-black text-center">{{ $overallPosition }}</td>
                            @if ($mark['average'] !== null)
                                @php
                                    $status = Grading::statusForTotal($mark['total'], $classId);
                                    $statusColor = $status === 'FAULU' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
                                @endphp
                                <td class="border border-black text-center {{ $statusColor }}">
                                    {{ $status }}</td>
                            @else
                                <td class="border border-black text-center"></td>
                            @endif
                        </tr>
                        @php $i++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>



        <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-2 mt-5">
            <div>
                <table class="w-full text-xs">
                    <thead>
                        <tr>
                            <th class="border border-black p-1 uppercase">Wastani Wa Shule</th>
                            <th class="border border-black p-1 uppercase">Daraja</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white">
                            <td class="border border-black p-1 text-center">
                                @php
                                    $gATotal = 0;
                                    foreach ($gAverage as $gA) {
                                        $gATotal += $gA;
                                    }

                                    $gAver =
                                        count($marks) > 0 &&
                                        count($subjects) * (count($marks) - $maleAbsent - $femaleAbsent) > 0
                                            ? $gATotal /
                                                (count($subjects) * (count($marks) - $maleAbsent - $femaleAbsent))
                                            : 0;
                                    $meanTotal2 =
                                        count($marks) - $maleAbsent - $femaleAbsent > 0
                                            ? $gATotal / (count($marks) - $maleAbsent - $femaleAbsent)
                                            : 0;
                                    $schoolGrade = Grading::gradeTotal($meanTotal2);
                                @endphp

                                {{ number_format($gAver, 2) }}
                            </td>
                            <td class="border border-black p-1 text-center {{ $gradeColorMap[$schoolGrade] ?? '' }}">{{ $schoolGrade }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <table class="w-full text-xs">
                    <thead>
                        <tr>
                            <th class="border border-black p-1 uppercase">Wastani Wa Ufaulu</th>
                            <th class="border border-black p-1 uppercase">Daraja</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white">
                            <td class="border border-black p-1 text-center">
                                @php
                                    $gATotal = 0;
                                    foreach ($gAverage as $gA) {
                                        $gATotal += $gA;
                                    }

                                    $gAver =
                                        count($marks) && count($marks) - $maleAbsent - $femaleAbsent > 0
                                            ? $gATotal / (count($marks) - $maleAbsent - $femaleAbsent)
                                            : 0;
                                @endphp

                                {{ number_format($gAver, 2) }}
                            </td>
                            @php $ufaruluGrade = Grading::gradeTotal($gAver); @endphp
                            <td class="border border-black p-1 text-center {{ $gradeColorMap[$ufaruluGrade] ?? '' }}">{{ $ufaruluGrade }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-2 mt-5">
            <div>
                <table class="w-full text-xs">
                    <tr>
                        <th rowspan="2" class="text-center border border-black">TATHIMINI YA UFAULU</th>
                        <th colspan="7" class="text-center border border-black uppercase">Daraja</th>
                    </tr>

                    <tr>
                        <th class="border border-black {{ $gradeColorMap['A'] }}">A</th>
                        <th class="border border-black {{ $gradeColorMap['B'] }}">B</th>
                        <th class="border border-black {{ $gradeColorMap['C'] }}">C</th>
                        <th class="border border-black {{ $gradeColorMap['D'] }}">D</th>
                        <th class="border border-black {{ $gradeColorMap['E'] }}">E</th>
                        <th class="border border-black {{ $gradeColorMap['ABS'] }}">ABS</th>
                        <th class="border border-black uppercase">Jumla</th>
                    </tr>

                    {{-- @php
                        if ($classId > 4) {
                            $failCount = $dmCount + $emCount + $dfCount + $efCount;
                            $failMaleCount = $dmCount + $emCount;
                            $failFemaleCount = $dfCount + $efCount;
                        } else {
                            $failCount = $emCount + $efCount;
                            $failMaleCount = $emCount;
                            $failFemaleCount = $efCount;
                        }

                        $gradeCount =
                            $amCount +
                            $bmCount +
                            $cmCount +
                            $dmCount +
                            $emCount +
                            $afCount +
                            $bfCount +
                            $cfCount +
                            $dfCount +
                            $efCount;
                        $gradeMaleCount = $amCount + $bmCount + $cmCount + $dmCount + $emCount;
                        $gradeFemaleCount = $afCount + $bfCount + $cfCount + $dfCount + $efCount;
                    @endphp --}}

                    <tr class="bg-white">
                        <td class="border border-black text-center">Wav</td>
                        <td class="border border-black text-center">{{ $amCount }}</td>
                        <td class="border border-black text-center">{{ $bmCount }}</td>
                        <td class="border border-black text-center">{{ $cmCount }}</td>
                        <td class="border border-black text-center">{{ $dmCount }}</td>
                        <td class="border border-black text-center">{{ $emCount }}</td>
                        <td class="border border-black text-center">{{ $maleAbsent }}</td>
                        <td class="border border-black text-center">{{ $gradeMaleCount + $maleAbsent }}</td>
                    </tr>

                    <tr class="bg-gray-200">
                        <td class="border border-black text-center">Was</td>
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
                            {{ $gradeMaleCount + $gradeFemaleCount + $maleAbsent + $femaleAbsent }}</td>
                    </tr>
                </table>
            </div>

            <div>
                <table class="w-full text-xs">
                    <thead>
                        <tr>
                            <th colspan="2" class="border border-black px-2 text-center">WALIOFANYA MTIHANI</th>
                            <th class="border border-black px-2 text-center uppercase bg-green-100 text-green-800">Waliofaulu</th>
                            <th class="border border-black px-2 text-center uppercase bg-red-100 text-red-800">Waliofeli</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white text-center">
                            <td class="border border-black px-2">Wav</td>
                            <td class="border border-black px-2">{{ $gradeMaleCount }}</td>
                            <td class="border border-black px-2">{{ $gradeMaleCount - $failMaleCount }}</td>
                            <td class="border border-black px-2">{{ $failMaleCount }}</td>
                        </tr>

                        <tr class="bg-gray-200 text-center">
                            <td class="border border-black px-2">Was</td>
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
                            @php
                                $passTitle = $classId > 4 ? '% Pass(A-C)' : '% Pass(A-D)';
                                $failTitle = $classId > 4 ? '% Fail(D-E)' : '% Fail(E)';
                                $waliofanyaPassPct = $gradeCount > 0 ? (($gradeCount - $failCount) * 100) / $gradeCount : 0;
                            @endphp
                            <td class="border border-black px-2 font-semibold {{ $passPctColor($waliofanyaPassPct) }}">
                                @if ($gradeCount > 0)
                                    <span>{{ $passTitle }}:</span>
                                    {{ number_format($waliofanyaPassPct, 2) }}
                                @else
                                    <p>{{ $passTitle }}: 0</p>
                                @endif
                            </td>

                            <td class="border border-black px-2 font-semibold {{ $passPctColor($waliofanyaPassPct) }}">
                                @if ($gradeCount > 0)
                                    <span>{{ $failTitle }}:</span>
                                    {{ number_format(($failCount * 100) / $gradeCount, 2) }}
                                @else
                                    <p>{{ $failTitle }}: 0</p>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{-- @php
                $gradeArray = array_fill_keys($subjects, ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0]);
                $gradeMaleArray = array_fill_keys($subjects, ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0]);
                $gradeFemaleArray = array_fill_keys($subjects, ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0]);

                $failedCount = 0;
                $subList = $subjects;
                @endphp

                @foreach ($allMarks as $aMark)
                @php
                if ($aMark['total'] != 0) {
                foreach ($subList as $list) {
                $grade = assignGrade($aMark[$list], $ranks);
                if ($grade != 'Null') {
                if ($aMark['gender'] == 'M') {
                $gradeMaleArray[$list][$grade]++;
                } else {
                $gradeFemaleArray[$list][$grade]++;
                }
                $gradeArray[$list][$grade]++;
                }
                }
                }
                @endphp
                @endforeach --}}

            <h2 class="text-2xl font-bold mb-2 text-center">TATHIMINI YA MADARAJA YA KILA SOMO</h2>

            <table class="w-full text-xs">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center border border-black uppercase">Somo</th>
                        <th colspan="3" class="text-center border border-black {{ $gradeColorMap['A'] }}">A</th>
                        <th colspan="3" class="text-center border border-black {{ $gradeColorMap['B'] }}">B</th>
                        <th colspan="3" class="text-center border border-black {{ $gradeColorMap['C'] }}">C</th>
                        <th colspan="3" class="text-center border border-black {{ $gradeColorMap['D'] }}">D</th>
                        <th colspan="3" class="text-center border border-black {{ $gradeColorMap['E'] }}">E</th>
                        <th rowspan="2" class="text-center border border-black uppercase">Wastani Wa Somo</th>
                        <th rowspan="2" class="text-center border border-black uppercase">Walio Faulu</th>
                        <th rowspan="2" class="text-center border border-black">%</th>
                        <th rowspan="2" class="text-center border border-black uppercase">Wasio Faulu</th>
                        <th rowspan="2" class="text-center border border-black">%</th>
                    </tr>

                    <tr>
                        <th class="text-center border border-black">Wav</th>
                        <th class="text-center border border-black">Was</th>
                        <th class="text-center border border-black">JML</th>
                        <th class="text-center border border-black">Wav</th>
                        <th class="text-center border border-black">Was</th>
                        <th class="text-center border border-black">JML</th>
                        <th class="text-center border border-black">Wav</th>
                        <th class="text-center border border-black">Was</th>
                        <th class="text-center border border-black">JML</th>
                        <th class="text-center border border-black">Wav</th>
                        <th class="text-center border border-black">Was</th>
                        <th class="text-center border border-black">JML</th>
                        <th class="text-center border border-black">Wav</th>
                        <th class="text-center border border-black">Was</th>
                        <th class="text-center border border-black">JML</th>
                    </tr>
                </thead>

                <tbody>
                    @if (count($subjects) > 0)
                        @php
                            $g = 0;
                        @endphp

                        @foreach ($subjects as $name)
                            @php
                                $rowColor = $g % 2 == 0 ? 'bg-white' : 'bg-gray-200';
                                $totalStudents = count($marks) - $maleAbsent - $femaleAbsent;
                                $subjectAverage =
                                    $totalStudents > 0 ? number_format($gAverage[$g] / $totalStudents, 2) : 0;

                                $totalGradeCount = array_sum($gradeArray[$name]);
                                if ($classId > 4) {
                                    $failedCount = $gradeArray[$name]['D'] + $gradeArray[$name]['E'];
                                } else {
                                    $failedCount = $gradeArray[$name]['E'];
                                }
                            @endphp

                            <tr class="{{ $rowColor }}">
                                <td class="pl-2 border border-black capitalize">{{ $name }}</td>
                                @foreach (['A', 'B', 'C', 'D', 'E'] as $grade)
                                    <td class="text-center border border-black px-2">{{ $gradeMaleArray[$name][$grade] }}
                                    </td>
                                    <td class="text-center border border-black px-2">
                                        {{ $gradeFemaleArray[$name][$grade] }}</td>
                                    <td class="text-center border border-black px-2">{{ $gradeArray[$name][$grade] }}</td>
                                @endforeach
                                @if (count($marks) > 0 && count($marks) - $maleAbsent - $femaleAbsent)
                                    <td class="text-center border border-black">
                                        {{ number_format($gAverage[$g] / (count($marks) - $maleAbsent - $femaleAbsent), 2) }}
                                    </td>
                                @else
                                    <td class="text-center border border-black">0</td>
                                @endif
                                {{-- <td class="text-center border border-black">
                                {{ $subjectAverage }}
                            </td> --}}
                                @php
                                    $subjectPassPct = $totalGradeCount > 0 ? (($totalGradeCount - $failedCount) * 100) / $totalGradeCount : 0;
                                @endphp
                                <td class="text-center border border-black">{{ $totalGradeCount - $failedCount }}</td>
                                <td class="text-center border border-black font-semibold {{ $passPctColor($subjectPassPct) }}">
                                    @if ($totalGradeCount > 0)
                                        {{ number_format($subjectPassPct, 2) }}
                                    @else
                                        <p>0</p>
                                    @endif
                                </td>
                                <td class="text-center border border-black">{{ $failedCount }}</td>
                                <td class="text-center border border-black font-semibold {{ $passPctColor($subjectPassPct) }}">
                                    @if ($totalGradeCount > 0)
                                        {{ number_format(($failedCount * 100) / $totalGradeCount, 2) }}
                                    @else
                                        <p>0</p>
                                    @endif
                                </td>
                            </tr>

                            @php
                                $g++;
                            @endphp
                            <!-- Add this modal after the form -->
                            <div id="dateModal"
                                class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
                                <div class="bg-white rounded-lg p-6">
                                    <h2 class="text-lg font-bold mb-4">Enter Dates</h2>
                                    <div class="mb-4">
                                        <label class="block text-gray-700">Tarehe ya Kufungua (Opening Date)</label>
                                        <input type="date" id="modalOpeningDate"
                                            class="mt-1 block w-full border-gray-300 rounded-md">
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700">Tarehe ya Kufunga (Closing Date)</label>
                                        <input type="date" id="modalClosingDate"
                                            class="mt-1 block w-full border-gray-300 rounded-md">
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="button" onclick="confirmDates()"
                                            class="bg-cyan-500 hover:bg-cyan-600 text-white py-1 px-2 rounded-md">Thibitisha</button>
                                        <button type="button" onclick="closeModal()"
                                            class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 py-1 px-2 rounded-md">Funga</button>
                                    </div>
                                </div>
                            </div>

                            <div id="dateModalEnglish"
                                class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
                                <div class="bg-white rounded-lg p-6">
                                    <h2 class="text-lg font-bold mb-4">Enter Dates (English)</h2>
                                    <div class="mb-4">
                                        <label class="block text-gray-700">Opening Date</label>
                                        <input type="date" id="modalOpeningDateEnglish"
                                            class="mt-1 block w-full border-gray-300 rounded-md">
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700">Closing Date</label>
                                        <input type="date" id="modalClosingDateEnglish"
                                            class="mt-1 block w-full border-gray-300 rounded-md">
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="button" onclick="confirmDatesEnglish()"
                                            class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md">Confirm</button>
                                        <button type="button" onclick="closeModalEnglish()"
                                            class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 py-1 px-2 rounded-md">Close</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <tr>
                            <td class="text-red-500 text-center p-2" colspan="21">No Data Found!</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
    @if ($errors->any())
        <script>
            // Collect errors and format them into a string to display in the toast
            const errors = @json($errors->all());
            const errorMessage = errors.join('<br>');

            // Show the toast container and message
            const toastContainer = document.getElementById('toast-container');
            const toastMessage = document.getElementById('toast-message');
            const toastText = document.getElementById('toast-text');

            toastText.innerHTML = errorMessage;
            toastMessage.classList.remove('hidden');

            // Show the toast with a fade-in effect
            setTimeout(() => {
                toastMessage.classList.add('opacity-100');
                toastMessage.classList.remove('opacity-0');
            }, 100);

            // Hide the toast after 5 seconds
            setTimeout(() => {
                toastMessage.classList.remove('opacity-100');
                toastMessage.classList.add('opacity-0');
            }, 5000);
        </script>
    @endif

    <script>
        // ================= Select All for student checkboxes =================
        const selectAll = document.getElementById('selectAll');
        const printReportButton = document.querySelector('#printReportForm button');
        const printEnglishButton = document.querySelector('#printReportFormEnglish button');

        function getStudentCheckboxes() {
            return Array.from(document.querySelectorAll('.studentCheckbox'));
        }

        function updatePrintButtons() {
            const checkboxes = getStudentCheckboxes();
            const selectedCount = checkboxes.filter(checkbox => checkbox.checked).length;

            if (printReportButton) printReportButton.disabled = selectedCount === 0;
            if (printEnglishButton) printEnglishButton.disabled = selectedCount === 0;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                let checkboxes = getStudentCheckboxes();
                checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                updatePrintButtons();
            });
        }

        // ================= Kiswahili Modal Functions =================
        function openModal() {
            document.getElementById('dateModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('dateModal').classList.add('hidden');
        }

        function confirmDates() {
            const openingDate = document.getElementById('modalOpeningDate').value;
            const closingDate = document.getElementById('modalClosingDate').value;
            if (!openingDate || !closingDate) {
                alert('Please fill both dates.');
                return;
            }
            document.getElementById('openingDate').value = openingDate;
            document.getElementById('closingDate').value = closingDate;
            closeModal();
            submitPrintReportForm();
        }

        function submitPrintReportForm() {
            let selectedStudents = [];
            let checkboxes = document.querySelectorAll('.studentCheckbox:checked');
            checkboxes.forEach(checkbox => selectedStudents.push(JSON.parse(checkbox.value)));

            document.getElementById('selectedStudents').value = JSON.stringify(selectedStudents);
            document.getElementById('printReportForm').submit();
        }

        updatePrintButtons();
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList && e.target.classList.contains('studentCheckbox')) {
                updatePrintButtons();
            }
        });

        // ================= English Modal & Button Functions =================
        function openModalEnglish() {
            document.getElementById('dateModalEnglish').classList.remove('hidden');
        }

        function closeModalEnglish() {
            document.getElementById('dateModalEnglish').classList.add('hidden');
        }

        function confirmDatesEnglish() {
            const openingDate = document.getElementById('modalOpeningDateEnglish').value;
            const closingDate = document.getElementById('modalClosingDateEnglish').value;
            if (!openingDate || !closingDate) {
                alert('Please fill both dates.');
                return;
            }
            document.getElementById('openingDateEnglish').value = openingDate;
            document.getElementById('closingDateEnglish').value = closingDate;
            closeModalEnglish();
            submitPrintReportFormEnglish();
        }

        function submitPrintReportFormEnglish() {
            let selectedStudents = [];
            let checkboxes = document.querySelectorAll('.studentCheckbox:checked');
            checkboxes.forEach(checkbox => selectedStudents.push(JSON.parse(checkbox.value)));

            if (selectedStudents.length === 0) {
                alert('Please select at least one student.');
                return;
            }

            document.getElementById('selectedStudentsEnglish').value = JSON.stringify(selectedStudents);
            document.getElementById('printReportFormEnglish').submit();
        }

        updatePrintButtons();
    </script>


@endsection
