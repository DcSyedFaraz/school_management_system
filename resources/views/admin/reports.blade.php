@extends('admin.layout')

@section('content')
    @php
        $subjects = [];
        $subjects = config('subjects.' . $classId, config('subjects.class_default'));

        // Fixed grade -> color mapping, shared by every grade cell/header on this page.
        $gradeColorMap = [
            'A' => 'bg-green-100 text-green-800',
            'B' => 'bg-lime-100 text-lime-800',
            'C' => 'bg-yellow-100 text-yellow-800',
            'D' => 'bg-orange-100 text-orange-800',
            'E' => 'bg-red-100 text-red-800',
            'ABS' => 'bg-gray-200 text-gray-500 italic',
        ];
        // Value-conditional tier for a "higher is better" percentage (0-100): high is green, mid is amber, low is red.
        $passPctColor = fn($pct) => $pct >= 80 ? 'text-green-600' : ($pct >= 50 ? 'text-amber-600' : 'text-red-600');
    @endphp

    <div class="p-3">
        <div class="flex justify-end">
            <form action="{{ url('/downloadReport') }}" method="post">
                @csrf

                <input type="hidden" name="rClass" id="rClass" value="{{ $classId }}">
                <input type="hidden" name="rExam" id="rExam" value="{{ $examId }}">
                <input type="hidden" name="rStartDate" id="rStartDate" value="{{ $startDate }}">
                <input type="hidden" name="rEndDate" id="rEndDate" value="{{ $endDate }}">
                <input type="hidden" name="rRegion" id="rRegion" value="{{ $regionId }}">
                <input type="hidden" name="rDistrict" id="rDistrict" value="{{ $districtId }}">

                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded-md mr-1">
                    <i class="material-symbols-outlined text-sm">download</i> <span>Pakia Kiolezo</span>
                </button>
            </form>
        </div>

        <div class="my-3">
            <h2 class="text-2xl font-bold">Kichujio:</h2>

            <form action="{{ url('/filterReport') }}" method="post" id="filterForm">
                @csrf

                <div class="grid lg:grid-cols-6 md:grid-cols-3 grid-cols-1 gap-2">
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
                <a href="{{ url('/admin-dashboard/reports') }}"><button type="button" form="filterForm"
                        class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">Onesha
                        Upya</button></a>
                <button type="submit" form="filterForm"
                    class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">Kichujio</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <h2 class="text-2xl font-bold mb-2">MATOKEO KWA MPANGILIO WA SHULE ZOTE:</h2>
            <table class="myTable bg-white text-xs">
                <thead>
                    <tr>
                        <th rowspan="2" class="border border-black">S/N</th>
                        <th rowspan="2" class="border border-black uppercase">Jina La Shule</th>
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
                        $aCount = 0;
                        $bCount = 0;
                        $cCount = 0;
                        $dCount = 0;
                        $eCount = 0;

                        $gAverage = array_fill(0, count($subjects), 0);
                    @endphp

                    @foreach ($marks as $mark)
                        @php
                            $totalMarks = 0;
                            foreach ($subjects as $key => $subject) {
                                $totalMarks += $mark[$subject];
                                $gAverage[$key] += $mark[$subject];
                            }

                            $rowGrade = Grading::gradeTotal($mark['avgTotal']);
                            if ($rowGrade == 'A') {
                                $aCount++;
                            } elseif ($rowGrade == 'B') {
                                $bCount++;
                            } elseif ($rowGrade == 'C') {
                                $cCount++;
                            } elseif ($rowGrade == 'D') {
                                $dCount++;
                            } else {
                                $eCount++;
                            }
                        @endphp

                        <tr class="odd:bg-gray-200 even:bg-white">
                            <td class="border border-black text-right">{{ $i }}</td>
                            <td class="capitalize border border-black">
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
                            @foreach ($subjects as $subject)
                                @if ($mark[$subject] === null)
                                    <td class="border border-black text-center {{ $gradeColorMap['ABS'] }}">ABS</td>
                                    <td class="border border-black text-center {{ $gradeColorMap['ABS'] }}">ABS</td>
                                @else
                                    @php $subjectGrade = Grading::gradeSubject($mark[$subject]); @endphp
                                    <td class="border border-black text-right">{{ number_format($mark[$subject], 2) }}</td>
                                    <td class="border border-black {{ $gradeColorMap[$subjectGrade] ?? '' }}">{{ $subjectGrade }}</td>
                                @endif
                            @endforeach
                            <td class="border border-black text-right">{{ number_format($totalMarks, 2) }}</td>
                            <td class="border border-black text-right">{{ $mark['avgTotal'] }}</td>
                            <td class="border border-black {{ $gradeColorMap[$rowGrade] ?? '' }}">{{ $rowGrade }}</td>

                            @if ($storedAvg == $mark['avgTotal'])
                                @php
                                    $j++;
                                    $storedAvg = $mark['avgTotal'];
                                @endphp

                                <td class="border border-black text-right">{{ $i - $j }}</td>
                            @else
                                @php
                                    $j = 0;
                                    $storedAvg = $mark['avgTotal'];
                                @endphp

                                <td class="border border-black text-right">{{ $i }}</td>
                            @endif

                            @php
                                $status = Grading::statusForTotal($mark['avgTotal'], $classId);
                                $statusColor = $status === 'FAULU' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold';
                            @endphp
                            <td class="border border-black {{ $statusColor }}">{{ $status }}</td>
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
                {{-- <h2 class="text-2xl font-bold mb-2">School Average Grade</h2> --}}
                <table class="w-full text-xs">
                    <thead>
                        <tr>
                            <th class="border border-black p-1 uppercase">Wastani Ya Daraja</th>
                            <th class="border border-black p-1 uppercase">Daraja</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white">
                            <td class="border border-black p-1 text-center">
                                @php
                                    $gATotal = array_sum($gAverage);
                                    $gAver =
                                        count($marks) > 0 && count($subjects) > 0
                                            ? $gATotal / (count($subjects) * count($marks))
                                            : 0;
                                    $meanTotal = count($marks) > 0 ? $gATotal / count($marks) : 0;
                                    $schoolGrade = Grading::gradeTotal($meanTotal);
                                @endphp

                                {{ number_format($gAver, 2) }}
                            </td>
                            <td class="border border-black p-1 text-center {{ $gradeColorMap[$schoolGrade] ?? '' }}">{{ $schoolGrade }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                {{-- <h2 class="text-2xl font-bold mb-2">School Average Passing</h2> --}}
                <table class="w-full text-xs">
                    <thead>
                        <tr>
                            <th class="border border-black p-1 uppercase">Wastani Ya Ufaulu</th>
                            <th class="border border-black p-1 uppercase">Daraja</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white">
                            <td class="border border-black p-1 text-center">
                                {{ number_format($meanTotal, 2) }}
                            </td>
                            <td class="border border-black p-1 text-center {{ $gradeColorMap[$schoolGrade] ?? '' }}">{{ $schoolGrade }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-2 mt-5">
            <div>
                {{-- <h2 class="text-2xl font-bold mb-2">Average Grade Distribution</h2> --}}
                <table class="w-full text-xs">
                    <tr>
                        <th rowspan="3" class="text-center border border-black">TATHIMINI YA UFAULU</th>
                        <th colspan="6" class="text-center border border-black uppercase">Daraja</th>
                    </tr>

                    <tr>
                        <th class="border border-black {{ $gradeColorMap['A'] }}">A</th>
                        <th class="border border-black {{ $gradeColorMap['B'] }}">B</th>
                        <th class="border border-black {{ $gradeColorMap['C'] }}">C</th>
                        <th class="border border-black {{ $gradeColorMap['D'] }}">D</th>
                        <th class="border border-black {{ $gradeColorMap['E'] }}">E</th>
                        <th class="border border-black uppercase">Jumla</th>
                    </tr>

                    @php
                        if ($classId > 4) {
                            $failCount = $dCount + $eCount;
                        } else {
                            $failCount = $eCount;
                        }

                        $gradeCount = $aCount + $bCount + $cCount + $dCount + $eCount;
                    @endphp

                    <tr>
                        <td class="border border-black text-center">{{ $aCount }}</td>
                        <td class="border border-black text-center">{{ $bCount }}</td>
                        <td class="border border-black text-center">{{ $cCount }}</td>
                        <td class="border border-black text-center">{{ $dCount }}</td>
                        <td class="border border-black text-center">{{ $eCount }}</td>
                        <td class="border border-black text-center">{{ $gradeCount }}</td>
                    </tr>
                </table>
            </div>

            <div>
                {{-- <h2 class="text-2xl font-bold mb-2">Exam Participation & Results</h2> --}}
                <table class="w-full text-xs">
                    <thead>
                        <tr>
                            <th colspan="2" class="border border-black px-2 text-center">WALIOSAJIRIWA</th>
                            <th class="border border-black px-2 text-center uppercase bg-green-100 text-green-800">Pass</th>
                            <th class="border border-black px-2 text-center uppercase bg-red-100 text-red-800">Fail</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="text-center">
                            <td class="border border-black px-2 uppercase" rowspan="2">Jumla</td>
                            <td class="border border-black px-2" rowspan="2">{{ $gradeCount }}</td>
                            <td class="border border-black px-2">{{ $gradeCount - $failCount }}</td>
                            <td class="border border-black px-2">{{ $failCount }}</td>
                        </tr>

                        <tr class="text-center">
                            @php
                                $passTitle = $classId > 4 ? '% Pass(A-C)' : '% Pass(A-D)';
                                $failTitle = $classId > 4 ? '% Fail(D-E)' : '% Fail(E)';
                                $waliosajiriwaPassPct = $gradeCount > 0 ? (($gradeCount - $failCount) * 100) / $gradeCount : 0;
                            @endphp
                            <td class="border border-black px-2 font-semibold {{ $passPctColor($waliosajiriwaPassPct) }}">
                                @if ($gradeCount > 0)
                                    <span>{{ $passTitle }}:</span>
                                    {{ number_format($waliosajiriwaPassPct, 2) }}
                                @else
                                    <p>{{ $passTitle }}: 0</p>
                                @endif
                            </td>

                            <td class="border border-black px-2 font-semibold {{ $passPctColor($waliosajiriwaPassPct) }}">
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
                $gradeArray = [];
                $failedCount = 0;
            @endphp

            @foreach ($marks as $aMark)
                @php
                    foreach ($subjects as $subject) {
                        $gradeArray[] = substr($subject, 0, 1) . Grading::gradeSubject($aMark[$subject]);
                    }
                @endphp
            @endforeach

            <h2 class="text-2xl font-bold mb-2 text-center">TATHIMINI YA MADARAJA YA KILA SOMO</h2>
            <table class="w-full text-xs">
                <thead>
                    <tr>
                        <th class="text-center border border-black uppercase">Somo</th>
                        <th class="text-center border border-black {{ $gradeColorMap['A'] }}">A</th>
                        <th class="text-center border border-black {{ $gradeColorMap['B'] }}">B</th>
                        <th class="text-center border border-black {{ $gradeColorMap['C'] }}">C</th>
                        <th class="text-center border border-black {{ $gradeColorMap['D'] }}">D</th>
                        <th class="text-center border border-black {{ $gradeColorMap['E'] }}">E</th>
                        <th class="text-center border border-black uppercase">Wastani Ya Somo</th>
                        <th class="text-center border border-black uppercase">Walio Faulu</th>
                        <th class="text-center border border-black">%</th>
                        <th class="text-center border border-black uppercase">Wasio Faulu</th>
                        <th class="text-center border border-black">%</th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($subjects) > 0)
                        @foreach ($subjects as $key => $subject)
                            @php
                                $rowColor = $key % 2 == 0 ? 'bg-white' : 'bg-gray-200';
                                $totalMarks = 0;
                                $totalStudents = 0;
                                $aCount = 0;
                                $bCount = 0;
                                $cCount = 0;
                                $dCount = 0;
                                $eCount = 0;
                            @endphp

                            @foreach ($marks as $mark)
                                @php
                                    $totalMarks += $mark[$subject];
                                    $totalStudents++;
                                    $subjGrade = Grading::gradeSubject($mark[$subject]);
                                    if ($subjGrade == 'A') {
                                        $aCount++;
                                    } elseif ($subjGrade == 'B') {
                                        $bCount++;
                                    } elseif ($subjGrade == 'C') {
                                        $cCount++;
                                    } elseif ($subjGrade == 'D') {
                                        $dCount++;
                                    } else {
                                        $eCount++;
                                    }
                                @endphp
                            @endforeach

                            @php
                                $failedCount = $classId > 4 ? $dCount + $eCount : $eCount;
                            @endphp

                            <tr class="{{ $rowColor }}">
                                <td class="pl-2 border border-black capitalize">{{ $subject }}</td>
                                <td class="text-center border border-black px-2">{{ $aCount }}</td>
                                <td class="text-center border border-black px-2">{{ $bCount }}</td>
                                <td class="text-center border border-black px-2">{{ $cCount }}</td>
                                <td class="text-center border border-black px-2">{{ $dCount }}</td>
                                <td class="text-center border border-black px-2">{{ $eCount }}</td>
                                @php
                                    $subjectPassPct = $totalStudents > 0 ? (($totalStudents - $failedCount) * 100) / $totalStudents : 0;
                                @endphp
                                <td class="text-center border border-black">
                                    {{ $totalStudents > 0 ? number_format($totalMarks / $totalStudents, 2) : 0 }}
                                </td>
                                <td class="text-center border border-black">{{ $totalStudents - $failedCount }}</td>
                                <td class="text-center border border-black font-semibold {{ $passPctColor($subjectPassPct) }}">
                                    {{ number_format($subjectPassPct, 2) }}
                                </td>
                                <td class="text-center border border-black">{{ $failedCount }}</td>
                                <td class="text-center border border-black font-semibold {{ $passPctColor($subjectPassPct) }}">
                                    {{ $totalStudents > 0 ? number_format(($failedCount * 100) / $totalStudents, 2) : 0 }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="text-red-500 text-center p-2" colspan="6">No Data Found!</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function setEndDate() {
            var startDate = $("#startDate").val();
            $("#endDate").attr('min', startDate);
        }
    </script>

@endsection
