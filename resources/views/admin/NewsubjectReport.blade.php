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
                foreach ($rank as $r) {
                    if ($r['rankRangeMin'] < $marks && $r['rankRangeMax'] >= $marks) {
                        return $r['rankName'];
                    }
                }
            }
            return 'Null';
        }
    @endphp

    <div class="p-3">
        <div class="flex justify-end">
            <form action="{{ url('/downloadSubjectReport') }}" method="post">
                @csrf

                <input type="hidden" name="rClass" id="rClass" value="{{ $classId }}">
                <input type="hidden" name="rExam" id="rExam" value="{{ $examId }}">
                <input type="hidden" name="rRegion" id="rRegion" value="{{ $regionId }}">
                <input type="hidden" name="rDistrict" id="rDistrict" value="{{ $districtId }}">
                <input type="hidden" name="rWard" id="rWard" value="{{ $wardId }}">
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

            <form action="{{ url('/filterSubjectReport') }}" method="post" id="filterForm">
                @csrf

                <div class="grid lg:grid-cols-7 md:grid-cols-3 grid-cols-1 gap-2">
                    <!-- Form Inputs for Filters (class, exam, region, etc.) -->
                    <div>
                        <label for="class">Darasa:<span class="text-red-500">*</span></label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="class" id="class"
                            required>
                            <option value="">-- SELECT CLASS --</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class['gradeId'] }}" @selected($classId == $class['gradeId'])>
                                    {{ $class['gradeName'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="exam">Mtihani:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="exam" id="exam">
                            <option value="">-- SELECT EXAM --</option>
                            @foreach ($exams as $exam)
                                <option value="{{ $exam['examId'] }}" @selected($examId == $exam['examId'])>
                                    {{ $exam['examName'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="region">Mkoa:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="region" id="region">
                            <option value="">-- SELECT REGION --</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region['regionId'] }}" @selected($regionId == $region['regionId'])>
                                    {{ $region['regionName'] }} ({{ $region['regionCode'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="district">Wilaya:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="district"
                            id="district">
                            <option value="">-- SELECT DISTRICT --</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district['districtId'] }}" @selected($districtId == $district['districtId'])>
                                    {{ $district['districtName'] }} ({{ $district['districtCode'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ward">Kata:</label>
                        <select class="block w-full block p-2 rounded-md border border-black" name="ward" id="ward">
                            <option value="">-- SELECT WARD --</option>
                            @foreach ($wards as $ward)
                                <option value="{{ $ward['wardId'] }}" @selected($wardId == $ward['wardId'])>
                                    {{ $ward['wardName'] }} ({{ $ward['wardCode'] }})</option>
                            @endforeach
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
                <button type="button" form="filterForm"
                    class="mx-1 bg-green-500 hover:bg-green-600 px-2 py-1 text-white rounded-md mt-1"
                    onclick="filterReport()">
                    Onesha Upya
                </button>
                <button type="button" class="bg-blue-500 hover:bg-blue-600 px-2 py-1 text-white rounded-md mt-1"
                    onclick="submitForm()">
                    Kichujio
                </button>
            </div>
        </div>

        <!-- Progress Bar -->
        <div id="progress-container" style="display: none;">
            <progress id="progress-bar" max="100" value="0"></progress>
            <p id="progress-text">Processing...</p>
        </div>

        <!-- Filtered Report Table -->
        <div class="overflow-x-auto">
            <h2 class="text-2xl font-bold mb-2 uppercase">Ripoti Kimasomo:</h2>
            <table class="myTable bg-white">
                <thead>
                    <tr>
                        <th class="border border-black uppercase">NA</th>
                        <th class="border border-black uppercase">Mkoa</th>
                        <th class="border border-black uppercase">Wilaya</th>
                        <th class="border border-black uppercase">Kata</th>
                        <th class="border border-black uppercase">Shule</th>
                        <!-- Dynamically generated columns based on subjects -->
                        @foreach ($subjects as $subject)
                            <th class="border border-black uppercase">{{ ucfirst($subject) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="report-data"></tbody>
            </table>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        function setEndDate() {
            var startDate = $("#startDate").val();
            $("#endDate").attr('min', startDate);
        }

        // Show Progress Bar during AJAX request
        function showProgressBar() {
            $('#progress-container').show();
            var progress = 0;
            var interval = setInterval(function() {
                progress += 10;
                $('#progress-bar').val(progress);
                $('#progress-text').text('Processing... ' + progress + '%');
                if (progress === 100) {
                    clearInterval(interval);
                    $('#progress-text').text('Completed!');
                }
            }, 500);
        }

        // Fetch and display filtered data from cache
        function fetchFilteredData() {
            showProgressBar();
            $.ajax({
                url: "{{ url('/fetchCachedFilteredReport') }}", // Endpoint to fetch cached data
                method: "GET",
                data: $('#filterForm').serialize(),
                success: function(response) {
                    if (response.length > 0) {
                        displayTableData(response);
                    } else {
                        alert("Data not available yet. Please try again later.");
                    }
                },
                error: function() {
                    alert("Error occurred. Please try again.");
                }
            });
        }

        // Display the filtered data in table
        function displayTableData(data) {
            var tableBody = $('#report-data');
            tableBody.empty(); // Clear previous data

            data.forEach(function(row, index) {
                var rowHtml = `<tr>
                    <td class="border border-black">${index + 1}</td>
                    <td class="border border-black">${row.region}</td>
                    <td class="border border-black">${row.district}</td>
                    <td class="border border-black">${row.ward}</td>
                    <td class="border border-black">${row.school}</td>`;

                row.subjects.forEach(function(subject) {
                    rowHtml += `<td class="border border-black">${subject}</td>`;
                });

                rowHtml += `</tr>`;
                tableBody.append(rowHtml);
            });
        }

        // Function to trigger filter and data fetch
        function filterReport() {
            $('#progress-container').hide();
            fetchFilteredData();
        }

        // Form submission logic
        function submitForm() {
            showProgressBar(); // Show the progress bar when the form is submitted
            $.ajax({
                url: "{{ url('/filterSubjectReport') }}", // The endpoint to trigger the filtering
                method: 'POST',
                data: $('#filterForm').serialize(), // Serialize the form data to send it with the request
                success: function(response) {
                    if (response.message === 'Filtering started, please wait...') {
                        // Optionally, you can track the progress with the jobId and use polling to check the status of the job
                        console.log(response.jobId);
                        fetchFilteredData(); // Fetch filtered data once the job is done
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                    alert("Error occurred. Please try again.");
                }
            });
        }
    </script>
@endsection
