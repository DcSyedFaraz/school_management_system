<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report (English)</title>
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
            text-transform: uppercase;
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
            clear: both;
            margin-top: 10px;
        }

        /* Fixed grade colors — shared with the on-screen report pages and the Chapisha Matokeo PDF. */
        .color-A {
            background-color: #dcfce7;
            color: #166534;
            font-weight: 700;
            padding: 0.25em 0.5em;
            border-radius: 0.25rem;
        }

        .color-B {
            background-color: #ecfccb;
            color: #3f6212;
            font-weight: 700;
            padding: 0.25em 0.5em;
            border-radius: 0.25rem;
        }

        .color-C {
            background-color: #fef9c3;
            color: #854d0e;
            font-weight: 700;
            padding: 0.25em 0.5em;
            border-radius: 0.25rem;
        }

        .color-D {
            background-color: #ffedd5;
            color: #9a3412;
            font-weight: 700;
            padding: 0.25em 0.5em;
            border-radius: 0.25rem;
        }

        .color-E {
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: 700;
            padding: 0.25em 0.5em;
            border-radius: 0.25rem;
        }

        .color-ABS {
            background-color: #e5e7eb;
            color: #6b7280;
            font-weight: 700;
            font-style: italic;
            padding: 0.25em 0.5em;
            border-radius: 0.25rem;
        }

        h2 {
            font-size: 10px;
        }

        table th,
        table td {
            text-align: center;
            /* Center all table cells */
            vertical-align: middle;
            /* Center vertically */
        }
    </style>
</head>

