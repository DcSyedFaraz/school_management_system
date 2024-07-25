<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report</title>
    <style>
        @page {
            size: A5;
            margin: 5mm;
        }

        /* Add your custom styles here */
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        .text-center {
            text-align: center;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .compact {
            font-size: 12px;
        }

        .header {
            background-color: #333;
            color: #fff;
            padding: 5px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 14px;
        }

        .student-info {
            background-color: #f0f0f0;
            padding: 5px;
            border: 1px solid #ddd;
        }

        .marks-summary {
            background-color: #f0f0f0;
            padding: 5px;
            border: 1px solid #ddd;
        }

        .remarks {
            padding: 5px;
            border: 1px solid #ddd;
        }

        .footer {
            background-color: #333;
            color: #fff;
            padding: 5px;
            text-align: center;
            clear: both;
        }
    </style>
</head>

<body>
    <div class="report-container">
        <div class="header">
            <h1 style="text-transform: uppercase">{{ $student['schoolname'] }}</h1>
            <p>Quality Education for the New Generation</p>
            <h2>PUPIL'S PROGRESS REPORT</h2>
        </div>

        <div class="student-info compact">
            <p><strong>NAME:</strong> {{ $student['studentName'] }}</p>
            <p><strong>CLASS:</strong> {{ $student['classname'] }}</p>
            <p><strong>EXAM:</strong> {{ $student['examname'] }}</p>
            <p><strong>EXAM DATE:</strong> {{ $student['date'] }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>SUBJECT</th>
                    {{-- <th>TEST</th> --}}
                    {{-- <th>EXAM</th> --}}
                    <th>TOTAL</th>
                    {{-- <th>AVERAGE</th> --}}
                    <th>GRADE</th>
                    <th>COMMENT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($student['subjects'] as $subject)
                    <tr>
                        <td style="text-transform: uppercase">{{ $subject['name'] }}</td>
                        {{-- <td>{{ $subject['test'] }}</td> --}}
                        {{-- <td>{{ $subject['exam'] }}</td> --}}
                        <td>{{ $subject['total'] }}</td>
                        {{-- <td>{{ $subject['average'] }}</td> --}}
                        <td>{{ $subject['grade'] }}</td>
                        <td>{{ $subject['comment'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="marks-summary compact">
            <p><strong>Total marks:</strong> {{ $student['totalMarks'] }} Out of {{ count($student['subjects']) * 50 }}</p>
            <p><strong>Average:</strong> {{ $student['average'] }}</p>
            <p><strong>Grade:</strong> {{ $student['grade'] }}</p>
            <p><strong>Position:</strong> {{ $student['position'] }} Out of {{ $student['totalposition'] }}</p>
            {{-- <p><strong>Attendance:</strong> {{ $student['attendance'] }} Days Present, {{ $student['absent'] }} Days
                absent</p> --}}
        </div>

        <div class="remarks compact">
            <p><strong>Class teacher's remarks:</strong> _______________________________</p>
            <p><strong>Head teacher's remarks:</strong> _______________________________</p>
        </div>

        <div class="footer">
            <p>Closing date: __________ Opening date: __________</p>
            <p>Jina la Mzazi: ______________________ Jina la Mwanafunz: ______________________ Maoni va Mzazi:
                ______________________ Saini ya Mzazi: ______________________</p>
            <p>Printed Date: {{ date('l, F j, Y') }}</p>
        </div>
    </div>
</body>

</html>
