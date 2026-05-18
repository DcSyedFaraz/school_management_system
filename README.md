# LMS — School Marks Management System

Tanzania primary school results management system built with Laravel.

---

## Tech Stack

- **Framework:** Laravel 10 (PHP)
- **Frontend:** Blade templates, Tailwind CSS
- **Excel:** maatwebsite/excel + PhpSpreadsheet
- **PDF:** Blade → PDF (DomPDF or similar via Laravel)
- **Database:** MySQL / MariaDB

---

## Project Structure

```
app/
  Http/Controllers/
    admin/           — Admin-side controllers
    user/            — Teacher/school-side controllers
  Imports/
    MarksImport.php  — Excel import handler (all classes)
  Exports/
    MarksExport.php          — Admin school-level marks export
    MarksUserExport.php      — Teacher per-student marks export
    StudentDataExport.php    — Admin student-data export
    SchoolReportExport.php   — Admin school PDF/Excel report export
    TeacherReportExport.php  — Teacher school report export
    SubjectExport.php        — Admin subject-wise export
    SubjectUserExport.php    — Teacher subject-wise export
  Models/
    Marks.php        — marks table model (PK: markId)
    Ranks.php        — rank/grade boundaries (A–E)
    Grades.php       — classes (Darasa la Kwanza … Saba)
    Exams.php        — exam types
    Schools.php
    Regions/Districts/Wards models

config/
  subjects.php       — maps classId (string) → array of DB column names per class

database/migrations/
  2024_07_24_142624_create_marks_table.php  — marks schema
  2026_05_18_000000_make_mark_subjects_nullable.php — makes subject cols nullable

public/excel/
  marks_template.xlsx  — single universal import template (all subject columns)

resources/views/
  admin/             — Admin Blade views
  user/              — Teacher/school Blade views
  pdf/               — PDF report views (Swahili)
  pdf/english/       — PDF report views (English)
  modals/            — Shared modal partials
```

---

## Database: `marks` Table

| Column | Type | Notes |
|--------|------|-------|
| markId | bigint PK | auto-increment |
| examDate | string | YYYY-MM-DD |
| classId | integer | 1–7 |
| studentName | string | |
| gender | string | 'M' or 'F' |
| hisabati | integer nullable | Math (cls 3–7) |
| kiswahili | integer nullable | Kiswahili (cls 3–7) |
| sayansi | integer nullable | Science (cls 3–7) |
| english | integer nullable | English (all) |
| jamii | integer nullable | Social Studies (cls 6–7) |
| maadili | integer nullable | Ethics (cls 3, 6–7) |
| kuhesabu | integer nullable | Arithmetic (cls 1–2) |
| kusoma | integer nullable | Reading (cls 1–2) |
| kuandika | integer nullable | Writing (cls 1–2) |
| mazingira | integer nullable | Environment (cls 1–2) |
| utamaduni | integer nullable | Culture (cls 2) |
| michezo | integer nullable | Sports (cls 1) |
| jiographia | integer nullable | Geography (cls 3–5) |
| smichezo | integer nullable | Sports (cls 3) |
| historia | integer nullable | History (cls 4–5) |
| s_kazi | integer nullable | Vocational Skills (cls 6) |
| total | double | sum of non-null subjects |
| average | double nullable | avg of non-null subjects; **NULL = fully absent** |
| examId | string | FK to exams |
| userId, regionId, districtId, wardId, schoolId | integers | location/user refs |
| isActive, isDeleted | string | soft-delete flags ('0'/'1') |

---

## Subject Configuration

File: `config/subjects.php`

```php
'1' => ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'michezo'],
'2' => ['kuhesabu', 'kusoma', 'kuandika', 'english', 'mazingira', 'utamaduni'],
'3' => ['hisabati', 'kiswahili', 'sayansi', 'english', 'maadili', 'jiographia', 'smichezo'],
'4' => ['hisabati', 'kiswahili', 'sayansi', 'english', 'jiographia', 'historia'],
'5' => ['hisabati', 'kiswahili', 'sayansi', 'english', 'jiographia', 'historia'],
'6' => ['hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili', 's_kazi'],
'class_default' => ['hisabati', 'kiswahili', 'sayansi', 'english', 'jamii', 'maadili'], // used for class 7
```

---

## Grading System

Grades are stored in the `ranks` table (5 tiers). The `assignGrade($marks)` helper in each view/export checks the Ranks table ranges.

Standard grade boundaries (configurable in DB):
| Grade | Range |
|-------|-------|
| A | 41–50 |
| B | 31–40 |
| C | 21–30 |
| D | 11–20 |
| E | 0–10 |

**Pass/Fail rules:**
- Class ≤ 4: D and above = FAULU (pass); E = FELI (fail)
- Class > 4: C and above = FAULU; D and E = FELI

---

