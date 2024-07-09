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
    @endphp

    <div class="p-3">
        <div class="flex justify-end">
            <form action="{{ url('/downloadTeacherSubjectReport') }}" method="post">
                @csrf
                
                <input type="hidden" name="rClass" id="rClass" value="{{ $classId }}">
                <input type="hidden" name="rExam" id="rExam" value="{{ $examId }}">
                <input type="hidden" name="rStartDate" id="rStartDate" value="{{ $startDate }}">
                <input type="hidden" name="rEndDate" id="rEndDate" value="{{ $endDate }}">
                <input type="hidden" name="rBorderline" id="rBorderline" value="{{ $borderLine }}">

                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded-md mr-1">
                    <i class="material-symbols-outlined text-sm">download</i> <span>Pakua Kiolezo</span>
                </button>
            </form>
        </div>
        
        <div class="my-3">
            <h2 class="text-2xl font-bold">Kichujio:</h2>
            
            <form action="{{ url('/filterTeacherSubjectReport') }}" method="post" id="filterForm">
                @csrf

                <div class="grid lg:grid-cols-4 md:grid-cols-4 grid-cols-1 gap-2">
                    <div>
                        <label for="class">Chagua Darasa:<span class="text-red-500">*</span></label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="class" id="class" required>
                            <option value="">-- CHAGUA DARASA --</option>
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
                        <label for="exam">Chagua Mtihani:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="exam" id="exam">
                            <option value="">-- CHAGUA MTIHANI --</option>
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
                <a href="{{ url('/dashboard/teacher-subject-report') }}"><button type="button" form="filterForm" class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">Onesha Upya</button></a>
                <button type="submit" form="filterForm" class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">Kichujio</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <h2 class="text-2xl font-bold mb-2 uppercase">Ripoti Kimasomo:</h2>

            @php
                $gradeArray=[];
                $subList=['hisabati','kiswahili','sayansi','english','jamii','maadili'];
            @endphp
    
            @foreach ($allMarks as $aMark)
                @php
                    if($aMark['total']!=0){
                        foreach ($subList as $list) {
                            if(assignGrade($aMark[$list])=='A'){
                                array_push($gradeArray, ''.substr($list, 0, 1).'A');
                            }
                            else if(assignGrade($aMark[$list])=='B'){
                                array_push($gradeArray, ''.substr($list, 0, 1).'B');
                            }
                            else if(assignGrade($aMark[$list])=='C'){
                                array_push($gradeArray, ''.substr($list, 0, 1).'C');
                            }
                            else if(assignGrade($aMark[$list])=='D'){
                                array_push($gradeArray, ''.substr($list, 0, 1).'D');
                            }
                            else{
                                array_push($gradeArray, ''.substr($list, 0, 1).'E');
                            }
                        }
                    }
                @endphp
            @endforeach

            <table class="myTable bg-white">
                <thead>
                    <tr>
                        <th class="border border-black uppercase" rowspan="2">NA</th>
                        <th class="border border-black uppercase" rowspan="2">Mkoa</th>
                        <th class="border border-black uppercase" rowspan="2">Wilaya</th>
                        <th class="border border-black uppercase" rowspan="2">Kata</th>
                        <th class="border border-black uppercase" rowspan="2">Shule</th>
                        <th class="border border-black uppercase" colspan="3" rowspan="1">WALIOFANYA</th>
                        <th class="border border-black uppercase" colspan="6" rowspan="1">Hisabati</th>
                        <th class="border border-black uppercase" colspan="6" rowspan="1">Kiswahili</th>
                        <th class="border border-black uppercase" colspan="6" rowspan="1">Sayansi</th>
                        <th class="border border-black uppercase" colspan="6" rowspan="1">English</th>
                        <th class="border border-black uppercase" colspan="6" rowspan="1">M/JAMII & S/KAZI</th>
                        <th class="border border-black uppercase" colspan="6" rowspan="1">U/MAADILI</th>
                    </tr>

                    <tr>
                        <th class="border border-black">WAV</th>
                        <th class="border border-black">WAS</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">A</th>
                        <th class="border border-black">B</th>
                        <th class="border border-black">C</th>
                        <th class="border border-black">D</th>
                        <th class="border border-black">E</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">A</th>
                        <th class="border border-black">B</th>
                        <th class="border border-black">C</th>
                        <th class="border border-black">D</th>
                        <th class="border border-black">E</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">A</th>
                        <th class="border border-black">B</th>
                        <th class="border border-black">C</th>
                        <th class="border border-black">D</th>
                        <th class="border border-black">E</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">A</th>
                        <th class="border border-black">B</th>
                        <th class="border border-black">C</th>
                        <th class="border border-black">D</th>
                        <th class="border border-black">E</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">A</th>
                        <th class="border border-black">B</th>
                        <th class="border border-black">C</th>
                        <th class="border border-black">D</th>
                        <th class="border border-black">E</th>
                        <th class="border border-black">JML</th>

                        <th class="border border-black">A</th>
                        <th class="border border-black">B</th>
                        <th class="border border-black">C</th>
                        <th class="border border-black">D</th>
                        <th class="border border-black">E</th>
                        <th class="border border-black">JML</th>
                    </tr>
                </thead>
    
                <tbody>
                    @if (count($allMarks)>0)
                        @php
                        $g=0;
                        $groupArray = array_count_values($gradeArray); 
                        $rowColor=($g%2==0)?"bg-white":"bg-gray-200";

                        $regionData=\App\Models\Regions::find($allMarks[0]['regionId']);
                        $regionName=($regionData)?$regionData['regionName']:'<span class="text-red-500 italic">Not Found!</span>';

                        $districtData=\App\Models\Districts::find($allMarks[0]['districtId']);
                        $districtName=($districtData)?$districtData['districtName']:'<span class="text-red-500 italic">Not Found!</span>';

                        $wardData=\App\Models\Wards::find($allMarks[0]['wardId']);
                        $wardName=($wardData)?$wardData['wardName']:'<span class="text-red-500 italic">Not Found!</span>';

                        $schoolData=\App\Models\Schools::find($allMarks[0]['schoolId']);
                        $schoolName=($schoolData)?$schoolData['schoolName']:'<span class="text-red-500 italic">Not Found!</span>';

                        $malePassed=\App\Models\Marks::where([
                            ['isActive','=','1'],
                            ['isDeleted','=','0'],
                            ['gender','=','M'],
                            ['schoolId','=',$allMarks[0]['schoolId']],
                            ['classId','=',$classId],
                            ['examId','=',$examId],
                            ['average','!=','0']
                        ])->whereBetween('examDate', [$startDate, $endDate])->count();

                        $femalePassed=\App\Models\Marks::where([
                            ['isActive','=','1'],
                            ['isDeleted','=','0'],
                            ['gender','=','F'],
                            ['schoolId','=',$allMarks[0]['schoolId']],
                            ['classId','=',$classId],
                            ['examId','=',$examId],
                            ['average','!=','0']
                        ])->whereBetween('examDate', [$startDate, $endDate])->count();
                    @endphp

                    <tr class="{{ $rowColor }}">
                        <td class="border border-black">1</td>
                        <td class="border border-black">{!! $regionName !!}</td>
                        <td class="border border-black">{!! $districtName !!}</td>
                        <td class="border border-black">{!! $wardName !!}</td>
                        <td class="border border-black">{!! $schoolName !!}</td>

                        <td class="border border-black totalFailMale col1">{{ $malePassed }}</td>
                        <td class="border border-black totalFailFemale col2">{{ $femalePassed }}</td>
                        <td class="border border-black col3">{{ ($malePassed+$femalePassed) }}</td>

                        @php
                            $y=4;
                        @endphp
                        @foreach ($subList as $name)
                            <td class="text-center border border-black px-2 col{{ $y }}">{{ (array_key_exists(''.substr($name, 0, 1).'A', $groupArray))?$groupArray[''.substr($name, 0, 1).'A']:0 }}</td>
                            <td class="text-center border border-black px-2 col{{ ($y+1) }}">{{ (array_key_exists(''.substr($name, 0, 1).'B', $groupArray))?$groupArray[''.substr($name, 0, 1).'B']:0 }}</td>
                            <td class="text-center border border-black px-2 col{{ ($y+2) }}">{{ (array_key_exists(''.substr($name, 0, 1).'C', $groupArray))?$groupArray[''.substr($name, 0, 1).'C']:0 }}</td>
                            <td class="text-center border border-black px-2 col{{ ($y+3) }}">{{ (array_key_exists(''.substr($name, 0, 1).'D', $groupArray))?$groupArray[''.substr($name, 0, 1).'D']:0 }}</td>
                            <td class="text-center border border-black px-2 col{{ ($y+4) }}">{{ (array_key_exists(''.substr($name, 0, 1).'E', $groupArray))?$groupArray[''.substr($name, 0, 1).'E']:0 }}</td>
                            <td class="text-center border border-black px-2 col{{ ($y+5) }}">{{ (((array_key_exists(''.substr($name, 0, 1).'A', $groupArray))?$groupArray[''.substr($name, 0, 1).'A']:0)+((array_key_exists(''.substr($name, 0, 1).'B', $groupArray))?$groupArray[''.substr($name, 0, 1).'B']:0)+((array_key_exists(''.substr($name, 0, 1).'C', $groupArray))?$groupArray[''.substr($name, 0, 1).'C']:0)+((array_key_exists(''.substr($name, 0, 1).'D', $groupArray))?$groupArray[''.substr($name, 0, 1).'D']:0)+((array_key_exists(''.substr($name, 0, 1).'E', $groupArray))?$groupArray[''.substr($name, 0, 1).'E']:0)) }}</td>
                            
                            @php
                                $y=$y+6;
                            @endphp
                        @endforeach
                    </tr>

                    <tr class="font-bold">
                        <td class="text-center border-y border-l border-black px-2 text-white">Jumla</td>
                        <td class="text-center border-y border-black px-2"></td>
                        <td class="text-center border-y border-black px-2">Jumla</td>
                        <td class="text-center border-y border-black px-2"></td>
                        <td class="text-center border-y border-r border-black px-2"></td>
                        <td class="text-center border border-black px-2" id="col1Jumla"></td>
                        <td class="text-center border border-black px-2" id="col2Jumla"></td>
                        <td class="text-center border border-black px-2" id="col3Jumla"></td>
                        <td class="text-center border border-black px-2" id="col4Jumla"></td>
                        <td class="text-center border border-black px-2" id="col5Jumla"></td>
                        <td class="text-center border border-black px-2" id="col6Jumla"></td>
                        <td class="text-center border border-black px-2" id="col7Jumla"></td>
                        <td class="text-center border border-black px-2" id="col8Jumla"></td>
                        <td class="text-center border border-black px-2" id="col9Jumla"></td>
                        <td class="text-center border border-black px-2" id="col10Jumla"></td>
                        <td class="text-center border border-black px-2" id="col11Jumla"></td>
                        <td class="text-center border border-black px-2" id="col12Jumla"></td>
                        <td class="text-center border border-black px-2" id="col13Jumla"></td>
                        <td class="text-center border border-black px-2" id="col14Jumla"></td>
                        <td class="text-center border border-black px-2" id="col15Jumla"></td>
                        <td class="text-center border border-black px-2" id="col16Jumla"></td>
                        <td class="text-center border border-black px-2" id="col17Jumla"></td>
                        <td class="text-center border border-black px-2" id="col18Jumla"></td>
                        <td class="text-center border border-black px-2" id="col19Jumla"></td>
                        <td class="text-center border border-black px-2" id="col20Jumla"></td>
                        <td class="text-center border border-black px-2" id="col21Jumla"></td>
                        <td class="text-center border border-black px-2" id="col22Jumla"></td>
                        <td class="text-center border border-black px-2" id="col23Jumla"></td>
                        <td class="text-center border border-black px-2" id="col24Jumla"></td>
                        <td class="text-center border border-black px-2" id="col25Jumla"></td>
                        <td class="text-center border border-black px-2" id="col26Jumla"></td>
                        <td class="text-center border border-black px-2" id="col27Jumla"></td>
                        <td class="text-center border border-black px-2" id="col28Jumla"></td>
                        <td class="text-center border border-black px-2" id="col29Jumla"></td>
                        <td class="text-center border border-black px-2" id="col30Jumla"></td>
                        <td class="text-center border border-black px-2" id="col31Jumla"></td>
                        <td class="text-center border border-black px-2" id="col32Jumla"></td>
                        <td class="text-center border border-black px-2" id="col33Jumla"></td>
                        <td class="text-center border border-black px-2" id="col34Jumla"></td>
                        <td class="text-center border border-black px-2" id="col35Jumla"></td>
                        <td class="text-center border border-black px-2" id="col36Jumla"></td>
                        <td class="text-center border border-black px-2" id="col37Jumla"></td>
                        <td class="text-center border border-black px-2" id="col38Jumla"></td>
                        <td class="text-center border border-black px-2" id="col39Jumla"></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        function setEndDate(){
            var startDate=$("#startDate").val();
            $("#endDate").attr('min', startDate);
        }

        for (let i = 1; i <=39; i++) {
            let colSum=0;
            $(`.col${i}`).each(function() {
                var value = parseInt($(this).text());
                if (!isNaN(value)) {
                    colSum += value;
                }
            });

            $(`#col${i}Jumla`).text(colSum);
        }
    </script>
@endsection