@extends('admin.layout')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<style>
    .progress-bar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: none;
    }

    .progress {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 50px;
        height: 5px;
        background-color: #fff;
    }

    .progress-bar-inner {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background-color: #337ab7;
        width: 0;
        transition: width 0.5s;
    }
</style>

@section('content')
    @php

        // function assignGrade($marks)
        // {
        //     $ranks = \App\Models\Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')
        //         ->where([['isActive', '=', '1'], ['isDeleted', '=', '0']])
        //         ->orderBy('rankName', 'asc')
        //         ->get();

        //     if ($marks == 10) {
        //         return 'E';
        //     }
        //     foreach ($ranks as $rank) {
        //         if ($rank['rankRangeMin'] < $marks && $rank['rankRangeMax'] >= $marks) {
        //             return $rank['rankName'];
        //         }
        //     }

        //     return 'Null';
        // }
        function assignGrade($mark)
        {
            $gradeBoundaries = [
                'A' => [41, 50],
                'B' => [31, 40],
                'C' => [21, 30],
                'D' => [11, 20],
                'E' => [0, 10],
            ];
            foreach ($gradeBoundaries as $grade => $range) {
                if ($mark >= $range[0] && $mark <= $range[1]) {
                    return $grade;
                }
            }
            return 'Null';
        }

        if ($classId > 4) {
            function finalStatus($average)
            {
                $rank = \App\Models\Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')
                    ->where([['isActive', '=', '1'], ['isDeleted', '=', '0']])
                    ->orderBy('rankName', 'asc')
                    ->get();

                if ($average <= $rank[3]['rankRangeMax']) {
                    return 'FAIL';
                } else {
                    return 'PASS';
                }
            }
        } else {
            function finalStatus($average)
            {
                $rank = \App\Models\Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')
                    ->where([['isActive', '=', '1'], ['isDeleted', '=', '0']])
                    ->orderBy('rankName', 'asc')
                    ->get();

                if ($average <= $rank[4]['rankRangeMax']) {
                    return 'FAIL';
                } else {
                    return 'PASS';
                }
            }
        }

        // Calculate total students who took the exam
        $totalStudentsTookExam =
            count($allMarks) - $gradeDistribution['male']['ABS'] - $gradeDistribution['female']['ABS'];
    @endphp

    <div class="p-3">
        <div class="flex justify-end">
            <form action="{{ url('/downloadStudentData') }}" method="post">
                @csrf

                <input type="hidden" name="rClass" id="rClass" value="{{ $classId }}">
                <input type="hidden" name="rExam" id="rExam" value="{{ $examId }}">
                <input type="hidden" name="rRegion" id="rRegion" value="{{ $regionId }}">
                <input type="hidden" name="rDistrict" id="rDistrict" value="{{ $districtId }}">
                <input type="hidden" name="rWard" id="rWard" value="{{ $wardId }}">
                <input type="hidden" name="rStartDate" id="rStartDate" value="{{ $startDate }}">
                <input type="hidden" name="rEndDate" id="rEndDate" value="{{ $endDate }}">

                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded-md mr-1">
                    <i class="material-symbols-outlined text-sm">download</i> <span>Pakua Kiolezo</span>
                </button>
            </form>
        </div>

        <div class="my-3">
            <h2 class="text-2xl font-bold">Kichujio:</h2>

            <form action="{{ url('/filterStudentData') }}" id="filterForm">
                @csrf

                <div class="grid lg:grid-cols-7 md:grid-cols-4 grid-cols-1 gap-2">
                    <div>
                        <label for="class">Darasa:<span class="text-red-500">*</span></label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="class" id="class"
                            required>
                            <option value="">-- SELECT CLASS --</option>
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
                        <label for="exam">Mtihani:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="exam" id="exam">
                            <option value="">-- SELECT EXAM --</option>
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
                        <label for="region">Mkoa:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="region" id="region">
                            <option value="">-- SELECT REGION --</option>
                            @if (count($regions) > 0)
                                @foreach ($regions as $region)
                                    <option value="{{ $region['regionId'] }}" @selected($regionId == $region['regionId'])>
                                        {{ $region['regionName'] }} ({{ $region['regionCode'] }})</option>
                                @endforeach
                            @else
                                <option value="" class="text-red-500">No Data Found!</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="district">Wilaya:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="district"
                            id="district">
                            <option value="">-- SELECT DISTRICT --</option>
                            @if (count($districts) > 0)
                                @foreach ($districts as $district)
                                    <option value="{{ $district['districtId'] }}" @selected($districtId == $district['districtId'])>
                                        {{ $district['districtName'] }} ({{ $district['districtCode'] }})</option>
                                @endforeach
                            @else
                                <option value="" class="text-red-500">No Data Found!</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="ward">Kata:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="ward" id="ward">
                            <option value="">-- SELECT WARD --</option>
                            @if (count($wards) > 0)
                                @foreach ($wards as $ward)
                                    <option value="{{ $ward['wardId'] }}" @selected($wardId == $ward['wardId'])>
                                        {{ $ward['wardName'] }} ({{ $ward['wardCode'] }})</option>
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

                <div class="flex justify-end">
                    <a href="{{ url('/admin-dashboard/student-data') }}"><button type="button" form="filterForm"
                            class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">Onesha
                            Upya</button></a>
                    <button type="submit" form="filterForm"
                        class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">Kichujio</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <h2 class="text-2xl font-bold mb-2">MATOKEO KWA MPANGILIO KWA WANAFUNZI WOTE:</h2>

            <table class="bg-white">
                <thead>
                    <tr>
                        <th rowspan="2" class="border border-black p-[15px]">S/N</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Jinala Mwanafunzi</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Darasa</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Mtihani</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Shule</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Mkoa</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Wilaya</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Kata</th>
                        @foreach ($subjects as $subject)
                            <th colspan="2" class="border border-black p-[15px] uppercase">{{ $subject }}</th>
                        @endforeach
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Jumla</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Wastani</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Daraja</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Nafasi</th>
                        <th rowspan="2" class="border border-black p-[15px] uppercase">Ufaulu</th>
                    </tr>
                    <tr>
                        @foreach ($subjects as $subject)
                            <th class="border border-black p-[15px]">AL</th>
                            <th class="border border-black p-[15px]">DRJ</th>
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
                            <td class="p-[15px] border border-black text-right">{{ $i }}</td>
                            <td class="p-[15px] capitalize border border-black">{{ $mark['studentName'] }}</td>
                            <td class="p-[15px] capitalize border border-black">
                                @php
                                    $gradeData = \App\Models\Grades::select('gradeName')
                                        ->where([['gradeId', '=', $mark['classId']]])
                                        ->first();

                                    $gradeName = $gradeData
                                        ? $gradeData['gradeName']
                                        : '<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $gradeName !!}</p>
                            </td>
                            <td class="p-[15px] capitalize border border-black">
                                @php
                                    $examData = \App\Models\Exams::select('examName')
                                        ->where([['examId', '=', $mark['examId']]])
                                        ->first();

                                    $examName = $examData
                                        ? $examData['examName']
                                        : '<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $examName !!}</p>
                            </td>
                            <td class="p-[15px] capitalize border border-black">
                                @php
                                    $schoolData = \App\Models\Schools::select('schoolName')
                                        ->where([['schoolId', '=', $mark['schoolId']]])
                                        ->first();

                                    $schoolName = $schoolData
                                        ? $schoolData['schoolName']
                                        : '<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $schoolName !!}</p>
                            </td>
                            <td class="p-[15px] capitalize border border-black">
                                @php
                                    $regionData = \App\Models\Regions::select('regionName')
                                        ->where([['regionId', '=', $mark['regionId']]])
                                        ->first();

                                    $regionName = $regionData
                                        ? $regionData['regionName']
                                        : '<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $regionName !!}</p>
                            </td>
                            <td class="p-[15px] capitalize border border-black">
                                @php
                                    $districtData = \App\Models\Districts::select('districtName')
                                        ->where([['districtId', '=', $mark['districtId']]])
                                        ->first();

                                    $districtName = $districtData
                                        ? $districtData['districtName']
                                        : '<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $districtName !!}</p>
                            </td>
                            <td class="p-[15px] capitalize border border-black">
                                @php
                                    $wardData = \App\Models\Wards::select('wardName')
                                        ->where([['wardId', '=', $mark['wardId']]])
                                        ->first();

                                    $wardName = $wardData
                                        ? $wardData['wardName']
                                        : '<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $wardName !!}</p>
                            </td>
                            @foreach ($subjects as $subject)
                                <td class="p-[15px] border border-black text-right">{{ $mark[$subject] }}</td>
                                <td class="p-[15px] border border-black">{{ assignGrade($mark[$subject]) }}</td>
                            @endforeach
                            <td class="p-[15px] border border-black text-right">{{ $mark['total'] }}</td>
                            <td class="p-[15px] border border-black text-right">{{ $mark['average'] }}</td>

                            @if ($mark['average'] > 0)
                                <td class="p-[15px] border border-black">{{ assignGrade($mark['average']) }}</td>
                            @else
                                <td class="p-[15px] border border-black">ABS</td>
                            @endif

                            @if ($storedAvg == $mark['average'])
                                @php
                                    $j++;
                                    $storedAvg = $mark['average'];
                                @endphp

                                <td class="p-[15px] border border-black text-right">{{ $i - $j }}</td>
                            @else
                                @php
                                    $j = 0;
                                    $storedAvg = $mark['average'];
                                @endphp

                                <td class="p-[15px] border border-black text-right">{{ $i }}</td>
                            @endif

                            @if ($mark['average'] > 0)
                                <td class="p-[15px] border border-black">{{ finalStatus($mark['average']) }}</td>
                            @else
                                <td class="p-[15px] border border-black"></td>
                            @endif
                        </tr>

                        @php
                            $i++;
                        @endphp
                    @endforeach
                </tbody>
            </table>
            <div class="my-[5px]">
                {{ $marks->links() }}
            </div>
        </div>

        <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-2 mt-5">
            <div>
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="border border-black p-1 uppercase">Wastani Ya Shule</th>
                            <th class="border border-black p-1 uppercase">Daraja</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white">
                            <td class="p-[15px] border border-black p-1 text-center">
                                @php
                                    $gATotal = array_sum($gAverage);
                                    $gAver =
                                        $totalStudentsTookExam > 0
                                            ? $gATotal / (count($subjects) * $totalStudentsTookExam)
                                            : 0;
                                    $schoolGrade = assignGrade($gAver);
                                @endphp
                                {{ number_format($gAver, 2) }}
                            </td>
                            <td class="p-[15px] border border-black p-1 text-center">{{ $schoolGrade }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="border border-black p-1 uppercase">Wastani Ya Ufaulu</th>
                            <th class="border border-black p-1 uppercase">Daraja</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white">
                            <td class="p-[15px] border border-black p-1 text-center">
                                @php
                                    $gATotal = array_sum($gAverage);
                                    $gAver = $totalStudentsTookExam > 0 ? $gATotal / $totalStudentsTookExam : 0;
                                @endphp
                                {{ number_format($gAver, 2) }}
                            </td>
                            <td class="p-[15px] border border-black p-1 text-center">{{ $schoolGrade }}</td>
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
                        $gradeMaleCount =
                            $gradeDistribution['male']['A'] +
                            $gradeDistribution['male']['B'] +
                            $gradeDistribution['male']['C'] +
                            $gradeDistribution['male']['D'] +
                            $gradeDistribution['male']['E'];
                        $gradeFemaleCount =
                            $gradeDistribution['female']['A'] +
                            $gradeDistribution['female']['B'] +
                            $gradeDistribution['female']['C'] +
                            $gradeDistribution['female']['D'] +
                            $gradeDistribution['female']['E'];
                        if ($classId > 4) {
                            $failCount =
                                $gradeDistribution['male']['D'] +
                                $gradeDistribution['male']['E'] +
                                $gradeDistribution['female']['D'] +
                                $gradeDistribution['female']['E'];
                            $failMaleCount = $gradeDistribution['male']['D'] + $gradeDistribution['male']['E'];
                            $failFemaleCount = $gradeDistribution['female']['D'] + $gradeDistribution['female']['E'];
                        } else {
                            $failCount = $gradeDistribution['male']['E'] + $gradeDistribution['female']['E'];
                            $failMaleCount = $gradeDistribution['male']['E'];
                            $failFemaleCount = $gradeDistribution['female']['E'];
                        }
                        $gradeCount = $gradeMaleCount + $gradeFemaleCount;
                    @endphp

                    <tr class="bg-white">
                        <td class="p-[15px] border border-black text-center">1</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['male']['A'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['male']['B'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['male']['C'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['male']['D'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['male']['E'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['male']['ABS'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ array_sum($gradeDistribution['male']) }}
                        </td>
                    </tr>

                    <tr class="bg-gray-200">
                        <td class="p-[15px] border border-black text-center">2</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['female']['A'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['female']['B'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['female']['C'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['female']['D'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['female']['E'] }}</td>
                        <td class="p-[15px] border border-black text-center">{{ $gradeDistribution['female']['ABS'] }}
                        </td>
                        <td class="p-[15px] border border-black text-center">{{ array_sum($gradeDistribution['female']) }}
                        </td>
                    </tr>

                    <tr class="bg-white">
                        <td class="p-[15px] border border-black text-center">Jumla</td>
                        <td class="p-[15px] border border-black text-center">
                            {{ $gradeDistribution['male']['A'] + $gradeDistribution['female']['A'] }}</td>
                        <td class="p-[15px] border border-black text-center">
                            {{ $gradeDistribution['male']['B'] + $gradeDistribution['female']['B'] }}</td>
                        <td class="p-[15px] border border-black text-center">
                            {{ $gradeDistribution['male']['C'] + $gradeDistribution['female']['C'] }}</td>
                        <td class="p-[15px] border border-black text-center">
                            {{ $gradeDistribution['male']['D'] + $gradeDistribution['female']['D'] }}</td>
                        <td class="p-[15px] border border-black text-center">
                            {{ $gradeDistribution['male']['E'] + $gradeDistribution['female']['E'] }}</td>
                        <td class="p-[15px] border border-black text-center">
                            {{ $gradeDistribution['male']['ABS'] + $gradeDistribution['female']['ABS'] }}</td>
                        <td class="p-[15px] border border-black text-center">
                            {{ array_sum($gradeDistribution['male']) + array_sum($gradeDistribution['female']) }}</td>
                    </tr>
                </table>
            </div>

            <div>
                <table class="w-full">
                    <thead>
                        <tr>
                            <th colspan="2" class="border border-black px-2 text-center">WALIOSAJIRIWA</th>
                            <th class="border border-black px-2 text-center uppercase">Pass</th>
                            <th class="border border-black px-2 text-center uppercase">Fail</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white text-center">
                            <td class="p-[15px] border border-black px-2">1</td>
                            <td class="p-[15px] border border-black px-2">{{ $gradeMaleCount }}</td>
                            <td class="p-[15px] border border-black px-2">{{ $gradeMaleCount - $failMaleCount }}</td>
                            <td class="p-[15px] border border-black px-2">{{ $failMaleCount }}</td>
                        </tr>

                        <tr class="bg-gray-200 text-center">
                            <td class="p-[15px] border border-black px-2">2</td>
                            <td class="p-[15px] border border-black px-2">{{ $gradeFemaleCount }}</td>
                            <td class="p-[15px] border border-black px-2">{{ $gradeFemaleCount - $failFemaleCount }}</td>
                            <td class="p-[15px] border border-black px-2">{{ $failFemaleCount }}</td>
                        </tr>

                        <tr class="bg-white text-center">
                            <td class="p-[15px] border border-black px-2" rowspan="2">Jumla</td>
                            <td class="p-[15px] border border-black px-2" rowspan="2">{{ $gradeCount }}</td>
                            <td class="p-[15px] border border-black px-2">{{ $gradeCount - $failCount }}</td>
                            <td class="p-[15px] border border-black px-2">{{ $failCount }}</td>
                        </tr>

                        <tr class="bg-gray-200 text-center">
                            <td class="p-[15px] border border-black px-2">
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

                            <td class="p-[15px] border border-black px-2">
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

        <div class="mt-5 overflow-x-auto">
            @php
                $failedCount = 0;
                $subList = ['hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili'];
            @endphp

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
                    @if (count($subjects) > 0)
                        @foreach ($subjects as $index => $subject)
                            <tr>
                                <td class="p-[15px] border border-black capitalize">{{ $subject }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['A']['male'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['A']['female'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['A']['male'][$index] + $subjectGradeCounts['A']['female'][$index] }}
                                </td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['B']['male'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['B']['female'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['B']['male'][$index] + $subjectGradeCounts['B']['female'][$index] }}
                                </td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['C']['male'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['C']['female'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['C']['male'][$index] + $subjectGradeCounts['C']['female'][$index] }}
                                </td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['D']['male'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['D']['female'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['D']['male'][$index] + $subjectGradeCounts['D']['female'][$index] }}
                                </td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['E']['male'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['E']['female'][$index] }}</td>
                                <td class="p-[15px] text-center border border-black">
                                    {{ $subjectGradeCounts['E']['male'][$index] + $subjectGradeCounts['E']['female'][$index] }}
                                </td>

                                <!-- Calculate and display the average for each subject -->
                                @if ($totalStudentsTookExam > 0)
                                    <td class="p-[15px] text-center border border-black">
                                        {{ number_format($gAverage[$index] / $totalStudentsTookExam, 2) }}</td>
                                @else
                                    <td class="p-[15px] text-center border border-black">0</td>
                                @endif

                                <!-- Calculate and display the pass/fail rate for the subject -->
                                @php
                                    if ($classId > 4) {
                                        $passCount =
                                            $subjectGradeCounts['A']['male'][$index] +
                                            $subjectGradeCounts['A']['female'][$index] +
                                            $subjectGradeCounts['B']['male'][$index] +
                                            $subjectGradeCounts['B']['female'][$index] +
                                            $subjectGradeCounts['C']['male'][$index] +
                                            $subjectGradeCounts['C']['female'][$index];
                                        $failCount =
                                            $subjectGradeCounts['D']['male'][$index] +
                                            $subjectGradeCounts['D']['female'][$index] +
                                            $subjectGradeCounts['E']['male'][$index] +
                                            $subjectGradeCounts['E']['female'][$index];
                                    } else {
                                        $passCount =
                                            $subjectGradeCounts['A']['male'][$index] +
                                            $subjectGradeCounts['A']['female'][$index] +
                                            $subjectGradeCounts['B']['male'][$index] +
                                            $subjectGradeCounts['B']['female'][$index] +
                                            $subjectGradeCounts['C']['male'][$index] +
                                            $subjectGradeCounts['C']['female'][$index] +
                                            $subjectGradeCounts['D']['male'][$index] +
                                            $subjectGradeCounts['D']['female'][$index];
                                        $failCount =
                                            $subjectGradeCounts['E']['male'][$index] +
                                            $subjectGradeCounts['E']['female'][$index];
                                    }

                                    $totalStudents = $passCount + $failCount;
                                    $passRate = $totalStudents > 0 ? ($passCount / $totalStudents) * 100 : 0;
                                    $failRate = $totalStudents > 0 ? ($failCount / $totalStudents) * 100 : 0;
                                @endphp
                                <td class="p-[15px] text-center border border-black">{{ $passCount }}</td>
                                <td class="p-[15px] text-center border border-black">{{ number_format($passRate, 2) }}%
                                </td>
                                <td class="p-[15px] text-center border border-black">{{ $failCount }}</td>
                                <td class="p-[15px] text-center border border-black">{{ number_format($failRate, 2) }}%
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="p-[15px] text-red-500 text-center p-2" colspan="6">No Data Found!</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="progress-bar" style="display: none;">
        <div class="progress" style="width: 100%; height: 5px; background-color: #ccc;">
            <div class="progress-bar-inner" style="width: 0%; background-color: #337ab7;"></div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#filterForm').submit(function() {
                setTimeout(function() {
                    var progressBar = $('.progress-bar-inner');
                    var interval = setInterval(function() {
                        var width = parseInt(progressBar.width()) + 10;
                        progressBar.css('width', width + '%');
                        if (width >= 100) {
                            clearInterval(interval);
                        }
                    }, 100);
                }, 500);
            });

            $(document).ajaxStop(function() {
                $('.progress-bar').hide();
            });
        });
    </script>
@endsection
