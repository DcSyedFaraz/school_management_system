<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report</title>
    <style>
        @page {
            size: A5;
            margin: 2mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 2px;
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
            font-size: 9px;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .header h1 {
            margin: 0;
            font-size: 12px;
            color: #333;
            background-color: #fff;
        }

        .student-info,
        .marks-summary,
        .remarks {
            background-color: #f0f0f0;
            padding: 2px;
            border: 1px solid #ddd;
            margin-bottom: 5px;
        }

        .remarks {
            height: 80px;
        }

        .footer {
            padding: 2px;
            /* text-align: center; */
            clear: both;
            margin-top: 10px;
        }

        .color-A {
            background-color: #00A82E;
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 100%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #fff;
        }

        .color-B {
            background-color: #1FEE0B;
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 100%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #212529;
        }

        .color-C {
            background-color: #DEF043;
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 100%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #212529;
        }

        .color-D {
            background-color: #FF772F;
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 100%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #fff;
        }

        .color-E {
            background-color: #FF0000;
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 100%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #fff;
        }
    </style>
</head>

<body>
    @php

        $term = date('m', strtotime($student['date'])) <= 6 ? 1 : 2;

    @endphp
    <div class="report-container">
        <div class="header">
            <h2>OFISI YA RAIS - TAMISEMI</h2>
            <h2>SHULE YA MSINGI {{ $student['schoolname'] }}</h2>
            <h2>RIPOTI YA MAENDELEO YA MWANAFUNZI</h2>
        </div>

        <div class="student-info compact">
            <p><strong>JINA:</strong> {{ $student['studentName'] }} <span style="float: right;"><strong>DARASA:</strong>
                    {{ $student['classname'] }}</span></p>
            <p>
                <span>

                    <strong>Muhula:</strong> {{ $term }}
                </span>
                <span style="text-align: center; margin-left: 10rem;">
                    <strong>MTIHANI:</strong> {{ $student['examname'] }}
                </span>
                <span style="float: right;">
                    <strong>TAREHE YA MTIHANI:</strong> {{ $student['date'] }}
                </span>
            </p>



        </div>

        <table>
            <thead>
                <tr>
                    <th>MASOMO</th>
                    <th>ALAMA</th>
                    <th>DARAJA</th>
                    <th>MAONI</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($student['subjects'] as $subject)
                    <tr>
                        {{-- @dd($subject) --}}
                        <td style="text-transform: uppercase">{{ $subject['name'] }}</td>
                        <td>{{ $subject['total'] }}</td>
                        <td><span class="color-{{ $subject['grade'] }}">{{ $subject['grade'] }}</span></td>
                        <td><span class="color-{{ $subject['grade'] }}">{{ $subject['gradeDescription'] }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>


        <div class="marks-summary compact">
            <p><strong>Jumla ya alama:</strong> {{ $student['totalMarks'] }} Kati ya
                {{ count($student['subjects']) * 50 }}</p>
            <p><strong>Wastani:</strong> {{ $student['average'] }}</p>
            <p><strong>Daraja:</strong> {{ $student['grade'] }}</p>
            <p><strong>Nafasi:</strong> {{ $student['position'] }} Kati ya {{ $student['totalposition'] }}</p>
        </div>

        <div class="remarks compact">
            <p><strong>Maoni ya mwalimu wa darasa:</strong>
                ________________________________________________________________________________
            </p>
            <p><strong>Maoni ya Mwalimu mkuu:</strong>
                ____________________________________________________________________________________</p>
            <p style="margin-bottom: 10px;"></p>
            <p><strong>Tarehe ya kufunga:</strong> ________________________________________
                <strong> Tarehe ya kufungua:</strong> _____________________________
            </p>
        </div>

        <p style="text-align: center;">
            xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
        </p>
        <div class="footer">
            <p><strong>Jina la Mzazi:</strong> _____________________________________________________<strong>Tarehe:</strong>________________________
            </p>
            <p> <strong>Jina la Mwanafunzi:</strong>
                ________________________________________________<strong>Darasa:</strong>________________________ </p>
            <p style="margin-top: 10px"><strong>Maoni ya
                    Mzazi:</strong>_________________________________________________________________________________________________
            </p>
            <p style="margin-bottom: 10px;">
                _________________________________________________________________________________________________</p>
            <p style="text-align: right;"><strong>Saini ya Mzazi:</strong> _____________________</p>
            {{-- <p style="text-align: right; margin-right: 6rem"><strong>Tarehe:</strong>{{ date('F j, Y') }}</p>
            <p style="text-align: right; margin-right: 8.2rem"><strong>Darasa:</strong> {{ $student['classname'] }}</p> --}}

        </div>
    </div>
</body>

</html>
