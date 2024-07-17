@extends('admin.layout')

@section('content')
    @php
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

        $ranks = \App\Models\Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')
            ->where([['isActive', '=', '1'], ['isDeleted', '=', '0']])
            ->orderBy('rankName', 'asc')
            ->get()
            ->toArray();
    @endphp

    <div class="p-3">
        <div class="flex justify-end">
            <form action="{{ url('/downloadTeacherReport') }}" method="post">
                @csrf

                <input type="hidden" name="rClass" id="rClass" value="{{ $classId }}">
                <input type="hidden" name="rExam" id="rExam" value="{{ $examId }}">
                <input type="hidden" name="rStartDate" id="rStartDate" value="{{ $startDate }}">
                <input type="hidden" name="rEndDate" id="rEndDate" value="{{ $endDate }}">

                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded-md mr-1">
                    <i class="material-symbols-outlined text-sm">download</i> <span>Pakua Matokeo</span>
                </button>
            </form>
        </div>

        <div class="my-3">
            <h2 class="text-2xl font-bold">Kichujio:</h2>

            <form action="{{ url('/filterUserReport') }}" method="post" id="filterForm">
                @csrf

                <div class="grid lg:grid-cols-4 md:grid-cols-4 grid-cols-1 gap-2">
                    <div>
                        <label for="class">Chagua Darasa:<span class="text-red-500">*</span></label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="class" id="class"
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
                        <select class="block w-full block p-2 rounded-md border border-black" name="exam" id="exam">
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
                        <input type="date" class="block w-full block p-2 rounded-md border border-black"
                            min="{{ date('Y-m-d', strtotime('2023-01-01')) }}" max="{{ date('Y-m-d') }}" name="startDate"
                            id="startDate" placeholder="Enter Start Date"
                            value="{{ date('Y-m-d', strtotime($startDate)) }}" onchange="setEndDate()">
                    </div>

                    <div>
                        <label for="endDate">Tarehe ya Mwisho:</label>
                        <input type="date" class="block w-full block p-2 rounded-md border border-black"
                            min="{{ date('Y-m-d', strtotime('2023-01-01')) }}" max="{{ date('Y-m-d') }}" name="endDate"
                            id="endDate" placeholder="Enter End Date" value="{{ date('Y-m-d', strtotime($endDate)) }}">
                    </div>
                </div>
            </form>

            <div class="flex justify-end">
                <a href="{{ url('/dashboard/reports') }}"><button type="button" form="filterForm"
                        class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">Onesha
                        Upya</button></a>
                <button type="submit" form="filterForm"
                    class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">Kichujio</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <h2 class="text-2xl font-bold mb-2">MATOKEO KWA MPANGILIO WA WANAFUNZI WOTE:</h2>
            <table class="myTable bg-white">
                <thead>
                    <tr>
                        <th rowspan="2" class="border border-black">S/N</th>
                        <th rowspan="2" class="border border-black uppercase">Jina la Mwanafunzi</th>
                        @foreach ($subjects as $subject)
                            <th colspan="2" class="border border-black uppercase">{{ $subject }}</th>
                        @endforeach
                        <th rowspan="2" class="border border-black uppercase">Jumla</th>
                        <th rowspan="2" class="border border-black uppercase">Wastani</th>
                        <th rowspan="2" class="border border-black uppercase">Daraja</th>
                        <th rowspan="2" class="border border-black uppercase">Nafasi</th>
                        <th rowspan="2" class="border border-black uppercase">Ufaulu</th>
                    </tr>

                    <tr>
                        @foreach ($subjects as $subject)
                            <th class="border border-black">AL</th>
                            <th class="border border-black">DRJ</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @php
                        $i = 1;
                        $j = 0;
                        $storedAvg = '';
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

                        $gAverage = array_fill(0, count($subjects), 0);
                    @endphp
                    @foreach ($marks as $mark)
                        @php
                            if ($mark['average'] == 0) {
                                $mark['gender'] == 'M' ? $maleAbsent++ : $femaleAbsent++;
                            } else {
                                foreach ($subjects as $index => $subject) {
                                    $gAverage[$index] += $mark[$subject];
                                }

                                if (assignGrade($mark['average'], $ranks) == 'A') {
                                    $mark['gender'] == 'M' ? $amCount++ : $afCount++;
                                } elseif (assignGrade($mark['average'], $ranks) == 'B') {
                                    $mark['gender'] == 'M' ? $bmCount++ : $bfCount++;
                                } elseif (assignGrade($mark['average'], $ranks) == 'C') {
                                    $mark['gender'] == 'M' ? $cmCount++ : $cfCount++;
                                } elseif (assignGrade($mark['average'], $ranks) == 'D') {
                                    $mark['gender'] == 'M' ? $dmCount++ : $dfCount++;
                                } else {
                                    $mark['gender'] == 'M' ? $emCount++ : $efCount++;
                                }
                            }
                        @endphp

                        <tr class="odd:bg-gray-200">
                            <td class="border border-black text-right">{{ $i }}</td>
                            <td class="capitalize border border-black">{{ $mark['studentName'] }}</td>
                            @foreach ($subjects as $subject)
                                <td class="border border-black text-right">{{ $mark[$subject] }}</td>
                                <td class="border border-black">{{ assignGrade($mark[$subject], $ranks) }}</td>
                            @endforeach
                            <td class="border border-black text-right">{{ $mark['total'] }}</td>
                            <td class="border border-black text-right">{{ $mark['average'] }}</td>

                            @if ($mark['average'] > 0)
                                <td class="border border-black">{{ assignGrade($mark['average'], $ranks) }}</td>
                            @else
                                <td class="border border-black">ABS</td>
                            @endif

                            @if ($storedAvg == $mark['average'])
                                @php
                                    $j++;
                                    $storedAvg = $mark['average'];
                                @endphp

                                <td class="border border-black text-right">{{ $i - $j }}</td>
                            @else
                                @php
                                    $j = 0;
                                    $storedAvg = $mark['average'];
                                @endphp

                                <td class="border border-black text-right">{{ $i }}</td>
                            @endif

                            @if ($mark['average'] > 0)
                                <td class="border border-black">{{ finalStatus($mark['average'], $ranks, $classId) }}</td>
                            @else
                                <td class="border border-black"></td>
                            @endif
                        </tr>

                        @php
                            $i++;
                        @endphp
                    @endforeach
                </tbody>
            </table>
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
                            <td class="border border-black p-1 text-center">
                                @php
                                    $gATotal = 0;
                                    foreach ($gAverage as $gA) {
                                        $gATotal += $gA;
                                    }

                                    $gAver =
                                        count($marks) > 0
                                            ? $gATotal /
                                                (count($subjects) * (count($marks) - $maleAbsent - $femaleAbsent))
                                            : 0;
                                    $schoolGrade = assignGrade($gAver, $ranks);
                                @endphp

                                {{ number_format($gAver, 2) }}
                            </td>
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
                            <td class="border border-black p-1 text-center">
                                @php
                                    $gATotal = 0;
                                    foreach ($gAverage as $gA) {
                                        $gATotal += $gA;
                                    }

                                    $gAver =
                                        count($marks) > 0
                                            ? $gATotal / (count($marks) - $maleAbsent - $femaleAbsent)
                                            : 0;
                                @endphp

                                {{ number_format($gAver, 2) }}
                            </td>
                            <td class="border border-black p-1 text-center">{{ $schoolGrade }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

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

                    @php
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
                    @endphp

                    <tr class="bg-white">
                        <td class="border border-black text-center">1</td>
                        <td class="border border-black text-center">{{ $amCount }}</td>
                        <td class="border border-black text-center">{{ $bmCount }}</td>
                        <td class="border border-black text-center">{{ $cmCount }}</td>
                        <td class="border border-black text-center">{{ $dmCount }}</td>
                        <td class="border border-black text-center">{{ $emCount }}</td>
                        <td class="border border-black text-center">{{ $maleAbsent }}</td>
                        <td class="border border-black text-center">{{ $gradeMaleCount + $maleAbsent }}</td>
                    </tr>

                    <tr class="bg-gray-200">
                        <td class="border border-black text-center">2</td>
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
                            <td class="border border-black px-2">1</td>
                            <td class="border border-black px-2">{{ $gradeMaleCount }}</td>
                            <td class="border border-black px-2">{{ $gradeMaleCount - $failMaleCount }}</td>
                            <td class="border border-black px-2">{{ $failMaleCount }}</td>
                        </tr>

                        <tr class="bg-gray-200 text-center">
                            <td class="border border-black px-2">2</td>
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
                                @php
                                    $passTitle = $classId > 4 ? '% Pass(A-C)' : '% Pass(A-D)';
                                @endphp

                                @if ($gradeCount > 0)
                                    <span>{{ $passTitle }}:</span>
                                    {{ number_format((($gradeCount - $failCount) * 100) / $gradeCount, 2) }}
                                @else
                                    <p>{{ $passTitle }}: 0</p>
                                @endif
                            </td>

                            <td class="border border-black px-2">
                                @php
                                    $failTitle = $classId > 4 ? '% Fail(D-E)' : '% Fail(E)';
                                @endphp

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
            @php
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
            @endforeach

            <h2 class="text-2xl font-bold mb-2 text-center">TATHIMINI YA MADARAJA YA KILA SOMO</h2>

            <table class="w-full">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center border border-black uppercase">Somo</th>
                        <th colspan="3" class="text-center border border-black">A</th>
                        <th colspan="3" class="text-center border border-black">B</th>
                        <th colspan="3" class="text-center border border-black">C</th>
                        <th colspan="3" class="text-center border border-black">D</th>
                        <th colspan="3" class="text-center border border-black">E</th>
                        <th rowspan="2" class="text-center border border-black uppercase">Wastani Ya Somo</th>
                        <th rowspan="2" class="text-center border border-black uppercase">Walio Faulu</th>
                        <th rowspan="2" class="text-center border border-black">%</th>
                        <th rowspan="2" class="text-center border border-black uppercase">Wasio Faulu</th>
                        <th rowspan="2" class="text-center border border-black">%</th>
                    </tr>

                    <tr>
                        <th class="text-center border border-black">1</th>
                        <th class="text-center border border-black">2</th>
                        <th class="text-center border border-black">JML</th>
                        <th class="text-center border border-black">1</th>
                        <th class="text-center border border-black">2</th>
                        <th class="text-center border border-black">JML</th>
                        <th class="text-center border border-black">1</th>
                        <th class="text-center border border-black">2</th>
                        <th class="text-center border border-black">JML</th>
                        <th class="text-center border border-black">1</th>
                        <th class="text-center border border-black">2</th>
                        <th class="text-center border border-black">JML</th>
                        <th class="text-center border border-black">1</th>
                        <th class="text-center border border-black">2</th>
                        <th class="text-center border border-black">JML</th>
                    </tr>
                </thead>

                <tbody>
                    @if (count($subList) > 0)
                        @php
                            $g = 0;
                        @endphp

                        @foreach ($subList as $name)
                            @php
                                $rowColor = $g % 2 == 0 ? 'bg-white' : 'bg-gray-200';

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
                                    <td class="text-center border border-black px-2">{{ $gradeMaleArray[$name][$grade] }}</td>
                                    <td class="text-center border border-black px-2">{{ $gradeFemaleArray[$name][$grade] }}</td>
                                    <td class="text-center border border-black px-2">{{ $gradeArray[$name][$grade] }}</td>
                                @endforeach
                                @if (count($marks) > 0)
                                    <td class="text-center border border-black">
                                        {{ number_format($gAverage[$g] / (count($marks) - $maleAbsent - $femaleAbsent), 2) }}
                                    </td>
                                @else
                                    <td class="text-center border border-black">0</td>
                                @endif
                                <td class="text-center border border-black">{{ $totalGradeCount - $failedCount }}</td>
                                <td class="text-center border border-black">
                                    @if ($totalGradeCount > 0)
                                        {{ number_format((($totalGradeCount - $failedCount) * 100) / $totalGradeCount, 2) }}
                                    @else
                                        <p>0</p>
                                    @endif
                                </td>
                                <td class="text-center border border-black">{{ $failedCount }}</td>
                                <td class="text-center border border-black">
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
@endsection
