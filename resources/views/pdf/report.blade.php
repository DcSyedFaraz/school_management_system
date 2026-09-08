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

        th, td {
            border: 1px solid #ddd;
            padding: 2px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            text-transform: uppercase;
        }

        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .compact { font-size: 9px; }
        .header { text-align: center; margin-bottom: 5px; }
        .student-info, .marks-summary, .remarks { background-color: #f0f0f0; padding: 2px; border: 1px solid #ddd; margin-bottom: 5px; }
        .remarks { height: 80px; }
        .footer { padding: 2px; clear: both; margin-top: 10px; }

        /* Fixed grade colors — shared with the on-screen report pages and the Chapisha Matokeo PDF. */
        .color-A { background-color: #dcfce7; color: #166534; font-weight: 700; padding: 0.25em 0.5em; border-radius: 0.25rem; }
        .color-B { background-color: #ecfccb; color: #3f6212; font-weight: 700; padding: 0.25em 0.5em; border-radius: 0.25rem; }
        .color-C { background-color: #fef9c3; color: #854d0e; font-weight: 700; padding: 0.25em 0.5em; border-radius: 0.25rem; }
        .color-D { background-color: #ffedd5; color: #9a3412; font-weight: 700; padding: 0.25em 0.5em; border-radius: 0.25rem; }
        .color-E { background-color: #fee2e2; color: #991b1b; font-weight: 700; padding: 0.25em 0.5em; border-radius: 0.25rem; }
        .color-ABS { background-color: #e5e7eb; color: #6b7280; font-weight: 700; font-style: italic; padding: 0.25em 0.5em; border-radius: 0.25rem; }

        h2 { font-size: 10px; }

        table th, table td {
        text-align: center; /* Center all table cells */
        vertical-align: middle; /* Center vertically */
    }
    </style>
</head>

<body>
    @php
        $term = isset($student['date']) && $student['date'] ? (date('m', strtotime($student['date'])) <= 6 ? 1 : 2) : '-';
        $subjectMapping = [
            'mazingira' => 'Kutunza afya na mazingira',
            'michezo' => 'Sanaa na michezo',
            'utamaduni' => 'Kuthamini utamaduni sanaa na michezo',
            'jiographia' => 'Jiographia na Mazingira',
            'jamii' => 'Maarifa ya Jamii',
            'maadili' => 'Uraia na Maadili',
            's_kazi' => 'Stadi za Kazi',
        ];

        $gradeComments = [
            'A' => 'Aongeze bidii zaidi ufaulu wake usishuke.',
            'B' => 'Aongeze bidii sana ili apate daraja la juu zaidi.',
            'C' => 'Ajitume kusoma zaidi kwa kuwa ufaulu wake si mzuri.',
            'D' => 'Ufaulu si mzuri aongeze bidii ya kujisomea.',
            'E' => 'Ufualu si mzuri anastahili adhabu.',
        ];
    @endphp

    <div class="report-container">
        <div class="header">
            <h2>OFISI YA WAZIRI MKUU - TAMISEMI</h2>
            <h2>HALMASHAURI YA {{ $student['districtName'] ?? '_____________________' }}</h2>
            <h2>SHULE YA MSINGI {{ $student['schoolname'] ?? '_____________________' }}</h2>
            <h2>RIPOTI YA MAENDELEO YA MWANAFUNZI</h2>
            <h2>MTIHANI WA {{ strtoupper($student['examname'] ?? '_____________________') }}</h2>
        </div>

        <div class="student-info compact">
            <p><strong>JINA:</strong> {{ $student['studentName'] ?? '_________________' }}
                <span style="float: right;"><strong>DARASA:</strong> {{ $student['classname'] ?? '________' }}</span>
            </p>
            <p>
                <span><strong>Muhula:</strong> {{ $term }}</span>
                <span style="float: right;"><strong>TAREHE YA MTIHANI:</strong> {{ $student['date'] ?? '-' }}</span>
            </p>
        </div>

        @php
            $subjects = $student['subjects'] ?? [];
            $totalMarks = 0;
            $subjectsTaken = 0;
            foreach($subjects as $sub) {
                if($sub['mark'] !== null) {
                    $totalMarks += $sub['mark'];
                    $subjectsTaken++;
                }
            }
        @endphp

        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
    <thead style="background-color: #f0f0f0;">
        <tr>
            <th style="text-align: left;">SOMO</th>
            <th style="text-align: center;">JUMLA</th>
            <th style="text-align: center;">DARAJA</th>
            <th style="text-align: center;">NAFASI KWA SOMO</th>
            <th style="text-align: left;">MAELEZO YA DARAJA</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($subjects as $subject)
            @php
                $subjectName = $subject['name'] ?? 'SOMU ISIYOJULIKANA';
                $subjectName = $subjectMapping[$subjectName] ?? $subjectName;
                $subjectName = strtoupper($subjectName);

                $grade = $subject['grade'] ?? Grading::absentGrade();
                $gradeDesc = $subject['gradeDescription'] ?? 'Hajafanya';

                $position = $subject['position'] ?? '-';
                $total = $subject['mark'] ?? '-';
            @endphp
            <tr>
                <td style="text-align: left;">{{ $subjectName }}</td>
                <td style="text-align: center;">{{ $total }}</td>
                <td style="text-align: center;">
                    <span class="color-{{ $grade }}">{{ $grade == Grading::absentGrade() ? 'Hajafanya' : $grade }}</span>
                </td>
                <td style="text-align: center;">{{ $position }}</td>
                <td style="text-align: left;"><span class="color-{{ $grade }}">{{ $gradeDesc }}</span></td>
            </tr>
        @endforeach
    </tbody>
</table>

        <div class="marks-summary compact">
            <p><strong>Jumla ya alama:</strong> {{ $totalMarks }} Kati ya {{ $subjectsTaken * 50 }}</p>
            <p><strong>Wastani:</strong> {{ $subjectsTaken > 0 ? number_format($totalMarks / $subjectsTaken, 2) : '-' }}</p>
            <p><strong>Daraja:</strong> <span class="color-{{ $student['grade'] ?? '' }}">{{ $student['grade'] ?? '-' }}</span></p>
            <p><strong>Nafasi:</strong> {{ $student['position'] ?? '-' }} Kati ya {{ $studentsTakenExam }}</p>
        </div>

        <div class="remarks compact">
            <p><strong>Maoni ya mwalimu wa darasa:</strong> <u>{{ $gradeComments[$student['grade']] ?? '' }}</u></p>
            <p><strong>Maoni ya Mwalimu mkuu:</strong> _____________________________________________________________________________________</p>
            <br>
            <p>
                <span style="float: left;"><strong>Tarehe ya kufunga:</strong> {{ $closingDate ?? '__________' }}</span>
                <span style="display: block; text-align: right;"><strong>Tarehe ya kufungua:</strong> {{ $openingDate ?? '__________' }}</span>
            </p>
        </div>

        <div class="footer">
            <p><strong>Jina la Mzazi:</strong> ___________________________________________________ <strong>Tarehe:</strong>_____________________</p>
            <p><strong>Jina la Mwanafunzi:</strong> ______________________________________________ <strong>Darasa:</strong>_____________________</p>
            <p><strong>Maoni ya Mzazi:</strong> ___________________________________________________________________________________</p>
            <p>__________________________________________________________________________________________________</p>
            <p>__________________________________________________________________________________________________</p>
            <p style="overflow: auto;">
                <span style="float: left;"><strong>Namba ya Shule:</strong> {{ $schoolContact ?? '_____________________' }}</span>
                <span style="float: right;"><strong>Saini ya Mzazi:</strong> _____________________</span>
            </p>
        </div>


        <table style="margin-top: 3rem; width: 100%;">
            <tr>
                <td style="border: none; padding: 0; white-space: nowrap; font-weight: bold;">Kata hapa</td>
                <td style="border: none; border-top: 1px dashed #000;">&nbsp;</td>
                <td style="border: none; padding: 0; white-space: nowrap; font-weight: bold;">Kata hapa</td>
            </tr>
        </table>
        <div style="text-align: center">
            <p><strong>Designed by:</strong> rmstechnology.co.tz +255 786 283 282 / +255 736 102 030</p>
            <p><strong>Printed Date:</strong> {{ date('F j, Y') }}</p>
        </div>
    </div>
</body>
</html>
