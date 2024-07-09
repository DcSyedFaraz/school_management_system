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
                <a href="{{ url('/admin-dashboard/reports') }}"><button type="button" form="filterForm" class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">Onesha Upya</button></a>
                <button type="submit" form="filterForm" class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">Kichujio</button>
            </div>
        </div>
    
        <div class="overflow-x-auto">
            <h2 class="text-2xl font-bold mb-2">MATOKEO KWA MPANGILIO WA SHULE ZOTE:</h2>
            <table class="myTable bg-white">
                <thead>
                    <tr>
                        <th rowspan="2" class="border border-black">S/N</th>
                        <th rowspan="2" class="border border-black uppercase">Jina La Shule</th>
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
                        $aCount=0;
                        $bCount=0;   
                        $cCount=0;   
                        $dCount=0;   
                        $eCount=0;
    
                        $gAverage=[0,0,0,0,0,0];
                    @endphp
    
                    @foreach ($marks as $mark)
                        @php
                            $totalMarks=$mark['hisabati']+$mark['kiswahili']+$mark['sayansi']+$mark['english']+$mark['jamii']+$mark['maadili'];
    
                            $gAverage[0]=$gAverage[0]+($mark['hisabati']);
                            $gAverage[1]=$gAverage[1]+($mark['kiswahili']);
                            $gAverage[2]=$gAverage[2]+($mark['sayansi']);
                            $gAverage[3]=$gAverage[3]+($mark['english']);
                            $gAverage[4]=$gAverage[4]+($mark['jamii']);
                            $gAverage[5]=$gAverage[5]+($mark['maadili']);
    
                            if(assignGrade($mark['averageMarks'])=='A'){
                                $aCount++;
                            }
                            else if(assignGrade($mark['averageMarks'])=='B'){
                                $bCount++;
                            }
                            else if(assignGrade($mark['averageMarks'])=='C'){
                                $cCount++;
                            }
                            else if(assignGrade($mark['averageMarks'])=='D'){
                                $dCount++;
                            }
                            else{
                                $eCount++;
                            }
                        @endphp
    
                        <tr class="odd:bg-gray-200 even:bg-white">
                            <td class="border border-black text-right">{{ $i }}</td>
                            <td class="capitalize border border-black">
                                @php
                                    $schoolData=\App\Models\Schools::select('schoolName')->where([
                                        ['schoolId','=',$mark['schoolId']]
                                    ])->first();
    
                                    $schoolName=($schoolData)?$schoolData['schoolName']:'<p class="text-red-500 italic">Not Found!</p>';
                                @endphp
    
                                <p>{!! $schoolName !!}</p>
                            </td>
                            <td class="border border-black text-right">{{ number_format(($mark['hisabati']),2) }}</td>
                            <td class="border border-black">{{ assignGrade(($mark['hisabati'])) }}</td>
                            <td class="border border-black text-right">{{ number_format(($mark['kiswahili']),2) }}</td>
                            <td class="border border-black">{{ assignGrade(($mark['kiswahili'])) }}</td>
                            <td class="border border-black text-right">{{ number_format(($mark['sayansi']),2) }}</td>
                            <td class="border border-black">{{ assignGrade(($mark['sayansi'])) }}</td>
                            <td class="border border-black text-right">{{ number_format(($mark['english']),2) }}</td>
                            <td class="border border-black">{{ assignGrade(($mark['english'])) }}</td>
                            <td class="border border-black text-right">{{ number_format(($mark['jamii']),2) }}</td>
                            <td class="border border-black">{{ assignGrade(($mark['jamii'])) }}</td>
                            <td class="border border-black text-right">{{ number_format(($mark['maadili']),2) }}</td>
                            <td class="border border-black">{{ assignGrade(($mark['maadili'])) }}</td>
                            <td class="border border-black text-right">{{ number_format($totalMarks, 2) }}</td>
                            <td class="border border-black text-right">{{ $mark['averageMarks'] }}</td>
                            <td class="border border-black">{{ assignGrade($mark['averageMarks']) }}</td>

                            @if ($storedAvg==$mark['averageMarks'])
                                @php
                                    $j++;
                                    $storedAvg=$mark['averageMarks'];
                                @endphp

                                <td class="border border-black text-right">{{ ($i-$j) }}</td>  
                            @else
                                @php
                                    $j=0;  
                                    $storedAvg=$mark['averageMarks'];  
                                @endphp

                                <td class="border border-black text-right">{{ $i }}</td>    
                            @endif
                            
                            <td class="border border-black">{{ finalStatus($mark['averageMarks']) }}</td>
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
                            <th class="border border-black p-1 uppercase">Wastani Ya Daraja</th>
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
    
                                    $gAver=(count($marks)>0)?($gATotal/(6*count($marks))):0;
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
    
                                    $gAver=(count($marks)>0)?($gATotal/(count($marks))):0;
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
                        <th rowspan="3" class="text-center border border-black">TATHIMINI YA UFAULU</th>
                        <th colspan="6" class="text-center border border-black uppercase">Daraja</th>
                    </tr>
    
                    <tr>
                        <th class="border border-black">A</th>
                        <th class="border border-black">B</th>
                        <th class="border border-black">C</th>
                        <th class="border border-black">D</th>
                        <th class="border border-black">E</th>
                        <th class="border border-black uppercase">Jumla</th>
                    </tr>
    
                    @php
                        if($classId>4){
                            $failCount=$dCount+$eCount;
                        }
                        else{
                            $failCount=$eCount;
                        }

                        $gradeCount=$aCount+$bCount+$cCount+$dCount+$eCount;
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
                <table class="w-full">
                    <thead>
                        <tr>
                            <th colspan="2" class="border border-black px-2 text-center">WALIOSAJIRIWA</th>
                            <th class="border border-black px-2 text-center uppercase">Pass</th>
                            <th class="border border-black px-2 text-center uppercase">Fail</th>
                        </tr>
                    </thead>
    
                    <tbody>
                        <tr class="text-center">
                            <td class="border border-black px-2 uppercase" rowspan="2">Jumla</td>
                            <td class="border border-black px-2" rowspan="2">{{ $gradeCount }}</td>
                            <td class="border border-black px-2">{{ ($gradeCount-$failCount) }}</td>
                            <td class="border border-black px-2">{{ $failCount }}</td>
                        </tr>
    
                        <tr class="text-center">
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
                $gradeArray=[];
                $failedCount=0;
                $subList=['hisabati','kiswahili','sayansi','english','jamii','maadili'];
            @endphp
    
            @foreach ($marks as $aMark)
                @php
                    foreach ($subList as $list) {
                        if(assignGrade(($aMark[$list]))=='A'){
                            array_push($gradeArray, ''.substr($list, 0, 1).'A');
                        }
                        else if(assignGrade(($aMark[$list]))=='B'){
                            array_push($gradeArray, ''.substr($list, 0, 1).'B');
                        }
                        else if(assignGrade(($aMark[$list]))=='C'){
                            array_push($gradeArray, ''.substr($list, 0, 1).'C');
                        }
                        else if(assignGrade(($aMark[$list]))=='D'){
                            array_push($gradeArray, ''.substr($list, 0, 1).'D');
                        }
                        else{
                            array_push($gradeArray, ''.substr($list, 0, 1).'E');
                        }
                    }
                @endphp
            @endforeach
    
            <h2 class="text-2xl font-bold mb-2 text-center">TATHIMINI YA MADARAJA YA KILA SOMO</h2>
    
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-center border border-black uppercase">Somo</th>
                        <th class="text-center border border-black">A</th>
                        <th class="text-center border border-black">B</th>
                        <th class="text-center border border-black">C</th>
                        <th class="text-center border border-black">D</th>
                        <th class="text-center border border-black">E</th>
                        <th class="text-center border border-black uppercase">Wastani Ya Somo</th>
                        <th class="text-center border border-black uppercase">Walio Faulu</th>
                        <th class="text-center border border-black">%</th>
                        <th class="text-center border border-black uppercase">Wasio Faulu</th>
                        <th class="text-center border border-black">%</th>
                    </tr>
                </thead>
    
                <tbody>
                    @if (count($subList)>0)
                        @php
                            $g=0;
                            $groupArray = array_count_values($gradeArray);  
                        @endphp
    
                        @foreach ($subList as $name)
                            @php
                                $rowColor=($g%2==0)?"bg-white":"bg-gray-200";

                                if($classId>4){
                                    $failedCount=(((array_key_exists(''.substr($name, 0, 1).'D', $groupArray))?$groupArray[''.substr($name, 0, 1).'D']:0)+((array_key_exists(''.substr($name, 0, 1).'E', $groupArray))?$groupArray[''.substr($name, 0, 1).'E']:0));
                                }
                                else{
                                    $failedCount=(array_key_exists(''.substr($name, 0, 1).'E', $groupArray))?$groupArray[''.substr($name, 0, 1).'E']:0;
                                }

                                $totalGradeCount=(((array_key_exists(''.substr($name, 0, 1).'A', $groupArray))?$groupArray[''.substr($name, 0, 1).'A']:0)+((array_key_exists(''.substr($name, 0, 1).'B', $groupArray))?$groupArray[''.substr($name, 0, 1).'B']:0)+((array_key_exists(''.substr($name, 0, 1).'C', $groupArray))?$groupArray[''.substr($name, 0, 1).'C']:0)+((array_key_exists(''.substr($name, 0, 1).'D', $groupArray))?$groupArray[''.substr($name, 0, 1).'D']:0)+((array_key_exists(''.substr($name, 0, 1).'E', $groupArray))?$groupArray[''.substr($name, 0, 1).'E']:0));
                            @endphp
    
                            <tr class="{{ $rowColor }}">
                                <td class="pl-2 border border-black capitalize">{{ $name }}</td>
                                <td class="text-center border border-black px-2">{{ (array_key_exists(''.substr($name, 0, 1).'A', $groupArray))?$groupArray[''.substr($name, 0, 1).'A']:0 }}</td>
                                <td class="text-center border border-black px-2">{{ (array_key_exists(''.substr($name, 0, 1).'B', $groupArray))?$groupArray[''.substr($name, 0, 1).'B']:0 }}</td>
                                <td class="text-center border border-black px-2">{{ (array_key_exists(''.substr($name, 0, 1).'C', $groupArray))?$groupArray[''.substr($name, 0, 1).'C']:0 }}</td>
                                <td class="text-center border border-black px-2">{{ (array_key_exists(''.substr($name, 0, 1).'D', $groupArray))?$groupArray[''.substr($name, 0, 1).'D']:0 }}</td>
                                <td class="text-center border border-black px-2">{{ (array_key_exists(''.substr($name, 0, 1).'E', $groupArray))?$groupArray[''.substr($name, 0, 1).'E']:0 }}</td>
    
                                @if (count($marks)>0)
                                    <td class="text-center border border-black">{{ number_format(($gAverage[$g]/count($marks)), 2) }}</td>
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
                                        {{ number_format((($failedCount*100)/$totalGradeCount), 2) }}
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
                            <td class="text-red-500 text-center p-2" colspan="6">No Data Found!</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function setEndDate(){
            var startDate=$("#startDate").val();
            $("#endDate").attr('min', startDate);
        }
    </script>
@endsection