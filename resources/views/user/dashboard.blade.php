@extends('admin.layout')

@section('content')
    <div class="my-3 mx-4 min-h-[88vh]">
        <div class="w-full">
            <div class="my-2">
                <h2 class="text-2xl font-bold">Kichujio:</h2>
                
                <form action="{{ url('/dashboard/filter') }}" method="post" id="filterForm">
                    @csrf
    
                    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-2">
                        <div>
                            <label for="class">Chagua Darasa:<span class="text-red-500">*</span></label>
                            <select class="block w-full block p-2 rounded-md border border-black" name="class" id="class" required>
                                <option value="">-- CHAGUA DARASA --</option>
                                @if (count($classes)>0)
                                    @foreach ($classes as $class)
                                        <option value="{{ $class['gradeId'] }}" @selected($classId==$class['gradeId'])>{{ $class['gradeName'] }}</option>
                                    @endforeach
                                @else
                                    <option value="" class="text-red-500">Hakuna Taarifa!</option>
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
                                    <option value="" class="text-red-500">Hakuna Taarifa!</option>
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
                    <a href="{{ url('/dashboard') }}"><button type="button" form="filterForm" class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1">Onesha Upya</button></a>
                    <button type="submit" form="filterForm" class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1">Kichujio</button>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 md:grid-cols-2 grid-cols-1 gap-3">
            <div>
                <h2 class="text-2xl font-bold mb-2 uppercase">Muhtasari wa Matokeo kwa grafu:</h2>
                <canvas id="myChart"></canvas>
            </div>

            <div>
                <h2 class="text-2xl font-bold mb-2">Muhtasari wa Matokeo kwa Nafasi:</h2>
                
                <div class="h-[350px] overflow-y-auto">
                    <table class="w-full">
                        <thead>
                            <th class="border border-black p-2 uppercase">Nafasi</th>
                            <th class="border border-black p-2 uppercase">Jina La Mwanafunzi</th>
                            <th class="border border-black p-2 uppercase">Ufaulu</th>
                        </thead>
    
                        <tbody>
                            @if (count($schoolRanks)>0)
                                @php
                                    $i=1;
                                    $totalPass=0;
                                @endphp
    
                                @foreach ($schoolRanks as $schoolRank)
                                    <tr class="odd:bg-gray-200 even:bg-white">
                                        <td class="border border-black text-center p-2">{{ $i }}</td>
                                        <td class="border border-black p-2 capitalize">{{ $schoolRank['studentName'] }}</td>
                                        <td class="border border-black p-2">
                                            @if ($schoolRank['average']>=$borderLine)
                                                @php
                                                    $totalPass++;
                                                @endphp

                                                <p class="text-green-500 italic">Pass</p>
                                            @elseif($schoolRank['average']==0)
                                                <p class="text-blue-500 italic">Absent</p>
                                            @else
                                                <p class="text-red-500 italic">Fail</p>
                                            @endif
                                        </td>
                                    </tr>
    
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                            @else
                                <td colspan="3" class="text-red-500 p-2 text-center border border-black">Hakuna Taarifa</td>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="grid lg:grid-cols-4 md:grid-cols-3 grid-cols-1 gap-2">
                @if (count($schoolRanks)>0)
                    <div class="rounded-xl shadow-2xl bg-white p-5 border-l-4 border-blue-500">
                        <h3 class="font-bold">Asilimia ya Ufaulu:</h3>

                        @php
                            $passPercentage=number_format(($totalPass/count($schoolRanks))*100, 2);    
                        @endphp

                        <p class="italic">{{ $passPercentage }} %</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function setEndDate(){
            var startDate=$("#startDate").val();
            $("#endDate").attr('min', startDate);
        }
        
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['A', 'B', 'C', 'D', 'E'],
                datasets: [
                    {
                        label: 'WAVULANA',
                        data: {!! json_encode($maleRanks) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    },
                    {
                        label: 'WASICHANA',
                        data: {!! json_encode($femaleRanks) !!},
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgb(255, 99, 132)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'WASTANI KWA DARAJA'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'NAMBA YA WANAFUNZI'
                        },
                        beginAtZero: true,
                        stepSize: 1
                    }
                }
            }
        });
    </script>
@endsection