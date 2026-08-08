<?php

/*
|--------------------------------------------------------------------------
| Grading — SINGLE SOURCE OF TRUTH
|--------------------------------------------------------------------------
|
| This file is the ONLY place grade letters, band boundaries, pass/fail
| rules and grade wording are defined. The `ranks` DB table and
| config/ranks.php are legacy and must not be read by application code.
|
| TWO SCALES COEXIST:
|   'subject' — a single subject mark, 0..50   (unchanged, legacy bands)
|   'total'   — a student's SUM of subject marks, 0..300  (the "Daraja")
|
| Adding/renaming/removing a grade letter = edit THIS FILE ONLY. Every
| letter list, tally loop, legend and description is derived from the
| 'bands' + 'descriptions' keys below via App\Services\GradingService.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Band tables
    |--------------------------------------------------------------------------
    | Bands MUST be listed best-first (highest 'min' first). 'max' is used
    | for LEGEND DISPLAY ONLY — band selection uses 'min' with a descending
    | scan, so fractional aggregate values (e.g. a school mean of 180.6)
    | can never fall into a gap between two bands, and there is no
    | < vs <= ambiguity at any call site.
    |
    | Both scales MUST declare the same set of letters, in the same order.
    | GradingService::assertConsistent() enforces this (see the unit test).
    */
    'scales' => [

        'subject' => [
            'min'   => 0,
            'max'   => 50,
            'bands' => [
                'A' => ['min' => 41, 'max' => 50],
                'B' => ['min' => 31, 'max' => 40],
                'C' => ['min' => 21, 'max' => 30],
                'D' => ['min' => 11, 'max' => 20],
                'E' => ['min' => 0,  'max' => 10],
            ],
        ],

        'total' => [
            'min'   => 0,
            'max'   => 300,
            'bands' => [
                'A' => ['min' => 241, 'max' => 300],
                'B' => ['min' => 181, 'max' => 240],
                'C' => ['min' => 121, 'max' => 180],
                'D' => ['min' => 61,  'max' => 120],
                'E' => ['min' => 0,   'max' => 60],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pass / fail
    |--------------------------------------------------------------------------
    | Expressed as LETTERS THAT FAIL, never as a number. Derived thresholds
    | (the old "borderLine") are computed from these by the service.
    |
    | 'upper' applies when classId >= upper_class_from (historically: >4).
    | 'lower' applies otherwise.
    */
    'pass' => [
        'upper_class_from' => 5,
        'failing_grades'   => [
            'upper' => ['D', 'E'],
            'lower' => ['E'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Absence
    |--------------------------------------------------------------------------
    | A student is ABSENT only when they have NO marks at all (total/average
    | is NULL). A student who sat fewer subjects is graded on their RAW
    | total and simply scores lower — no scaling, no pro-rating.
    */
    'absent' => [
        'grade'       => 'ABS',
        'label'       => ['sw' => 'Hayupo',    'en' => 'Absent'],
        'status'      => ['sw' => '-',         'en' => '-'],
        'description' => ['sw' => 'Hajafanya', 'en' => 'Did not sit'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Grade descriptions
    |--------------------------------------------------------------------------
    | Keys MUST match the band letters exactly.
    */
    'descriptions' => [
        'A' => ['sw' => 'Bora',        'en' => 'Excellent'],
        'B' => ['sw' => 'Nzuri sana',  'en' => 'Very good'],
        'C' => ['sw' => 'Nzuri',       'en' => 'Good'],
        'D' => ['sw' => 'Inaridhisha', 'en' => 'Satisfactory'],
        'E' => ['sw' => 'Dhaifu',      'en' => 'Poor'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pass/fail labels
    |--------------------------------------------------------------------------
    | 'sw' is used by resources/views/{admin,user}/** and the Swahili PDFs.
    | 'en' is used by resources/views/pdf/english/**.
    */
    'labels' => [
        'sw' => ['pass' => 'FAULU', 'fail' => 'FELI'],
        'en' => ['pass' => 'PASS',  'fail' => 'FAIL'],
    ],

    'default_locale' => 'sw',

    /*
    |--------------------------------------------------------------------------
    | Strict scale checking
    |--------------------------------------------------------------------------
    | When true, gradeSubject() THROWS if handed a value above the subject
    | max (50) — i.e. someone passed a 0..300 total to the 0..50 method.
    | When false it clamps and logs a warning. Defaults to app.debug so
    | developers get a loud failure and production degrades gracefully.
    */
    'strict' => env('GRADING_STRICT', null),
];
