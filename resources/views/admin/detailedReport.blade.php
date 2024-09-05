@extends('admin.layout')

@section('content')
    @php
        function assignGrade($marks)
        {
            $rank = \App\Models\Ranks::select('rankName', 'rankRangeMin', 'rankRangeMax')
                ->where([['isActive', '=', '1'], ['isDeleted', '=', '0']])
                ->orderBy('rankName', 'asc')
                ->get();

            if ($rank) {
                if ($rank[0]['rankRangeMin'] < $marks && $rank[0]['rankRangeMax'] >= $marks) {
                    return $rank[0]['rankName'];
                } elseif ($rank[1]['rankRangeMin'] < $marks && $rank[1]['rankRangeMax'] >= $marks) {
                    return $rank[1]['rankName'];
                } elseif ($rank[2]['rankRangeMin'] < $marks && $rank[2]['rankRangeMax'] >= $marks) {
                    return $rank[2]['rankName'];
                } elseif ($rank[3]['rankRangeMin'] < $marks && $rank[3]['rankRangeMax'] >= $marks) {
                    return $rank[3]['rankName'];
                } else {
                    return $rank[4]['rankName'];
                }
            } else {
                return 'Null';
            }
        }
    @endphp

    <div class="p-3">
        <div class="flex justify-end">
            <form action="{{ url('/downloadDetailedReport') }}" method="post">
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

            <form action="{{ url('/filterDetailedReport') }}" method="post" id="filterForm">
                @csrf

                <div class="grid lg:grid-cols-7 md:grid-cols-3 grid-cols-1 gap-2">
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
                                <option value="" class="text-red-500">Hakuna Taarifa Iliyopatikana!</option>
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
                                <option value="" class="text-red-500">Hakuna Taarifa Iliyopatikana!</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="region">Chagua Mkoa:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="region" id="region">
                            <option value="">-- CHAGUA MKOA --</option>
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
                        <label for="district">Chagua Wilaya:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="district"
                            id="district">
                            <option value="">-- CHAGUA WILAYA --</option>
                            @if (count($districts) > 0)
                                @foreach ($districts as $district)
                                    <option value="{{ $district['districtId'] }}" @selected($districtId == $district['districtId'])>
                                        {{ $district['districtName'] }} ({{ $district['districtCode'] }})</option>
                                @endforeach
                            @else
                                <option value="" class="text-red-500">Hakuna Taarifa Iliyopatikana!</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="ward">Chagua Kata:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="ward" id="ward">
                            <option value="">-- CHAGUA KATA --</option>
                            @if (count($wards) > 0)
                                @foreach ($wards as $ward)
                                    <option value="{{ $ward['wardId'] }}" @selected($wardId == $ward['wardId'])>
                                        {{ $ward['wardName'] }} ({{ $ward['wardCode'] }})</option>
                                @endforeach
                            @else
                                <option value="" class="text-red-500">Hakuna Taarifa Iliyopatikana!</option>
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
                <a href="{{ url('/dashboard/detailed-report') }}"><button type="button" form="filterForm"
                        class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">Onesha
                        Upya</button></a>
                <button type="submit" form="filterForm"
                    class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">Kichujio</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <h2 class="text-2xl font-bold mb-2">SFNA ANALYSIS:</h2>

            @if ($classId > 4)
                @php
                    $waliofanyaTitle1 = 'Waliopata Alama A-C';
                    $waliofanyaTitle2 = 'Waliopata D-E';
                    $jumlaTitle1 = 'Jumla Ya A-C';
                    $jumlaTitle2 = 'Jumla Ya D-E';
                    $colSpan1 = 9;
                    $colSpan2 = 6;
                @endphp
            @else
                @php
                    $waliofanyaTitle1 = 'Waliopata Alama A-D';
                    $waliofanyaTitle2 = 'Waliopata E';
                    $jumlaTitle1 = 'Jumla Ya A-D';
                    $jumlaTitle2 = 'Jumla Ya E';
                    $colSpan1 = 12;
                    $colSpan2 = 3;
                @endphp
            @endif
            <table class="myTable bg-white">
                <thead>
                    <tr>
                        <th class="border border-black uppercase" rowspan="3">NA</th>
                        <th class="border border-black uppercase" rowspan="3">Mkoa</th>
                        <th class="border border-black uppercase" rowspan="3">Wilaya</th>
                        <th class="border border-black uppercase" rowspan="3">Kata</th>
                        <th class="border border-black uppercase" rowspan="3">Shule</th>
                        <th class="border border-black uppercase" colspan="3" rowspan="2">Walioanza DRS LA I -
                            {{ date('Y') - ($classId - 1) }}</th>
                        <th class="border border-black uppercase" colspan="3" rowspan="2">Waliosaijliwa</th>
                        <th class="border border-black uppercase" colspan="3" rowspan="2">Waliofanya Mtihani</th>
                        <th class="border border-black uppercase" rowspan="3">%</th>
                        <th class="border border-black uppercase" colspan="3" rowspan="2">Wasiofanya</th>
                        <th class="border border-black uppercase" rowspan="3">%</th>
                        <th class="border border-black uppercase" colspan="{{ $colSpan1 }}" rowspan="1">
                            {{ $waliofanyaTitle1 }}</th>
                        <th class="border border-black uppercase" colspan="3" rowspan="2">{{ $jumlaTitle1 }}</th>
                        <th class="border border-black uppercase" rowspan="3">%</th>
                        <th class="border border-black uppercase" colspan="{{ $colSpan2 }}" rowspan="1">
                            {{ $waliofanyaTitle2 }}</th>
                        <th class="border border-black uppercase" colspan="3" rowspan="2">{{ $jumlaTitle2 }}</th>
                        <th class="border border-black uppercase" rowspan="3">%</th>
                        <th class="border border-black uppercase" rowspan="3">Wastani ya ufaulu</th>
                        <th class="border border-black uppercase" rowspan="3">Daraja</th>
                    </tr>

                    <tr>
                        <th class="border border-black" colspan="3" rowspan="1">A</th>
                        <th class="border border-black" colspan="3" rowspan="1">B</th>
                        <th class="border border-black" colspan="3" rowspan="1">C</th>

                        <th class="border border-black" colspan="3" rowspan="1">D</th>
                        <th class="border border-black" colspan="3" rowspan="1">E</th>
                    </tr>

                    <tr>
                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($marks as $mark)
                        @php
                            $regionData = \App\Models\Regions::find($mark['regionId']);
                            $regionName = $regionData
                                ? $regionData['regionName']
                                : '<span class="text-red-500 italic">Not Found!</span>';

                            $districtData = \App\Models\Districts::find($mark['districtId']);
                            $districtName = $districtData
                                ? $districtData['districtName']
                                : '<span class="text-red-500 italic">Not Found!</span>';

                            $wardData = \App\Models\Wards::find($mark['wardId']);
                            $wardName = $wardData
                                ? $wardData['wardName']
                                : '<span class="text-red-500 italic">Not Found!</span>';

                            $schoolData = \App\Models\Schools::find($mark['schoolId']);
                            $schoolName = $schoolData
                                ? $schoolData['schoolName']
                                : '<span class="text-red-500 italic">Not Found!</span>';

                            $examCondition = $examId == '' ? ['examId', '!=', null] : ['examId', '=', $examId];
                            $regionCondition =
                                $regionId == '' ? ['regionId', '!=', null] : ['regionId', '=', $regionId];
                            $districtCondition =
                                $districtId == '' ? ['districtId', '!=', null] : ['districtId', '=', $districtId];
                            $wardCondition = $wardId == '' ? ['wardId', '!=', null] : ['wardId', '=', $wardId];

                            $fgMale = \App\Models\Marks::where([
                                ['isActive', '=', '1'],
                                ['isDeleted', '=', '0'],
                                ['classId', '=', $classId],
                                ['firstGrade', '=', '1'],
                                ['gender', '=', 'M'],
                                ['schoolId', '=', $mark['schoolId']],
                                $examCondition,
                                $regionCondition,
                                $districtCondition,
                                $wardCondition,
                            ])
                                ->whereBetween('examDate', [$startDate, $endDate])
                                ->count();

                            $fgFemale = \App\Models\Marks::where([
                                ['isActive', '=', '1'],
                                ['isDeleted', '=', '0'],
                                ['classId', '=', $classId],
                                ['firstGrade', '=', '1'],
                                ['gender', '=', 'F'],
                                ['schoolId', '=', $mark['schoolId']],
                                $examCondition,
                                $regionCondition,
                                $districtCondition,
                                $wardCondition,
                            ])
                                ->whereBetween('examDate', [$startDate, $endDate])
                                ->count();

                            $avgMarks = \App\Models\Marks::selectRaw('gender, average as averageMarks')
                                ->where([
                                    ['isActive', '=', '1'],
                                    ['isDeleted', '=', '0'],
                                    ['classId', '=', $classId],
                                    ['schoolId', '=', $mark['schoolId']],
                                    $examCondition,
                                    $regionCondition,
                                    $districtCondition,
                                    $wardCondition,
                                ])
                                ->whereBetween('examDate', [$startDate, $endDate])
                                ->get();

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
                                $avg['gender'] == 'M' ? $totalMale++ : $totalFemale++;

                                if ($avg['averageMarks'] == 0) {
                                    if ($avg['gender'] == 'M') {
                                        $maleAbsent++;
                                    } else {
                                        $femaleAbsent++;
                                    }
                                } else {
                                    if (assignGrade($avg['averageMarks']) == 'A') {
                                        $avg['gender'] == 'M' ? $aGradeMale++ : $aGradeFemale++;
                                    } elseif (assignGrade($avg['averageMarks']) == 'B') {
                                        $avg['gender'] == 'M' ? $bGradeMale++ : $bGradeFemale++;
                                    } elseif (assignGrade($avg['averageMarks']) == 'C') {
                                        $avg['gender'] == 'M' ? $cGradeMale++ : $cGradeFemale++;
                                    } elseif (assignGrade($avg['averageMarks']) == 'D') {
                                        $avg['gender'] == 'M' ? $dGradeMale++ : $dGradeFemale++;
                                    } else {
                                        $avg['gender'] == 'M' ? $eGradeMale++ : $eGradeFemale++;
                                    }
                                }
                            }

                            if ($classId > 4) {
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
                        @endphp

                        <tr class="odd:bg-white even:bg-gray-200">
                            <td class="border border-black">{{ $i }}</td>
                            <td class="border border-black">{!! $regionName !!}</td>
                            <td class="border border-black">{!! $districtName !!}</td>
                            <td class="border border-black">{!! $wardName !!}</td>
                            <td class="border border-black">{!! $schoolName !!}</td>
                            <td class="border border-black fgMale">{{ $fgMale }}</td>
                            <td class="border border-black fgFemale">{{ $fgFemale }}</td>
                            <td class="border border-black">{{ $fgMale + $fgFemale }}</td>
                            <td class="border border-black maleTotal">{{ $totalMale }}</td>
                            <td class="border border-black femaleTotal">{{ $totalFemale }}</td>
                            <td class="border border-black">{{ $totalMale + $totalFemale }}</td>
                            <td class="border border-black totalPassMale">{{ $totalPassMale + $totalFailMale }}</td>
                            <td class="border border-black totalPassFemale">{{ $totalPassFemale + $totalFailFemale }}</td>
                            {{-- <td class="border border-black"> {{ $totalPassMale + $totalPassFemale + $totalFailMale + $totalFailFemale }}</td> --}}
                            <td class="border border-black">
                                @php
                                    $totalstudent =
                                        $totalPassMale + $totalPassFemale + $totalFailMale + $totalFailFemale;

                                @endphp
                                {{-- @dd($totalPassMale, $totalPassFemale, $totalFailMale, $totalFailFemale, $totalPassMale + $totalPassFemale + $totalFailMale + $totalFailFemale, $totalstudent) --}}
                                {{ $totalstudent }}</td>
                            @if ($totalMale + $totalFemale == 0)
                                <td class="border border-black">0</td>
                            @else
                                <td class="border border-black">
                                    {{ number_format((($totalPassMale + $totalPassFemale + $totalFailMale + $totalFailFemale) / ($totalMale + $totalFemale)) * 100, 2) }}
                                </td>
                            @endif
                            <td class="border border-black totalFailMale">{{ $maleAbsent }}</td>
                            <td class="border border-black totalFailFemale">{{ $femaleAbsent }}</td>
                            <td class="border border-black">{{ $maleAbsent + $femaleAbsent }}</td>
                            @if ($totalMale + $totalFemale == 0)
                                <td class="border border-black">0</td>
                            @else
                                <td class="border border-black">
                                    {{ number_format((($maleAbsent + $femaleAbsent) / ($totalMale + $totalFemale)) * 100, 2) }}
                                </td>
                            @endif
                            <td class="border border-black aGradeMale">{{ $aGradeMale }}</td>
                            <td class="border border-black aGradeFemale">{{ $aGradeFemale }}</td>
                            <td class="border border-black">{{ $aGradeMale + $aGradeFemale }}</td>
                            <td class="border border-black bGradeMale">{{ $bGradeMale }}</td>
                            <td class="border border-black bGradeFemale">{{ $bGradeFemale }}</td>
                            <td class="border border-black">{{ $bGradeMale + $bGradeFemale }}</td>
                            <td class="border border-black cGradeMale">{{ $cGradeMale }}</td>
                            <td class="border border-black cGradeFemale">{{ $cGradeFemale }}</td>
                            <td class="border border-black">{{ $cGradeMale + $cGradeFemale }}</td>

                            @if ($classId > 4)
                                <td class="border border-black passGradeMale">
                                    {{ $aGradeMale + $bGradeMale + $cGradeMale }}
                                </td>
                                <td class="border border-black passGradeFemale">
                                    {{ $aGradeFemale + $bGradeFemale + $cGradeFemale }}</td>
                                <td class="border border-black">
                                    @php
                                        $gradess =
                                            $aGradeMale +
                                            $bGradeMale +
                                            $cGradeMale +
                                            $aGradeFemale +
                                            $bGradeFemale +
                                            $cGradeFemale;
                                    @endphp
                                    {{ $gradess }}
                                </td>
                                {{-- <td class="border border-black">{{ ($aGradeMale+$bGradeMale+$cGradeMale+$aGradeFemale+$bGradeFemale+$cGradeFemale) }}</td> --}}
                                @if ($totalMale + $totalFemale == 0)
                                    <td class="border border-black">0</td>
                                @else
                                    <td class="border border-black">
                                        {{ number_format((($aGradeMale + $bGradeMale + $cGradeMale + $aGradeFemale + $bGradeFemale + $cGradeFemale) / ($totalMale + $totalFemale)) * 100, 2) }}
                                    </td>
                                @endif
                                <td class="border border-black dGradeMale">{{ $dGradeMale }}</td>
                                <td class="border border-black dGradeFemale">{{ $dGradeFemale }}</td>
                                <td class="border border-black">{{ $dGradeMale + $dGradeFemale }}</td>
                                <td class="border border-black eGradeMale">{{ $eGradeMale }}</td>
                                <td class="border border-black eGradeFemale">{{ $eGradeFemale }}</td>
                                <td class="border border-black">{{ $eGradeMale + $eGradeFemale }}</td>
                                <td class="border border-black failGradeMale">{{ $eGradeMale + $dGradeMale }}</td>
                                <td class="border border-black failGradeFemale">{{ $eGradeFemale + $dGradeFemale }}</td>
                                <td class="border border-black">
                                    {{ $eGradeMale + $dGradeMale + $eGradeFemale + $dGradeFemale }}</td>
                                @if ($totalMale + $totalFemale == 0)
                                    <td class="border border-black">0</td>
                                @else
                                    <td class="border border-black">
                                        {{ number_format(($gradess / $totalstudent) * 100, 2) }}
                                        {{-- {{ number_format((($eGradeMale + $dGradeMale + $eGradeFemale + $dGradeFemale) / ($totalMale + $totalFemale)) * 100, 2) }} --}}
                                    </td>
                                @endif
                            @else
                                <td class="border border-black dGradeMale">{{ $dGradeMale }}</td>
                                <td class="border border-black dGradeFemale">{{ $dGradeFemale }}</td>
                                <td class="border border-black">{{ $dGradeMale + $dGradeFemale }}</td>
                                <td class="border border-black passGradeMale">
                                    {{ $aGradeMale + $bGradeMale + $cGradeMale + $dGradeMale }}</td>
                                <td class="border border-black passGradeFemale">
                                    {{ $aGradeFemale + $bGradeFemale + $cGradeFemale + $dGradeFemale }}</td>
                                <td class="border border-black">
                                    @php
                                        $gradess =
                                            $aGradeMale +
                                            $bGradeMale +
                                            $cGradeMale +
                                            $dGradeMale +
                                            $aGradeFemale +
                                            $bGradeFemale +
                                            $cGradeFemale +
                                            $dGradeFemale;
                                    @endphp
                                    {{ $gradess }}
                                    {{-- {{ $aGradeMale + $bGradeMale + $cGradeMale + $dGradeMale + $aGradeFemale + $bGradeFemale + $cGradeFemale + $dGradeFemale }} --}}
                                </td>
                                @if ($totalMale + $totalFemale == 0)
                                    <td class="border border-black">0</td>
                                @else
                                    <td class="border border-black">
                                        {{ number_format(($gradess / $totalstudent) * 100, 2) }}
                                        {{-- {{ number_format((($aGradeMale + $bGradeMale + $cGradeMale + $dGradeMale + $aGradeFemale + $bGradeFemale + $cGradeFemale + $dGradeFemale) / $totalstudent) * 100, 2) }} --}}
                                    </td>
                                @endif

                                <td class="border border-black eGradeMale">{{ $eGradeMale }}</td>
                                <td class="border border-black eGradeFemale">{{ $eGradeFemale }}</td>
                                <td class="border border-black">{{ $eGradeMale + $eGradeFemale }}</td>
                                <td class="border border-black failGradeMale">{{ $eGradeMale }}</td>
                                <td class="border border-black failGradeFemale">{{ $eGradeFemale }}</td>
                                <td class="border border-black">{{ $eGradeMale + $eGradeFemale }}</td>
                                @if ($totalMale + $totalFemale == 0)
                                    <td class="border border-black">0</td>
                                @else
                                    <td class="border border-black">
                                        {{ number_format((($eGradeMale + $eGradeFemale) / $totalstudent) * 100, 2) }}
                                    </td>
                                @endif
                            @endif

                            <td class="border border-black averageMarks">
                                {{ number_format($mark['averageMarks'] / (count($avgMarks) - $maleAbsent - $femaleAbsent), 5) }}
                            </td>
                            <td class="border border-black">
                                {{ assignGrade(number_format($mark['averageMarks'] / (count($avgMarks) - $maleAbsent - $femaleAbsent), 5) / 6) }}
                            </td>
                        </tr>

                        @php
                            $i++;
                        @endphp
                    @endforeach

                    @if (count($marks) > 0)
                        <tr class="font-bold">
                            <td class="border-y border-l border-black text-center text-white">9999</td>
                            <td class="border-y border-black"></td>
                            <td class="border-y border-black">Jumla</td>
                            <td class="border-y border-black"></td>
                            <td class="border-y border-black"></td>
                            <td class="border border-black" id="fgMaleJumla">0</td>
                            <td class="border border-black" id="fgFemaleJumla">0</td>
                            <td class="border border-black" id="fgJumla">0</td>
                            <td class="border border-black" id="maleJumla">0</td>
                            <td class="border border-black" id="femaleJumla">0</td>
                            <td class="border border-black" id="total">0</td>
                            <td class="border border-black" id="malePassTotalJumla">0</td>
                            <td class="border border-black" id="femalePassTotalJumla">0</td>
                            <td class="border border-black" id="passTotalJumla">0</td>
                            <td class="border border-black" id="passPercent">0</td>
                            <td class="border border-black" id="maleFailTotalJumla">0</td>
                            <td class="border border-black" id="femaleFailTotalJumla">0</td>
                            <td class="border border-black" id="failTotalJumla">0</td>
                            <td class="border border-black" id="failPercent">0</td>
                            <td class="border border-black" id="aGradeMaleJumla">0</td>
                            <td class="border border-black" id="aGradeFemaleJumla">0</td>
                            <td class="border border-black" id="aGradeJumla">0</td>
                            <td class="border border-black" id="bGradeMaleJumla">0</td>
                            <td class="border border-black" id="bGradeFemaleJumla">0</td>
                            <td class="border border-black" id="bGradeJumla">0</td>
                            <td class="border border-black" id="cGradeMaleJumla">0</td>
                            <td class="border border-black" id="cGradeFemaleJumla">0</td>
                            <td class="border border-black" id="cGradeJumla">0</td>

                            @if ($classId > 4)
                                <td class="border border-black" id="passGradeMaleJumla"></td>
                                <td class="border border-black" id="passGradeFemaleJumla"></td>
                                <td class="border border-black" id="passGradeJumla"></td>
                                <td class="border border-black" id="passGradePercent"></td>
                                <td class="border border-black" id="dGradeMaleJumla"></td>
                                <td class="border border-black" id="dGradeFemaleJumla"></td>
                                <td class="border border-black" id="dGradeJumla"></td>
                                <td class="border border-black" id="eGradeMaleJumla"></td>
                                <td class="border border-black" id="eGradeFemaleJumla"></td>
                                <td class="border border-black" id="eGradeJumla"></td>
                                <td class="border border-black" id="failGradeMaleJumla"></td>
                                <td class="border border-black" id="failGradeFemaleJumla"></td>
                                <td class="border border-black" id="failGradeJumla"></td>
                                <td class="border border-black" id="failGradePercent"></td>
                            @else
                                <td class="border border-black" id="dGradeMaleJumla"></td>
                                <td class="border border-black" id="dGradeFemaleJumla"></td>
                                <td class="border border-black" id="dGradeJumla"></td>
                                <td class="border border-black" id="passGradeMaleJumla"></td>
                                <td class="border border-black" id="passGradeFemaleJumla"></td>
                                <td class="border border-black" id="passGradeJumla"></td>
                                <td class="border border-black" id="passGradePercent"></td>
                                <td class="border border-black" id="eGradeMaleJumla"></td>
                                <td class="border border-black" id="eGradeFemaleJumla"></td>
                                <td class="border border-black" id="eGradeJumla"></td>
                                <td class="border border-black" id="failGradeMaleJumla"></td>
                                <td class="border border-black" id="failGradeFemaleJumla"></td>
                                <td class="border border-black" id="failGradeJumla"></td>
                                <td class="border border-black" id="failGradePercent"></td>
                            @endif

                            <td class="border border-black" id="averageMarksSum"></td>
                            <td class="border border-black" id="finalDaraja"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            var fgMaleSum = 0;
            var fgFemaleSum = 0;

            var maleTotalSum = 0;
            var femaleTotalSum = 0;

            var totalPassMaleSum = 0;
            var totalPassFemaleSum = 0;

            var totalFailMaleSum = 0;
            var totalFailFemaleSum = 0;

            var aGradeMaleSum = 0;
            var bGradeMaleSum = 0;
            var cGradeMaleSum = 0;
            var dGradeMaleSum = 0;
            var eGradeMaleSum = 0;

            var aGradeFemaleSum = 0;
            var bGradeFemaleSum = 0;
            var cGradeFemaleSum = 0;
            var dGradeFemaleSum = 0;
            var eGradeFemaleSum = 0;

            var passGradeMaleSum = 0;
            var passGradeFemaleSum = 0;

            var failGradeMaleSum = 0;
            var failGradeFemaleSum = 0;

            var averageMarksSum = 0;

            $('.fgMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    fgMaleSum += value;
                }
            });

            $('.fgFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    fgFemaleSum += value;
                }
            });

            $('.maleTotal').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    maleTotalSum += value;
                }
            });

            $('.femaleTotal').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    femaleTotalSum += value;
                }
            });

            $('.totalPassMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    totalPassMaleSum += value;
                }
            });

            $('.totalPassFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    totalPassFemaleSum += value;
                }
            });

            $('.totalFailMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    totalFailMaleSum += value;
                }
            });

            $('.totalFailFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    totalFailFemaleSum += value;
                }
            });

            $('.aGradeMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    aGradeMaleSum += value;
                }
            });

            $('.bGradeMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    bGradeMaleSum += value;
                }
            });

            $('.cGradeMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    cGradeMaleSum += value;
                }
            });

            $('.dGradeMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    dGradeMaleSum += value;
                }
            });

            $('.eGradeMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    eGradeMaleSum += value;
                }
            });

            $('.aGradeFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    aGradeFemaleSum += value;
                }
            });

            $('.bGradeFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    bGradeFemaleSum += value;
                }
            });

            $('.cGradeFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    cGradeFemaleSum += value;
                }
            });

            $('.passGradeMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    passGradeMaleSum += value;
                }
            });

            $('.passGradeFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    passGradeFemaleSum += value;
                }
            });

            $('.failGradeMale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    failGradeMaleSum += value;
                }
            });

            $('.failGradeFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    failGradeFemaleSum += value;
                }
            });

            $('.dGradeFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    dGradeFemaleSum += value;
                }
            });

            $('.eGradeFemale').each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    eGradeFemaleSum += value;
                }
            });

            $('.averageMarks').each(function() {
                var value = parseFloat($(this).text());
                if (!isNaN(value)) {
                    averageMarksSum += value;
                }
            });

            $("#fgMaleJumla").text(fgMaleSum);
            $("#fgFemaleJumla").text(fgFemaleSum);
            $("#fgJumla").text(fgFemaleSum + fgMaleSum);

            $("#maleJumla").text(maleTotalSum);
            $("#femaleJumla").text(femaleTotalSum);
            $("#total").text(femaleTotalSum + maleTotalSum);

            $("#malePassTotalJumla").text(totalPassMaleSum);
            $("#femalePassTotalJumla").text(totalPassFemaleSum);
            $("#passTotalJumla").text(totalPassMaleSum + totalPassFemaleSum);
            $("#passPercent").text(((totalPassMaleSum + totalPassFemaleSum) / (femaleTotalSum + maleTotalSum) * 100)
                .toFixed(2));

            $("#maleFailTotalJumla").text(totalFailMaleSum);
            $("#femaleFailTotalJumla").text(totalFailFemaleSum);
            $("#failTotalJumla").text(totalFailMaleSum + totalFailFemaleSum);
            $("#failPercent").text(((totalFailMaleSum + totalFailFemaleSum) / (femaleTotalSum + maleTotalSum) * 100)
                .toFixed(2));

            $("#aGradeMaleJumla").text(aGradeMaleSum);
            $("#bGradeMaleJumla").text(bGradeMaleSum);
            $("#cGradeMaleJumla").text(cGradeMaleSum);
            $("#dGradeMaleJumla").text(dGradeMaleSum);
            $("#eGradeMaleJumla").text(eGradeMaleSum);

            $("#aGradeFemaleJumla").text(aGradeFemaleSum);
            $("#bGradeFemaleJumla").text(bGradeFemaleSum);
            $("#cGradeFemaleJumla").text(cGradeFemaleSum);
            $("#dGradeFemaleJumla").text(dGradeFemaleSum);
            $("#eGradeFemaleJumla").text(eGradeFemaleSum);

            $("#aGradeJumla").text(aGradeFemaleSum + aGradeMaleSum);
            $("#bGradeJumla").text(bGradeFemaleSum + bGradeMaleSum);
            $("#cGradeJumla").text(cGradeFemaleSum + cGradeMaleSum);
            $("#dGradeJumla").text(dGradeFemaleSum + dGradeMaleSum);
            $("#eGradeJumla").text(eGradeFemaleSum + eGradeMaleSum);

            $("#passGradeMaleJumla").text(passGradeMaleSum);
            $("#passGradeFemaleJumla").text(passGradeFemaleSum);
            $("#passGradeJumla").text(passGradeMaleSum + passGradeFemaleSum);

            var totalPass = totalPassMaleSum + totalPassFemaleSum;
            var totalStudents = passGradeMaleSum + passGradeFemaleSum;

            $("#passGradePercent").text(((totalStudents / totalPass) * 100).toFixed(2));
            // $("#passGradePercent").text(((passGradeMaleSum + passGradeFemaleSum) / (femaleTotalSum + maleTotalSum) *
            //     100).toFixed(2));

            $("#failGradeMaleJumla").text(failGradeMaleSum);
            $("#failGradeFemaleJumla").text(failGradeFemaleSum);
            $("#failGradeJumla").text(failGradeMaleSum + failGradeFemaleSum);
            $("#failGradePercent").text(((failGradeMaleSum + failGradeFemaleSum) / (femaleTotalSum + maleTotalSum) *
                100).toFixed(2));

            if ({{ count($marks) }} > 0) {
                var finalAvg = averageMarksSum / {{ count($marks) }};
                $("#averageMarksSum").text((finalAvg).toFixed(5));

                if (finalAvg >= 241 && finalAvg <= 300) {
                    $("#finalDaraja").text('A');
                } else if (finalAvg >= 181 && finalAvg <= 240) {
                    $("#finalDaraja").text('B');
                } else if (finalAvg >= 121 && finalAvg <= 180) {
                    $("#finalDaraja").text('C');
                } else if (finalAvg >= 61 && finalAvg <= 120) {
                    $("#finalDaraja").text('D');
                } else {
                    $("#finalDaraja").text('E');
                }
            } else {
                $("#averageMarksSum").text('0');
                $("#finalDaraja").text('---');
            }
        });

        function setEndDate() {
            var startDate = $("#startDate").val();
            $("#endDate").attr('min', startDate);
        }
    </script>
@endsection