<body>
    @php
        $term =
            isset($student['date']) && $student['date'] ? (date('m', strtotime($student['date'])) <= 6 ? 1 : 2) : '-';
        $subjectMapping = [
            'hisabati' => 'Mathematics',
            'sayansi' => 'Science and Technology',
            'jiographia' => 'Geography and Environment',
            'historia' => 'History of Tanzania',
            'mazingira' => 'Health and Environment',
            'michezo' => 'Arts and Sports',
            'utamaduni' => 'Culture, Arts and Sports',
            'jamii' => 'Social Studies',
            'maadili' => 'Civics and Morals',
            's_kazi' => 'Vocational Skills',
        ];

        $classMapping = [
            'KWANZA' => 'ONE',
            'PILI' => 'TWO',
            'TATU' => 'THREE',
            'NNE' => 'FOUR',
            'TANO' => 'FIVE',
            'SITA' => 'SIX',
            'SABA' => 'SEVEN',
        ];

        $examMapping = [
            'NUSU MUHULA' => 'MID-TERM EXAM',
            'MWISHO MUHULA WA I' => 'TERMINAL EXAM',
            'MWISHO MUHULA WA II' => 'ANNUAL EXAM',
            'WIKI' => 'WEEKLY EXAM',
        ];

        $gradeComments = [
            'A' => 'Keep up the excellent performance.',
            'B' => 'Work harder to achieve a higher grade.',
            'C' => 'Needs to study more; performance is fair.',
            'D' => 'Performance is low; increase effort in studies.',
            'E' => 'Performance is poor; needs serious improvement.',
        ];

    @endphp

    <div class="report-container">
        <div class="header">
            <h2>PRIME MINISTER'S OFFICE - TAMISEMI</h2>
            <h2>{{ $student['districtName'] ?? '_____________________' }} DISTRICT COUNCIL</h2>
            <h2>{{ $student['schoolname'] ?? '_____________________' }} PRIMARY SCHOOL</h2>
            <h2>STUDENT PROGRESS REPORT</h2>
            <h2>EXAMINATION:
                {{ $examMapping[strtoupper($student['examname'] ?? '')] ?? strtoupper($student['examname'] ?? '_____________________') }}
            </h2>
        </div>

        <div class="student-info compact">
            <p><strong>NAME:</strong> {{ $student['studentName'] ?? '_________________' }}
                <span style="float: right;"><strong>CLASS:</strong>
                    {{ $classMapping[strtoupper($student['classname'] ?? '')] ?? ($student['classname'] ?? '________') }}</span>
            </p>
            <p>
                <span><strong>TERM:</strong> {{ $term }}</span>
                <span style="float: right;"><strong>EXAM DATE:</strong> {{ $student['date'] ?? '-' }}</span>
            </p>
        </div>

        @php
            $subjects = $student['subjects'] ?? [];
            $totalMarks = 0;
            $subjectsTaken = 0;
            foreach ($subjects as $sub) {
                if ($sub['mark'] !== null) {
                    $totalMarks += $sub['mark'];
                    $subjectsTaken++;
                }
            }
        @endphp

        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
            <thead style="background-color: #f0f0f0;">
                <tr>
                    <th style="text-align: left;">SUBJECT</th>
                    <th style="text-align: center;">TOTAL</th>
                    <th style="text-align: center;">GRADE</th>
                    <th style="text-align: center;">SUBJECT POSITION</th>
                    <th style="text-align: left;">GRADE DESCRIPTION</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subjects as $subject)
                    @php
                        $subjectName = $subject['name'] ?? 'UNKNOWN SUBJECT';
                        $subjectName = $subjectMapping[$subjectName] ?? $subjectName;
                        $subjectName = strtoupper($subjectName);

                        $grade = $subject['grade'] ?? Grading::absentGrade();
                        $gradeDesc = $subject['gradeDescription'] ?? 'Not Taken';

                        $position = $subject['position'] ?? '-';
                        $total = $subject['mark'] ?? '-';
                    @endphp
                    <tr>
                        <td style="text-align: left;">{{ $subjectName }}</td>
                        <td style="text-align: center;">{{ $total }}</td>
                        <td style="text-align: center;">
                            <span class="color-{{ $grade }}">{{ $grade == Grading::absentGrade() ? 'N/A' : $grade }}</span>
                        </td>
                        <td style="text-align: center;">{{ $position }}</td>
                        <td style="text-align: left;"><span
                                class="color-{{ $grade }}">{{ $gradeDesc }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="marks-summary compact">
            <p><strong>Total Marks:</strong> {{ $totalMarks }} Out of {{ $subjectsTaken * 50 }}</p>
            <p><strong>Average:</strong>
                {{ $subjectsTaken > 0 ? number_format($totalMarks / $subjectsTaken, 2) : '-' }}</p>
            <p><strong>Grade:</strong> {{ $student['grade'] ?? '-' }}</p>
            <p><strong>Position:</strong> {{ $student['position'] ?? '-' }} Out of {{ $studentsTakenExam }}</p>
        </div>

        <div class="remarks compact">
            <p><strong>Class Teacher's Remarks:</strong> <u>{{ $gradeComments[$student['grade']] ?? '' }}</u></p>
            <p><strong>Head Teacher's Remarks:</strong>
                _____________________________________________________________________________________</p>
            <br>
            <p>
                <span style="float: left;"><strong>Closing Date:</strong> {{ $closingDate ?? '__________' }}</span>
                <span style="display: block; text-align: right;"><strong>Opening Date:</strong>
                    {{ $openingDate ?? '__________' }}</span>
            </p>
        </div>

        <div class="footer">
            <p><strong>Parent Name:</strong> ___________________________________________________
                <strong>Date:</strong>_____________________
            </p>
            <p><strong>Student Name:</strong>
                __________________________________________________<strong>Class:</strong>_____________________</p>
            <p><strong>Parent's Remarks:</strong>
                __________________________________________________________________________________________________</p>
            <p>__________________________________________________________________________________________________</p>
            <p>__________________________________________________________________________________________________</p>
            <p style="overflow: auto;">
                <span style="float: left;"><strong>School Contact:</strong>
                    {{ $schoolContact ?? '_____________________' }}</span>
                <span style="float: right;"><strong>Parent's Signature:</strong> _____________________</span>
            </p>
        </div>


        <hr style="margin-top: 3rem; border-style: dashed">
        <div style="text-align: center">
            <p><strong>Designed by:</strong> rmstechnology.co.tz +255 786 283 282 / +255 736 102 030</p>
            <p><strong>Printed Date:</strong> {{ date('F j, Y') }}</p>
        </div>
    </div>
</body>

</html>
