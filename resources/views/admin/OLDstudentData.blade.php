@extends('admin.layout')

@section('content')
    @php
        function assignGrade($marks){
            $rank=\App\Models\Ranks::select('rankName','rankRangeMin','rankRangeMax')->where([
                ['isActive','=','1'],
                ['isDeleted','=','0']
            ])->orderBy('rankName','asc')->get();

            if($rank){
                if($rank[0]['rankRangeMin']<$marks && $rank[0]['rankRangeMax']>=$marks){
                    return $rank[0]['rankName'];
                }
                else if($rank[1]['rankRangeMin']<$marks && $rank[1]['rankRangeMax']>=$marks){
                    return $rank[1]['rankName'];
                }
                else if($rank[2]['rankRangeMin']<$marks && $rank[2]['rankRangeMax']>=$marks){
                    return $rank[2]['rankName'];
                }
                else if($rank[3]['rankRangeMin']<$marks && $rank[3]['rankRangeMax']>=$marks){
                    return $rank[3]['rankName'];
                }
                else{
                    return $rank[4]['rankName'];
                }
            }
            else{
                return "Null";
            }
        }

        if($classId>4){
            function finalStatus($average){
                $rank=\App\Models\Ranks::select('rankName','rankRangeMin','rankRangeMax')->where([
                    ['isActive','=','1'],
                    ['isDeleted','=','0']
                ])->orderBy('rankName','asc')->get();

                if($average<=$rank[3]['rankRangeMax']){
                    return "FAIL";
                }
                else{
                    return "PASS";
                }
            }
        }
        else{
            function finalStatus($average){
                $rank=\App\Models\Ranks::select('rankName','rankRangeMin','rankRangeMax')->where([
                    ['isActive','=','1'],
                    ['isDeleted','=','0']
                ])->orderBy('rankName','asc')->get();

                if($average<=$rank[4]['rankRangeMax']){
                    return "FAIL";
                }
                else{
                    return "PASS";
                }
            }
        }
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

            <form action="{{ url('/filterStudentData') }}" method="post" id="filterForm">
                @csrf

                <div class="grid lg:grid-cols-7 md:grid-cols-4 grid-cols-1 gap-2">
                    <div>
                        <label for="class">Darasa:<span class="text-red-500">*</span></label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="class" id="class" required>
                            <option value="">-- SELECT CLASS --</option>
                            @if (count($classes)>0)
                                @foreach ($classes as $class)
                                    <option value="{{ $class['gradeId'] }}" @selected($classId==$class['gradeId'])>{{ $class['gradeName'] }}</option>
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
                            @if (count($exams)>0)
                                @foreach ($exams as $exam)
                                    <option value="{{ $exam['examId'] }}" @selected($examId==$exam['examId'])>{{ $exam['examName'] }}</option>
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
                            @if (count($regions)>0)
                                @foreach ($regions as $region)
                                    <option value="{{ $region['regionId'] }}" @selected($regionId==$region['regionId'])>{{ $region['regionName'] }} ({{ $region['regionCode'] }})</option>
                                @endforeach
                            @else
                                <option value="" class="text-red-500">No Data Found!</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="district">Wilaya:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="district" id="district">
                            <option value="">-- SELECT DISTRICT --</option>
                            @if (count($districts)>0)
                                @foreach ($districts as $district)
                                    <option value="{{ $district['districtId'] }}" @selected($districtId==$district['districtId'])>{{ $district['districtName'] }} ({{ $district['districtCode'] }})</option>
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
                            @if (count($wards)>0)
                                @foreach ($wards as $ward)
                                    <option value="{{ $ward['wardId'] }}" @selected($wardId==$ward['wardId'])>{{ $ward['wardName'] }} ({{ $ward['wardCode'] }})</option>
                                @endforeach
                            @else
                                <option value="" class="text-red-500">No Data Found!</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="startDate">Tarehe ya Kuanza:</label>
                        <input type="date" class="block w-full block p-2 rounded-md border border-black" min="{{ date('Y-m-d', strtotime("2023-01-01")) }}" max="{{ date('Y-m-d') }}" name="startDate" id="startDate" placeholder="Enter Start Date" value="{{ date('Y-m-d', strtotime($startDate)) }}" onchange="setEndDate()">
                    </div>

                    <div>
                        <label for="endDate">Tarehe ya Mwisho:</label>
                        <input type="date" class="block w-full block p-2 rounded-md border border-black" min="{{ date('Y-m-d', strtotime("2023-01-01")) }}" max="{{ date('Y-m-d') }}" name="endDate" id="endDate" placeholder="Enter End Date" value="{{ date('Y-m-d', strtotime($endDate)) }}">
                    </div>
                </div>
            </form>

            <div class="flex justify-end">
                <a href="{{ url('/admin-dashboard/student-data') }}"><button type="button" form="filterForm" class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">Onesha Upya</button></a>
                <button type="submit" form="filterForm" class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">Kichujio</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <h2 class="text-2xl font-bold mb-2">MATOKEO KWA MPANGILIO KWA WANAFUNZI WOTE:</h2>

            <table class="myTable bg-white">
                <thead>
                    <tr>
                        <th rowspan="2" class="border border-black">S/N</th>
                        <th rowspan="2" class="border border-black uppercase">Jinala Mwanafunzi</th>
                        <th rowspan="2" class="border border-black uppercase">Darasa</th>
                        <th rowspan="2" class="border border-black uppercase">Mtihani</th>
                        <th rowspan="2" class="border border-black uppercase">Shule</th>
                        <th rowspan="2" class="border border-black uppercase">Mkoa</th>
                        <th rowspan="2" class="border border-black uppercase">Wilaya</th>
                        <th rowspan="2" class="border border-black uppercase">Kata</th>
                        <th colspan="2" class="border border-black uppercase">Hisabati</th>
                        <th colspan="2" class="border border-black uppercase">Kiswahili</th>
                        <th colspan="2" class="border border-black uppercase">Sayansi</th>
                        <th colspan="2" class="border border-black uppercase">English</th>
                        <th colspan="2" class="border border-black uppercase">M/JAMII & S/KAZI</th>
                        <th colspan="2" class="border border-black uppercase">U/MAADILI</th>
                        <th rowspan="2" class="border border-black uppercase">Jumla</th>
                        <th rowspan="2" class="border border-black uppercase">Wastani</th>
                        <th rowspan="2" class="border border-black uppercase">Daraja</th>
                        <th rowspan="2" class="border border-black uppercase">Nafasi</th>
                        <th rowspan="2" class="border border-black uppercase">Ufaulu</th>
                    </tr>

                    <tr>
                        <th class="border border-black">AL</th>
                        <th class="border border-black">DRJ</th>
                        <th class="border border-black">AL</th>
                        <th class="border border-black">DRJ</th>
                        <th class="border border-black">AL</th>
                        <th class="border border-black">DRJ</th>
                        <th class="border border-black">AL</th>
                        <th class="border border-black">DRJ</th>
                        <th class="border border-black">AL</th>
                        <th class="border border-black">DRJ</th>
                        <th class="border border-black">AL</th>
                        <th class="border border-black">DRJ</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $i=1;
                        $j=0;
                        $storedAvg='';
                    @endphp
                    @foreach ($marks as $mark)
                        <tr class="odd:bg-gray-200">
                            <td class="border border-black text-right">{{ $i }}</td>
                            <td class="capitalize border border-black">{{ $mark['studentName'] }}</td>
                            <td class="capitalize border border-black">
                                @php
                                    $gradeData=\App\Models\Grades::select('gradeName')->where([
                                        ['gradeId','=',$mark['classId']]
                                    ])->first();

                                    $gradeName=($gradeData)?$gradeData['gradeName']:'<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $gradeName !!}</p>
                            </td>
                            <td class="capitalize border border-black">
                                @php
                                    $examData=\App\Models\Exams::select('examName')->where([
                                        ['examId','=',$mark['examId']]
                                    ])->first();

                                    $examName=($examData)?$examData['examName']:'<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $examName !!}</p>
                            </td>
                            <td class="capitalize border border-black">
                                @php
                                    $schoolData=\App\Models\Schools::select('schoolName')->where([
                                        ['schoolId','=',$mark['schoolId']]
                                    ])->first();

                                    $schoolName=($schoolData)?$schoolData['schoolName']:'<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $schoolName !!}</p>
                            </td>
                            <td class="capitalize border border-black">
                                @php
                                    $regionData=\App\Models\Regions::select('regionName')->where([
                                        ['regionId','=',$mark['regionId']]
                                    ])->first();

                                    $regionName=($regionData)?$regionData['regionName']:'<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $regionName !!}</p>
                            </td>
                            <td class="capitalize border border-black">
                                @php
                                    $districtData=\App\Models\Districts::select('districtName')->where([
                                        ['districtId','=',$mark['districtId']]
                                    ])->first();

                                    $districtName=($districtData)?$districtData['districtName']:'<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $districtName !!}</p>
                            </td>
                            <td class="capitalize border border-black">
                                @php
                                    $wardData=\App\Models\Wards::select('wardName')->where([
                                        ['wardId','=',$mark['wardId']]
                                    ])->first();

                                    $wardName=($wardData)?$wardData['wardName']:'<p class="text-red-500 italic">Not Found!</p>';
                                @endphp

                                <p>{!! $wardName !!}</p>
                            </td>
                            <td class="border border-black text-right">{{ $mark['hisabati'] }}</td>
                            <td class="border border-black">{{ assignGrade($mark['hisabati']) }}</td>
                            <td class="border border-black text-right">{{ $mark['kiswahili'] }}</td>
                            <td class="border border-black">{{ assignGrade($mark['kiswahili']) }}</td>
                            <td class="border border-black text-right">{{ $mark['sayansi'] }}</td>
                            <td class="border border-black">{{ assignGrade($mark['sayansi']) }}</td>
                            <td class="border border-black text-right">{{ $mark['english'] }}</td>
                            <td class="border border-black">{{ assignGrade($mark['english']) }}</td>
                            <td class="border border-black text-right">{{ $mark['jamii'] }}</td>
                            <td class="border border-black">{{ assignGrade($mark['jamii']) }}</td>
                            <td class="border border-black text-right">{{ $mark['maadili'] }}</td>
                            <td class="border border-black">{{ assignGrade($mark['maadili']) }}</td>
                            <td class="border border-black text-right">{{ $mark['total'] }}</td>
                            <td class="border border-black text-right">{{ $mark['average'] }}</td>

                            @if ($mark['average']>0)
                                <td class="border border-black">{{ assignGrade($mark['average']) }}</td>
                            @else
                                <td class="border border-black">ABS</td>
                            @endif

                            @if ($storedAvg==$mark['average'])
                                @php
                                    $j++;
                                    $storedAvg=$mark['average'];
                                @endphp

                                <td class="border border-black text-right">{{ ($i-$j) }}</td>
                            @else
                                @php
                                    $j=0;
                                    $storedAvg=$mark['average'];
                                @endphp

                                <td class="border border-black text-right">{{ $i }}</td>
                            @endif

                            @if ($mark['average']>0)
                                <td class="border border-black">{{ finalStatus($mark['average']) }}</td>
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
                {{-- <h2 class="text-2xl font-bold mb-2">School Average Grade</h2> --}}
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="border border-black p-1 uppercase">Wastani Ya Shule</th>
                            <th class="border border-black p-1 uppercase">Daraja</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white">
                            <td class="border border-black p-1 text-center">
                                @php
                                    $gATotal=0;
                                    foreach ($gAverage as $gA) {
                                        $gATotal=$gATotal+$gA;
                                    }

                                    $gAver=(count($marks)>0)?($gATotal/(6*(count($marks)-$gradeArray[10]-$gradeArray[11]))):0;
                                    $schoolGrade=assignGrade($gAver);
                                @endphp

                                {{ number_format($gAver, 2) }}
                            </td>
                            <td class="border border-black p-1 text-center">{{ $schoolGrade }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                {{-- <h2 class="text-2xl font-bold mb-2">School Average Passing</h2> --}}
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="border border-black p-1 uppercase">Wastani Ya Ufaulu</th>
                            <th class="border border-black p-1 uppercase">Daraja</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="bg-white">
                            <td class="border border-black p-1 text-center">
                                @php
                                    $gATotal=0;
                                    foreach ($gAverage as $gA) {
                                        $gATotal=$gATotal+$gA;
                                    }

                                    $gAver=(count($marks)>0)?($gATotal/(count($marks)-$gradeArray[10]-$gradeArray[11])):0;
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
                {{-- <h2 class="text-2xl font-bold mb-2">Average Grade Distribution</h2> --}}
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
                        if($classId>4){
                            $failCount=$gradeArray[3]+$gradeArray[4]+$gradeArray[8]+$gradeArray[9];
                            $failMaleCount=$gradeArray[3]+$gradeArray[4];
                            $failFemaleCount=$gradeArray[8]+$gradeArray[9];
                        }
                        else{
                            $failCount=$gradeArray[4]+$gradeArray[9];
                            $failMaleCount=$gradeArray[4];
                            $failFemaleCount=$gradeArray[9];
                        }

                        $gradeCount=array_sum($gradeArray)-$gradeArray[10]-$gradeArray[11];
                        $gradeMaleCount=$gradeArray[0]+$gradeArray[1]+$gradeArray[2]+$gradeArray[3]+$gradeArray[4];
                        $gradeFemaleCount=$gradeArray[5]+$gradeArray[6]+$gradeArray[7]+$gradeArray[8]+$gradeArray[9];
                    @endphp

                    <tr class="bg-white">
                        <td class="border border-black text-center">1</td>
                        <td class="border border-black text-center">{{ $gradeArray[0] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[1] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[2] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[3] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[4] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[10] }}</td>
                        <td class="border border-black text-center">{{ $gradeMaleCount+$gradeArray[10] }}</td>
                    </tr>

                    <tr class="bg-gray-200">
                        <td class="border border-black text-center">2</td>
                        <td class="border border-black text-center">{{ $gradeArray[5] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[6] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[7] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[8] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[9] }}</td>
                        <td class="border border-black text-center">{{ $gradeArray[11] }}</td>
                        <td class="border border-black text-center">{{ $gradeFemaleCount+$gradeArray[11] }}</td>
                    </tr>

                    <tr class="bg-white">
                        <td class="border border-black text-center">Jumla</td>
                        <td class="border border-black text-center">{{ ($gradeArray[0]+$gradeArray[5]) }}</td>
                        <td class="border border-black text-center">{{ ($gradeArray[1]+$gradeArray[6]) }}</td>
                        <td class="border border-black text-center">{{ ($gradeArray[2]+$gradeArray[7]) }}</td>
                        <td class="border border-black text-center">{{ ($gradeArray[3]+$gradeArray[8]) }}</td>
                        <td class="border border-black text-center">{{ ($gradeArray[4]+$gradeArray[9]) }}</td>
                        <td class="border border-black text-center">{{ ($gradeArray[10]+$gradeArray[11]) }}</td>
                        <td class="border border-black text-center">{{ ($gradeMaleCount+$gradeFemaleCount+$gradeArray[10]+$gradeArray[11]) }}</td>
                    </tr>
                </table>
            </div>

            <div>
                {{-- <h2 class="text-2xl font-bold mb-2">Exam Participation & Results</h2> --}}
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
                            <td class="border border-black px-2">1</td>
                            <td class="border border-black px-2">{{ $gradeMaleCount }}</td>
                            <td class="border border-black px-2">{{ ($gradeMaleCount-$failMaleCount) }}</td>
                            <td class="border border-black px-2">{{ $failMaleCount }}</td>
                        </tr>

                        <tr class="bg-gray-200 text-center">
                            <td class="border border-black px-2">2</td>
                            <td class="border border-black px-2">{{ $gradeFemaleCount }}</td>
                            <td class="border border-black px-2">{{ ($gradeFemaleCount-$failFemaleCount) }}</td>
                            <td class="border border-black px-2">{{ $failFemaleCount }}</td>
                        </tr>

                        <tr class="bg-white text-center">
                            <td class="border border-black px-2" rowspan="2">Jumla</td>
                            <td class="border border-black px-2" rowspan="2">{{ $gradeCount }}</td>
                            <td class="border border-black px-2">{{ ($gradeCount-$failCount) }}</td>
                            <td class="border border-black px-2">{{ $failCount }}</td>
                        </tr>

                        <tr class="bg-gray-200 text-center">
                            <td class="border border-black px-2">
                                @php
                                    $passTitle=($classId>4)?"% Pass(A-C)":"% Pass(A-D)";
                                @endphp

                                @if ($gradeCount>0)
                                    <span>{{ $passTitle }}:</span> {{ number_format(((($gradeCount-$failCount)*100)/$gradeCount), 2)  }}
                                @else
                                    <p>{{ $passTitle }}: 0</p>
                                @endif
                            </td>

                            <td class="border border-black px-2">
                                @php
                                    $failTitle=($classId>4)?"% Fail(D-E)":"% Fail(E)";
                                @endphp

                                @if ($gradeCount>0)
                                    <span>{{ $failTitle }}:</span> {{ number_format((($failCount*100)/$gradeCount), 2) }}
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
                $failedCount=0;
                $subList=['hisabati','kiswahili','sayansi','english','jamii','maadili'];
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
                    @if (count($subList)>0)
                        @php
                            $i=0;
                            $rowColor=($i%2==0)?"bg-white":"bg-gray-200";
                            $failCount=($classId>4)?(array_sum($dMaleGrade)+array_sum($eMaleGrade)+array_sum($dFemaleGrade)+array_sum($eFemaleGrade)):(array_sum($eMaleGrade)+array_sum($dMaleGrade));
                        @endphp
                        @foreach ($subList as $name)
                            @php
                                $totalGradeCount=$aMaleGrade[$i]+$bMaleGrade[$i]+$cMaleGrade[$i]+$dMaleGrade[$i]+$eMaleGrade[$i]+$aFemaleGrade[$i]+$bFemaleGrade[$i]+$cFemaleGrade[$i]+$dFemaleGrade[$i]+$eFemaleGrade[$i];
                            @endphp

                            <tr class="{{ $rowColor }}">
                                <td class="pl-2 border border-black capitalize">{{ $name }}</td>
                                <td class="text-center border border-black px-2">{{ $aMaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ $aFemaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ ($aMaleGrade[$i]+$aFemaleGrade[$i]) }}</td>
                                <td class="text-center border border-black px-2">{{ $bMaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ $bFemaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ ($bMaleGrade[$i]+$bFemaleGrade[$i]) }}</td>
                                <td class="text-center border border-black px-2">{{ $cMaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ $cFemaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ ($cMaleGrade[$i]+$cFemaleGrade[$i]) }}</td>
                                <td class="text-center border border-black px-2">{{ $dMaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ $dFemaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ ($dMaleGrade[$i]+$dFemaleGrade[$i]) }}</td>
                                <td class="text-center border border-black px-2">{{ $eMaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ $eFemaleGrade[$i] }}</td>
                                <td class="text-center border border-black px-2">{{ ($eMaleGrade[$i]+$eFemaleGrade[$i]) }}</td>

                                @if (count($marks)>0)
                                    <td class="text-center border border-black">{{ number_format(($gAverage[$i]/(count($marks)-$gradeArray[10]-$gradeArray[11])), 2) }}</td>
                                @else
                                    <td class="text-center border border-black">0</td>
                                @endif

                                <td class="text-center border border-black">{{ ($totalGradeCount-$failedCount) }}</td>
                                <td class="text-center border border-black">
                                    @if ($totalGradeCount>0)
                                        {{ number_format(((($totalGradeCount-$failedCount)*100)/$totalGradeCount), 2) }}
                                    @else
                                        <p>0</p>
                                    @endif
                                </td>
                                <td class="text-center border border-black">{{ $failedCount }}</td>
                                <td class="text-center border border-black">
                                    @if ($totalGradeCount>0)
                                        {{ number_format(((($failedCount)*100)/$totalGradeCount), 2) }}
                                    @else
                                        <p>0</p>
                                    @endif
                                </td>
                            </tr>

                            @php
                                $i++;
                            @endphp
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
@endsection