## Absent vs 0 Mark Logic

**Critical rule (applied across entire app since May 2026):**

| DB Value | Meaning | Display |
|----------|---------|---------|
| `NULL` subject column | Student was absent for that subject | `ABS` |
| `0` subject column | Student sat and scored zero | `0` + grade `E` |
| `NULL` average | Student fully absent (all subjects NULL) | `ABS` |
| `0` average | Student scored 0 on all subjects (present) | grade `E` |

**Absence detection in code:**
- ✅ `$mark['average'] === null` → absent
- ✅ `$mark[$subject] === null` → absent for that subject
- ❌ `$mark['average'] == 0` → **DO NOT use** (this was the old buggy check)
- ❌ `$mark[$subject] > 0` → **DO NOT use** (excludes valid 0 marks)

**SQL queries:**
- ✅ `CASE WHEN average IS NOT NULL THEN average END` → excludes absent
- ✅ `CASE WHEN $subject IS NOT NULL THEN $subject END` → excludes absent subjects
- ✅ `->whereNotNull('average')` → filter absent students
- ❌ `CASE WHEN average > 0` → old pattern, do not use

---

## Excel Import

**Template:** `public/excel/marks_template.xlsx` (one file for all classes)

**Import class:** `app/Imports/MarksImport.php`
- Uses `config/subjects.php` to determine which columns to read per class
- Blank cell → NULL stored in DB (absent)
- `0` → stored as 0 (real mark)
- Validation: `nullable|numeric|min:0|max:50` per subject

**Upload route:** `POST /uploads/file` → `UploadController@fileUpload`

**Manual entry route:** `POST /uploads/save` → `UploadController@saveUpload`

---

## Routes & Pages

### Admin Side (`/admin-dashboard/...`)
| URL | Controller | View |
|-----|-----------|------|
| `/admin-dashboard` | `DashboardController@adminDashboard` | `admin/dashboard` |
| `/admin-dashboard/reports` | `ReportController@reports` | `admin/reports` |
| `/admin-dashboard/student-data` | `ReportController@studentData` | `admin/studentData` |
| `/admin-dashboard/subject-report` | `SubjectReportController@reports` | `admin/subjectReport` |
| `/dashboard/detailed-report` | `DetailedReportController@reports` | `admin/detailedReport` |

### Teacher/School Side (`/dashboard/...`)
| URL | Controller | View |
|-----|-----------|------|
| `/dashboard` | `UserDashboardController@adminDashboard` | `user/dashboard` |
| `/dashboard/reports` | `UserReportController@reports` | `user/reports` |
| `/dashboard/uploads` | `UploadController@uploads` | `user/uploads` |
| `/dashboard/teacher-detailed-report` | `UserDetailedReportController@reports` | `user/detailedReport` |
| `/dashboard/teacher-subject-report` | `UserSubjectReportController@reports` | `user/subjectReport` |

---

## PDF Reports

Generated from Blade views via DomPDF. Controller fetches data and returns `view()->render()` + PDF conversion.

| Report | Swahili View | English View |
|--------|-------------|--------------|
| Single student | `pdf/report.blade.php` | `pdf/english/report.blade.php` |
| All students | `pdf/report-all.blade.php` | `pdf/english/report-all.blade.php` |

---

## Key Conventions

| Swahili | English | Meaning |
|---------|---------|---------|
| Darasa | Class | Grade/class level |
| Mtihani | Exam | Examination |
| ABS | ABS | Absent (Hayupo) |
| HYP | HYP | Absent in PDF (Hajafanya) |
| Hayupo | N/A | Not present |
| FAULU | PASS | Passed |
| FELI | FAIL | Failed |
| Jumla | Total | Sum of marks |
| Wastani | Average | Average marks |
| Daraja | Grade | A/B/C/D/E |

---

## Significant Changes Log

| Date | Change | Files |
|------|--------|-------|
| 2026-05 | Fixed D→E grade inflation: removed hardcoded `if ($marks == 10) return 'E'` | `resources/views/user/detailedReport.blade.php` |
| 2026-05 | Replaced 6 class-specific Excel templates with one universal template | `public/excel/marks_template.xlsx`, `excelfileModal.blade.php` |
| 2026-05 | Changed absent/0 logic: blank=NULL=absent, 0=real mark throughout entire app | All exports, controllers, blade views, import |
| 2026-05 | DB migration: subject columns changed to `nullable default(null)` | `2026_05_18_000000_make_mark_subjects_nullable.php` |

---

## How to Guide Future AI Sessions

Tell the AI: **"Read README.md first for full project context before making any changes."**

Key files to always check before modifying:
1. `README.md` — this file
2. `config/subjects.php` — which subjects belong to which class
3. `app/Imports/MarksImport.php` — import logic
4. `database/migrations/2024_07_24_142624_create_marks_table.php` — DB schema
