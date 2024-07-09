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
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="px-3 text-red-500 italic text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-between mb-1">
            <div>
                <a href="{{ asset('excel/marks.xlsx') }}" target="blank" download>
                    <button type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded-md mr-1">
                        <i class="material-symbols-outlined text-sm">download</i> <span>Pakua Kiolezo</span>
                    </button>
                </a>
            </div>
    
            <div class="space-x-0.5">
                <button type="button" data-modal-target="uploadModal" data-modal-toggle="uploadModal" class="bg-purple-500 hover:bg-purple-600 text-white py-1 px-2 rounded-md">
                    <i class="material-symbols-outlined text-sm">upload</i> <span>Pakia Kiolezo</span>
                </button>
    
                <button type="button" data-modal-target="newEntryModal" data-modal-toggle="newEntryModal" class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded-md">
                    <i class="material-symbols-outlined text-sm">add</i>
                </button>

                <button type="button" data-modal-target="bulkDelModal" data-modal-toggle="bulkDelModal" class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded-md">
                    <i class="material-symbols-outlined text-sm">delete</i>
                </button>
            </div>
        </div>
    
        <div class="my-3">
            <h2 class="text-2xl font-bold">Kichujio:</h2>
            
            <form action="{{ url('/filterUploads') }}" method="post" id="filterForm">
                @csrf
    
                <div class="grid lg:grid-cols-4 md:grid-cols-4 grid-cols-1 gap-2">
                    <div>
                        <label for="class">Chagua Darasa:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="class" id="class">
                            <option value="">--- CHAGUA DARASA ---</option>
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
                            <option value="">--- CHAGUA MTIHANI ---</option>
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
                <a href="{{ url('/dashboard/uploads') }}"><button type="button" form="filterForm" class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">Onesha Upya</button></a>
                <button type="submit" form="filterForm" class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">Kichujio</button>
            </div>
        </div>
    
        <div class="p-3 overflow-x-scroll">
            <table class="myTable bg-white">
                <thead>
                    <tr>
                        <th class="border border-black">S/N</th>
                        <th class="border border-black uppercase">Jina La Mwanafunzi</th>
                        <th class="border border-black uppercase">
                            <p>Hisabati</p>
                            <p>(Grade)</p>
                        </th>
                        <th class="border border-black uppercase">
                            <p>Kiswahili</p>
                            <p>(Grade)</p>
                        </th>
                        <th class="border border-black uppercase">
                            <p>Sayansi</p>
                            <p>(Grade)</p>
                        </th>
                        <th class="border border-black uppercase">
                            <p>English</p>
                            <p>(Grade)</p>
                        </th>
                        <th class="border border-black uppercase">
                            <p>M/JAMII & S/KAZI</p>
                            <p>(Grade)</p>
                        </th>
                        <th class="border border-black uppercase">
                            <p>U/MAADILI</p>
                            <p>(Grade)</p>
                        </th>
                        <th class="border border-black uppercase">Jumla</th>
                        <th class="border border-black uppercase">Wastani</th>
                        <th class="border border-black uppercase">Daraja</th>
                        <th class="border border-black uppercase">Ufaulu</th>
                        <th class="border border-black uppercase">Action</th>
                    </tr>
                </thead>
    
                <tbody>
                    @php
                        $i=1;
                    @endphp
                    @foreach ($marks as $mark)
                        {{-- @php
                            $totalMarks=$mark['hisabati']+$mark['kiswahili']+$mark['sayansi']+$mark['english']+$mark['jamii']+$mark['maadili'];
                            $average=number_format(($totalMarks/6), 2, '.', '');    
                        @endphp --}}
    
                        <tr class="odd:bg-gray-200 even:bg-white">
                            <td class="border border-black">
                                <div class="flex justify-start">
                                    <div>
                                        {{ $i }}.
                                    </div>

                                    <div class="flex items-center ml-1">
                                        <input id="delRecord{{ $mark['markId'] }}" name="delRecord{{ $mark['markId'] }}" type="checkbox" onclick="addDelId({{ $mark['markId'] }})" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                    </div>
                                </div>
                            </td>
                            <td class="capitalize border border-black">
                                <p>{{ $mark['studentName'] }}</p>
    
                                @php
                                    $examData=\App\Models\Exams::select('examName')->where([
                                        ['examId','=',$mark['examId']]
                                    ])->first();
    
                                    $examName=($examData)?$examData['examName']:'<span class="text-red-500 italic">Not Found!</span>';
    
                                    $classData=\App\Models\Grades::select('gradeName')->where([
                                        ['gradeId','=',$mark['classId']]
                                    ])->first();
    
                                    $className=($classData)?$classData['gradeName']:'<span class="text-red-500 italic">Not Found!</span>';
                                @endphp
    
                                @if ($mark['gender']=='M')
                                    <p class="italic text-sm"><b>Gender:</b> Male</p>     
                                @else
                                    <p class="italic text-sm"><b>Gender:</b> Female</p>
                                @endif
                                
                                <p class="text-sm italic"><b>Class:</b> {!! $className !!}</p>
                                <p class="text-sm italic"><b>Exam:</b> {!! $examName !!}</p>
                                <p class="text-sm italic">{{ date('d-m-Y', strtotime($mark['examDate'])) }}</p>
                            </td>
                            <td class="border border-black text-center">
                                <p>{{ $mark['hisabati'] }}</p>
                                <p>{{ assignGrade($mark['hisabati']) }}</p>
                            </td>
                            <td class="border border-black text-center">
                                <p>{{ $mark['kiswahili'] }}</p>
                                <p>{{ assignGrade($mark['kiswahili']) }}</p>
                            </td>
                            <td class="border border-black text-center">
                                <p>{{ $mark['sayansi'] }}</p>
                                <p>{{ assignGrade($mark['sayansi']) }}</p>
                            </td>
                            <td class="border border-black text-center">
                                <p>{{ $mark['english'] }}</p>
                                <p>{{ assignGrade($mark['english']) }}</p>
                            </td>
                            <td class="border border-black text-center">
                                <p>{{ $mark['jamii'] }}</p>
                                <p>{{ assignGrade($mark['jamii']) }}</p>
                            </td>
                            <td class="border border-black text-center">
                                <p>{{ $mark['maadili'] }}</p>
                                <p>{{ assignGrade($mark['maadili']) }}</p>
                            </td>
                            <td class="border border-black text-right">{{ $mark['total'] }}</td>
                            <td class="border border-black text-right">{{ $mark['average'] }}</td>
    
                            @if ($mark['average']>0)
                                <td class="border border-black">{{ assignGrade($mark['average']) }}</td>
                            @else
                                <td class="border border-black">ABS</td> 
                            @endif
    
                            @if ($mark['average']>0)
                                <td class="border border-black">{{ finalStatus($mark['average']) }}</td> 
                            @else
                                <td class="border border-black"></td> 
                            @endif
    
                            <td class="border border-black">
                                <button type="button" data-modal-target="editEntryModal" data-modal-toggle="editEntryModal" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md mr-0.5" onclick="editEntry({{ $mark['markId'] }})">
                                    <i class="material-symbols-outlined text-sm">edit</i>
                                </button>
    
                                <button type="button" data-modal-target="delEntryModal" data-modal-toggle="delEntryModal" class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded-md" onclick="handleDel({{ $mark['markId'] }})">
                                    <i class="material-symbols-outlined text-sm">delete</i>
                                </button>
                            </td>
                        </tr> 
    
                        @php
                            $i++;
                        @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('modals.newUpload')
    @include('modals.editUpload')
    @include('modals.rejectModal')
    @include('modals.uploadModal')
    @include('modals.bulkDeleteModel')

    <script>
        function setEndDate(){
            var startDate=$("#startDate").val();
            $("#endDate").attr('min', startDate);
        }

        function addDelId(id){
            if($(`#delRecord${id}`).is(':checked')){
                $("#bulkDelId").append(`<input type="hidden" name="delId[]" id="delId${id}" value="${id}">`);
            }
            else{
                $(`#delId${id}`).remove();
            }
        }
        
        function editEntry(id){
            $("#editEntryModal").removeClass('hidden');

            $.ajax({
                type:"GET",
                url:`{{ url('/uploadInfo') }}/${id}`,
                success: function($response){
                    if($response.status==200){
                        $(`#updatedStudentName`).val($response.data.studentName);
                        $('#updatedGender').val($response.data.gender);
                        $('#updatedClass').val($response.data.classId);
                        $('#updatedExam').val($response.data.examId);
                        $('#updatedExamDate').val($response.data.examDate);
                        $('#updatedHisabatiMarks').val($response.data.hisabati);
                        $('#updatedKiswahiliMarks').val($response.data.kiswahili);
                        $('#updatedSayansiMarks').val($response.data.sayansi);
                        $('#updatedEnglishMarks').val($response.data.english);
                        $('#updatedJamiiMarks').val($response.data.jamii);
                        $('#updatedMaadiliMarks').val($response.data.maadili);
                        $('#updatedFirstGrade').val($response.data.firstGrade);
                        $("#entryId").val(id);   
                    }
                }
            });
        }

        function handleDel(id){
            $("#delEntryModal").removeClass('hidden'); 
            $("#delEntryId").val(id);  
        }
    </script>
@endsection