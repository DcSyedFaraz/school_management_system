# CLAUDE.md — Project structure and grading system context

This file exists so a future agent (or the next `claude` session) doesn't have
to re-discover the codebase layout or the grading-system migration history
from scratch. Two parts: **Part 1** is a map of where things live (read this
first, any time). **Part 2** is what changed in the grading system and why
(read this before touching anything related to grades, Daraja, totals,
averages, or the `ranks`/`grading` system).

---

# Part 1 — Project structure

Laravel 10 school Result Management System (RMS) for Tanzanian primary
schools. Two roles: **admin** (region/district/ward/school oversight, national
level) and **user** (a teacher, scoped to one school). Swahili-first UI with
English-mirrored PDF report views. **No route-level auth middleware** — every
controller method checks `Session::get('adminLoggedin')` /
`Session::get('loggedin')` manually and redirects/aborts inline. There are no
Eloquent Policies/Gates in use.

## `app/Http/Controllers/`

Split into `admin/` and `user/`. Most report/dashboard features have a
**matching pair** — e.g. `ReportController` (admin) / `UserReportController`
(user) — implementing the same report type: admin controllers filter across
many schools by region/district/ward, user controllers are pre-scoped to the
logged-in teacher's own `schoolId`. When fixing a bug in one half of a pair,
check whether the same bug exists in the other half.

### `admin/`
- `DashboardController.php` — login (`signIn`/`login`/`logout`), forgot/reset
  password (`forgotPassword`, `forgotEmail`, `resetPasswordPage`,
  `resetPassword`), `changePassword`, `changeLang`, admin dashboard
  (`adminDashboard`, `adminDashboardFilter`).
- `DistrictController.php`, `RegionController.php`, `WardController.php`,
  `SchoolController.php` — CRUD for each org-hierarchy level, all the same
  method shape: list, `save`, `update`, `{x}Info`, `delete`.
- `UserController.php` — manage teacher accounts (`teachers`) and admin
  accounts (`admins`, `saveAdmin`, `updateAdmin`, `adminInfo`, `adminActivity`
  for enable/disable).
- `ReportController.php` — main admin results screen: school-ranking report
  (`reports`, `filterReport`, `downloadReport`), per-student data export
  (`studentData`, `studentDataFilter`, `downloadStudentData` — queues
  `DownloadStudentDataJob`), English report entry points
  (`studentReportEnglish`, `schoolReportEnglish`).
- `DetailedReportController.php` — per-school per-subject breakdown:
  `reports`, `filterReport`, `downloadAdminReport` (→ `SchoolReportExport`).
- `SubjectReportController.php` — per-subject comparison report: `reports`,
  `filterReport`, `downloadSubjectReport` (→ `SubjectExport`); has a
  commented-out cached-report variant.

### `user/`
- `UserDashboardController.php` — teacher dashboard (`adminDashboard`,
  `adminDashboardFilter` — same method names as the admin dashboard
  controller, scoped to one school; unrelated classes, just matching naming).
- `UploadController.php` — marks upload: list/filter (`uploads`,
  `filterUploads`), manual entry (`saveUpload`, `updateUpload`,
  `uploadInfo`), delete/bulk delete (`deleteUpload`, `deleteBulkUpload`),
  Excel bulk import (`fileUpload` → `MarksImport`), `getSubjectsForClass`
  (AJAX helper).
- `UserReportController.php` — teacher's school-level report: `reports`,
  `filterReport`, `downloadTeacherReport` (→ `TeacherReportExport`), plus
  `printAllReport`/`printAllReportEnglish` (whole-class PDF report cards).
- `UserDetailedReportController.php` — teacher's per-subject-per-student
  report: `reports`, `filterReport`, `downloadTeacherReport`.
- `UserSubjectReportController.php` — teacher's per-subject report:
  `reports`, `filterReport`, `getSubjectsForClass`,
  `downloadTeacherSubjectReport` (→ `SubjectUserExport`).
- `PrintController.php` — generates individual/batch PDF report cards:
  `printReport` (Swahili), `printReportEnglish`/`studentEnglishReport`
  (English). See Part 2 for why this controller now rebuilds grades from the
  DB instead of trusting client JSON.

## `app/Models/`

All use non-standard primary keys (legacy schema) and mostly disable
timestamps.

| Model | Table | PK | Notes |
|---|---|---|---|
| `User.php` | `users` | `userId` | `$timestamps=false`; `userType` distinguishes admin/user (checked via Session, not Gates). |
| `Marks.php` | `marks` | `markId` | `$timestamps=false`; **one row per student per exam**, subject marks are individual integer columns (not normalized), `total`/`average` are stored computed columns. Relations: `school()`→Schools, `class()`→Grades (classId→gradeId), `exam()`→Exams. |
| `MarkGrade.php` | `mark_grades` | default `id` | Newer normalized table: one row per `markId`+`subject` holding the computed grade letter, FK cascade-delete to `marks`. Populated by the `ProcessMarksGrades` command. |
| `Schools.php` | `schools` | `schoolId` | No FK columns to region/district/ward in its own schema — that linkage lives only on `users`/`marks` (denormalized). |
| `Regions.php` | `regions` | `regionId` | |
| `Districts.php` | `districts` | `districtId` | PK is a plain integer column, not auto-increment like its siblings. |
| `Wards.php` | `wards` | `wardId` | |
| `Exams.php` | `exams` | `examId` | |
| `Grades.php` | `grades` | `gradeId` | Represents school **class/darasa levels** (1–7) — NOT student grades/marks. Don't confuse with the grading system. |
| `Subjects.php` | `subjects` | `subjectId` | Master subject list table; superseded in practice by `config/subjects.php` for actual grading logic. |
| `Ranks.php` | `ranks` | `rankId` | **Deprecated** — see Part 2. Do not query this or read `config('ranks')` (deleted). |

All lookup tables share an `isActive`/`isDeleted` string-flag pattern
(`"1"`/`"0"`, not real booleans, no real soft-deletes).

## `app/Exports/` (Maatwebsite Excel)

All implement `FromCollection, WithHeadings, WithMapping, WithColumnWidths`;
constructors take filter params. Pattern: **admin** exports take
region/district/ward filters and aggregate across schools (one row per
school); **user/teacher** exports are pre-scoped to one `schoolId` and
produce per-student or per-subject rows.

- `MarksExport.php` — admin, raw marks/subject scores across region/district, one row per school.
- `SubjectExport.php` — admin, per-subject comparison (`SubjectReportController::downloadSubjectReport`).
- `SchoolReportExport.php` — admin, per-school detailed report (`DetailedReportController::downloadAdminReport`).
- `StudentDataExport.php` — admin, per-student raw dump; only export using `WithChunkReading` (large dataset; backed by a queued job).
- `TeacherReportExport.php` — user, school-level report scoped to one `schoolId` (`UserReportController::downloadTeacherReport`).
- `MarksUserExport.php` — user, marks export scoped to the teacher's own school/class.
- `SubjectUserExport.php` — user, per-subject export scoped to one school (`UserSubjectReportController::downloadTeacherSubjectReport`).

(The five `OLD*`/`OLD_*` export classes that used to exist here were deleted
as dead code during the Part 2 migration — don't recreate them.)

## `app/Imports/`

- `MarksImport.php` — Excel bulk-upload importer (`ToCollection,
  WithHeadingRow, WithValidation, SkipsEmptyRows`). Constructor takes
  `$requestData, $userId, $userRegion, $userDistrict, $userWard, $userSchool`
  — school/location context is injected server-side from the session, never
  trusted from the uploaded file. Used by `user/UploadController::fileUpload`.

## `app/Jobs/`

- `DownloadStudentDataJob.php` — queued job building `StudentDataExport`,
  storing the xlsx under `storage/public/exports/`, caching the path under
  `student_data_export_{examId}` for 1 hour. Backs
  `admin/ReportController::downloadStudentData`.
- `FilterReportJob.php` — queued job aggregating `Marks` (avg by
  school/region/district/ward for an exam/date range), caches the result
  under a `filtered_report_{...}` key. Supports admin report filter screens
  on large datasets.

## `app/Services/` and `app/Facades/`

Exactly one service + one facade, added during the Part 2 grading migration:

- `app/Services/GradingService.php` — single source of truth for converting
  marks → grade letters. See Part 2 for full detail.
- `app/Facades/Grading.php` — thin facade (`Grading::gradeTotal(...)` etc.),
  bound as a singleton in `AppServiceProvider`.

**Do not add anything else here without checking Part 2 first** — this
directory exists specifically to end a pattern of duplicated grading logic;
adding a second unrelated service is fine, but re-adding grading logic
outside `GradingService` is exactly what was just cleaned up.

## `app/Providers/`

- `AppServiceProvider.php` — registers `GradingService` as a singleton
  (config-injected) and aliases it to `'grading'` for the `Grading` facade.
  `boot()` is empty.
- `AuthServiceProvider.php`, `EventServiceProvider.php`,
  `BroadcastServiceProvider.php` — stock Laravel scaffolding, no custom
  gates/policies/event mappings/broadcasting in use.
- `RouteServiceProvider.php` — stock; defines `HOME`, an `api` rate limiter,
  loads `routes/api.php` and `routes/web.php`.
- `SubjectsIntegrityServiceProvider.php` — watchdog: on `boot()`, snapshots
  `config('subjects')` to `storage/app/subjects_snapshot.json` on first run,
  then diffs live config against that snapshot on every subsequent boot; if a
  class's subject list changed it logs `Log::critical` and caches violations
  under `subjects_integrity_violations` for 24h (surfaced on the admin login
  view). Registered after `AppServiceProvider` in `config/app.php`.

## `database/seeders/`

Test/dev seed data — **fully verified end-to-end** against a real local
MySQL database (see "Seeders — verified" below for exactly what was tested).

- `LocationsAndUsersSeeder.php` — creates one region/district/ward, two
  schools, one admin account, and one teacher account per school:
  - **Admin:** `admin@gmail.com` / `12345678` (`userType='A'`, not scoped to
    any school — sees everything).
  - **Teacher A:** `user@gmail.com` / `12345678` — scoped to "Shule ya
    Msingi Mfano A".
  - **Teacher B:** `user2@gmail.com` / `12345678` — scoped to "Shule ya
    Msingi Mfano B".
  - Safe to re-run: every row is looked up by natural key first, no
    duplicates on a second run.
- `MarksSeeder.php` — depends on the seeder above having run first. Seeds 6
  `Grades` rows (classes 1–6, matching `config/subjects.php`), 2 `Exams`,
  and 15 fake students per class per school per exam (360 `Marks` rows
  total). Deliberately generates a mix of fully-scored, partially-absent
  (~10% chance per subject), and fully-absent (~1-in-15 students) records —
  the three cases `GradingService::isAbsent()` must handle. Clears its own
  previously-seeded rows (by schoolId+examId) before regenerating, so
  re-running doesn't pile up duplicate students.
- Run both with `php artisan db:seed` (registered in `DatabaseSeeder.php`),
  or individually with `php artisan db:seed --class=Database\Seeders\LocationsAndUsersSeeder`.
- **Important pattern if you add more seeders here:** none of the models in
  this app declare `$fillable` (base Eloquent default is `$guarded =
  ['*']`), so `Model::create([...])` / `firstOrCreate([...])` will throw
  `MassAssignmentException`. Every write in these seeders uses the same
  `new Model; $model['field'] = $value; $model->save();` pattern the rest
  of the codebase already uses (see `MarksImport`, `UploadController`) —
  follow that pattern, don't reach for `create()`.

### Seeders — verified

Actually tested against a real MySQL database in this session, not just
read for syntax:
- `php artisan migrate` — **found and fixed a pre-existing bug** unrelated
  to the seeders: `database/migrations/2025_02_28_131137_create_mark_grades_table.php`
  declared `markId` as `unsignedInteger`, but `marks.markId` (from
  `$table->id('markId')`) is `unsignedBigInteger` — the FK constraint
  failed on any fresh `migrate` with `SQLSTATE[HY000]: General error: 3780`.
  Fixed to `unsignedBigInteger('markId')`. If you're setting up a fresh
  environment and hit that error, this fix is already in place — check
  `git log` if it somehow isn't.
- `php artisan db:seed` — ran clean, twice in a row (idempotency confirmed:
  same row counts both times — 3 users, 1 region, 6 grades, 2 exams, 360
  marks).
- Logged in via HTTP as both `admin@gmail.com` and `user@gmail.com` (cookie
  jar + CSRF token extraction, not just curl-without-a-session) and
  confirmed real page loads (not error pages) for: admin dashboard, admin
  reports, admin student-data, teacher dashboard, teacher reports, teacher
  uploads (with an explicit filter to reach the seeded exam/class
  combination — the *default* filter combo on the uploads page didn't match
  any seeded rows, which is expected, not a bug — see the filter logic in
  `UploadController::uploads()`).
- Downloaded and confirmed valid `.xlsx` output (`file` command reports
  "Microsoft Excel 2007+") from both `MarksUserExport` (teacher) and
  `MarksExport` (admin).
- **Directly confirmed the total-based grading migration (Part 2 of this
  file) works correctly on real seeded data**: a student with total=190
  rendered grade B, total=163 rendered C, total=106 rendered D — all
  matching the new bands exactly. A fully-absent seeded student rendered
  `ABS` in every column with no error. An admin school-level report row
  showed a mean total of 155.29 correctly graded C, with **both** summary
  boxes ("Wastani Ya Daraja" and "Wastani Ya Ufaulu") showing the same
  grade — confirming the two-boxes-disagreeing bug fixed in Part 2 stayed
  fixed.
- The local `lms` MySQL database and its seeded data were **left in place**
  after this verification (not torn down) — the user asked for this data to
  test with, so it's there to use. The test HTTP server
  (`php artisan serve`) was stopped afterward.

## `app/Console/Commands/`

- `ProcessMarksGrades.php` — chunks through `Marks` (`chunkById(5000, ...,
  'markId')`) recomputing `total`/`average` from the subject columns for
  each row, per `config/subjects.php`'s class→subject mapping. See Part 2 —
  this command was previously broken (dead accumulation loop, would have
  zeroed every `average`) and has been restored as the canonical backfill.
  Run via `php artisan app:process-marks-grades`. **Back up `marks` before
  running against production data.**

## `resources/views/`

- **`admin/`** — admin pages: `layout.blade.php`/`header.blade.php`/
  `headerScripts.blade.php`/`sidebar.blade.php` (shared chrome),
  `index.blade.php`/`dashboard.blade.php` (landing/dashboard), `regions.blade.php`,
  `schools.blade.php`, `users.blade.php` (CRUD list pages), `reports.blade.php`,
  `detailedReport.blade.php`, `subjectReport.blade.php`, `studentData.blade.php`
  (the four admin report/export screens), `changepassword.blade.php`,
  `forgot.blade.php`, `resetPassword.blade.php` (auth flow),
  `help.blade.php`, `mwongozo.blade.php` ("guide"), `results.blade.php`
  (public NECTA-results placeholder).
- **`user/`** — teacher pages: `dashboard.blade.php`, `reports.blade.php`,
  `detailedReport.blade.php`, `subjectReport.blade.php` (teacher equivalents
  of the admin report screens, scoped to one school), `uploads.blade.php`
  (marks upload/manage screen — the single biggest, most edited view in the
  Part 2 migration alongside `reports.blade.php`).
- **`pdf/`** — Swahili PDF report-card templates (dompdf): `report.blade.php`
  (single student), `report-all.blade.php` (whole-class batch).
- **`pdf/english/`** — English mirrors of the above, same filenames. Pattern:
  every Swahili PDF view has a same-named English counterpart here, selected
  by the controller's `*English` methods.
- **`modals/`** — Bootstrap modal partials included into CRUD pages:
  `newAdmin`/`editAdmin`, `newSchool`/`editSchool`, `newEntry`/`editEntry`
  (generic region/district/ward/exam/grade form, reused across several admin
  CRUD controllers), `newUpload`/`editUpload` (manual marks entry),
  `uploadModal`/`excelfileModal` (Excel bulk upload UI),
  `changePasswordModal`, `bulkDeleteModel` (bulk delete confirm),
  `rejectModal`, `activityModal` (enable/disable confirm).
- **`emails/`** — `forgotTemplate.blade.php` (password reset, via
  `ForgotMail`), `newUserTemplate.blade.php` (welcome/credentials, via
  `NewUserMail`).
- `welcome.blade.php` — stock Laravel default page, unused leftover.

## `config/`

Standard Laravel + package configs present (`app.php`, `auth.php`,
`database.php`, `session.php`, etc.; `dompdf.php` for PDF report cards,
`excel.php` for all Exports/Imports, `datatables.php` for admin/user list
pages).

Custom, app-specific:
- `subjects.php` — maps class/grade key (`'1'`..`'6'`, plus
  `'class_default'`) to its list of subject column names on `marks`. Classes
  1–2 use one subject set, 3–6 use another (varies slightly). **Always 6
  subjects per class** — this fixed count is relied on by
  `GradingService::maxTotal()` to derive the 0–300 total ceiling
  (`6 × 50`). If this ever changes to a variable subject count per class,
  the total bands in `grading.php` will need reviewing.
- `grading.php` — **single source of truth for grading**, added in the Part
  2 refactor. See Part 2 for full detail. Never read directly — always go
  through `GradingService`/`Grading`.

## `database/migrations/` — schema shape

- **`users`** (PK `userId`) → nullable `schoolId`/`regionId`/`districtId`/
  `wardId` scope a teacher to their school (loosely typed, not real FKs).
- **`regions`** → **`districts`** → **`wards`** → **`schools`** — the
  four-level Tanzanian admin hierarchy. `schools` itself has no FK columns to
  region/district/ward; that linkage only exists via `users.schoolId`/
  `marks.schoolId` plus denormalized region/district/ward IDs duplicated onto
  `users` and `marks` directly.
- **`grades`** (PK `gradeId`) — class/darasa levels (1–7).
- **`exams`** (PK `examId`) — exam definitions.
- **`ranks`** (PK `rankId`) — legacy grade-band table, superseded by
  `config/grading.php`. Should not be queried by new code.
- **`marks`** (PK `markId`) — the central fact table: one row per student
  per exam, individual integer columns per subject (hisabati, kiswahili,
  sayansi, english, jamii, maadili, kuhesabu, kusoma, kuandika, mazingira,
  utamaduni, michezo, jiographia, smichezo, historia, s_kazi), `total`/
  `average` double columns, denormalized `examId`/`userId`/`regionId`/
  `districtId`/`wardId`/`schoolId`/`classId`, plus `examDate`,
  `studentName`, `gender`.
- **`mark_grades`** (default `id` PK, added 2025-02-28) — normalized
  per-`markId`+`subject` grade-letter cache, FK cascade-delete to `marks`,
  indexed on `markId` and `subject`. Populated by `ProcessMarksGrades`.
- **2026-05-18 migrations** (`make_mark_subjects_nullable.php`,
  `make_average_nullable.php`) — changed all 16 subject-mark columns and
  `average` from `nullable()->default(0)` to `nullable()->default(null)`, so
  an un-entered mark is now genuinely `NULL` (distinguishable from an
  explicit 0). This is the schema change that made
  `GradingService::isAbsent()`'s null-based absence rule possible — **any
  code doing arithmetic on marks must handle `NULL` explicitly, not just
  `0`.**
- Standard Laravel infra tables also present: `password_reset_tokens`,
  `failed_jobs`, `personal_access_tokens` (Sanctum — present but no evidence
  of API token usage in app code), `jobs` (queue table backing the two Jobs
  above).

## `routes/web.php` — routing pattern

Flat file, **no route groups, no named prefixes, no `auth` middleware** —
every protected method checks the session manually (see top of Part 1).
Feature groupings (~95 route lines):
- **Misc/static** (~10): `/help`, `/index`, `/mwongozo` (registered twice),
  `/results` + 4 NECTA placeholder sub-routes, `/done` (calls
  `Artisan::call('optimize:clear')` — an ungated maintenance endpoint, worth
  knowing about if you're auditing for exposed admin actions).
- **Auth** (~11): login/logout, forgot/reset password, change password,
  language switch, dashboard entry points.
- **Admin CRUD for org hierarchy** (~20): 5 routes each for
  teachers/admins/regions/districts/wards/schools, consistent list/save/
  update/delete/`{x}Info` pattern.
- **Admin reports** (~13): `ReportController` (6), `DetailedReportController`
  (3), `SubjectReportController` (4, incl. one dead commented-out route).
- **Teacher/user routes** (~20): `UserDashboardController` (2),
  `UserReportController` (5), `UserDetailedReportController` (3),
  `UserSubjectReportController` (4), `UploadController` (7).
- **PDF printing** (2): `/printReport`, `/report.student.english` →
  `PrintController`.

**Known quirk, not a live bug:** both `Route::get('/dashboard', [DashboardController::class,
'dashboard'])` (line ~92) and `Route::get('/dashboard', [UserDashboardController::class,
'adminDashboard'])` (line ~134) register the identical URI+method. Laravel
lets the **last-registered** one win, so the second silently shadows the
first — and `DashboardController` doesn't even define a `dashboard()`
method, so the first registration was already dead on arrival. Not a runtime
crash risk (the dead route is simply unreachable), but if you're asked to
add a third `/dashboard` route or debug "why isn't my dashboard change
showing up," this is why.

## Top-level

- **`CLAUDE.md`** (this file) — read first.
- **`.claude/`** — only an empty `worktrees/` directory; no skills/commands
  checked into the repo.
- **`.env.example`** — standard Laravel env template; check the real `.env`
  for actual local DB/mail/queue/cache driver config.
- **`composer.json`** notable packages: `barryvdh/laravel-dompdf` (PDF
  report cards), `maatwebsite/excel` (all Exports/Imports),
  `yajra/laravel-datatables-oracle` (admin/user list-page datatables),
  `stichoza/google-translate-php` (likely backs `changeLang`/Swahili-English
  mirroring), `setasign/fpdf`/`fpdi` (lower-level PDF manipulation, used by
  `PrintController` to merge per-student PDFs into one batch file),
  `laravel/sanctum` (present, no evidence of actual API token usage).
- **`lms.zip`** at repo root — a stray archive, not part of the app.
  Confirm with the user before deleting; don't assume it's safe to remove
  without asking, and don't unzip/trust its contents as current source.

---

# Part 2 — Grading system migration context

## What happened

The user asked to change how the overall student grade ("Daraja") is computed:
**from AVERAGE (0–50) to TOTAL (0–300, six subjects × 50)**, with new bands:

| Grade | Total |
|---|---|
| A | 241–300 |
| B | 181–240 |
| C | 121–180 |
| D | 61–120 |
| E | 0–60 |

**Per-subject grades did NOT change** — they still use the old 0–50 bands
(A=41-50, B=31-40, C=21-30, D=11-20, E=0-10). Only the *overall* grade moved
to the total scale.

Before this change, grading logic was duplicated ~28 times across the codebase
(`assignGrade()` in 12 Export classes, 3 controllers, a Job, and ~14 Blade
`@php` blocks) with 4 different boundary conventions and 3 different pass/fail
conventions. `finalStatus()` was duplicated 13 times. Grade bands came from
three competing sources: the `ranks` DB table, `config/ranks.php`, and
hardcoded arrays in 4 files.

**All of that has been centralized into one config file + one service.** The
old duplication is gone. If you are about to write `assignGrade(` or
`finalStatus(` anywhere, or query the `Ranks` model, or read
`config('ranks')` — stop, you're about to reintroduce the exact problem that
was just fixed. Use `Grading::` (see below).

The full plan this work followed is saved at (if the plan file still exists
on this machine):
`C:\Users\Desktop\.claude\plans\change-the-grade-ssytem-wondrous-gadget.md`
It has the complete phase-by-phase design rationale, the audit findings, and
the verification table. This CLAUDE.md is the condensed "what you need to
know to keep working" version.

## The new system — single source of truth

### `config/grading.php` (new file)

The **only** place grade letters, band boundaries, pass/fail rules, and grade
wording are defined. Two scales:

```php
'scales' => [
    'subject' => [...],  // 0..50, per-subject mark. UNCHANGED bands.
    'total'   => [...],  // 0..300, the Daraja. THE bands that changed.
],
'pass' => [
    'upper_class_from' => 5,  // classId >= 5 uses the 'upper' failing set
    'failing_grades' => ['upper' => ['D','E'], 'lower' => ['E']],
],
'absent' => [...],       // sentinel grade 'ABS', labels, status '-'
'descriptions' => [...], // A->Bora/Excellent, etc.
'labels' => [...],       // sw: FAULU/FELI, en: PASS/FAIL
```

`config/subjects.php` is unchanged and still owns subject-count-per-class
(always 6). `maxTotal($classId)` in the service derives `300` from
`6 subjects × 50`. If anyone ever changes `config/subjects.php` to a
different subject count per class, the total bands (`0..300`) will silently
stop matching reality — there's no automatic re-derivation of the *band
numbers themselves*, only of `maxTotal()`. This wasn't flagged as urgent
because the school system's subject count has been stable at 6, but it's a
sharp edge worth knowing about.

### `app/Services/GradingService.php` (new file)

The only class that turns a number into a grade letter. Full API is
documented in the class's own docblocks — read the file, it's ~350 lines and
every method is one-line-summarized. The two you'll use 95% of the time:

```php
Grading::gradeSubject($mark);   // 0..50 scale — a single subject's mark
Grading::gradeTotal($total);    // 0..300 scale — the Daraja
```

**There is deliberately no generic `grade($value, $scale)` method.** Do not
add one. The whole point of separate method names is that
`grep Grading::gradeTotal` gives you a complete, trustworthy list of every
site that grades on the total scale. A defaultable `$scale` parameter would
silently reintroduce the exact bug class this migration fixed (an average fed
where a total was expected, or vice versa).

Other methods you'll need:
- `Grading::statusForTotal($total, $classId, $locale = null)` — pass/fail
  label (`FAULU`/`FELI` or `PASS`/`FAIL` for `'en'`). Replaces every
  `finalStatus()`.
- `Grading::passingTotal($classId)` — the lowest total that still passes.
  Replaces the four inconsistent `$borderLine` computations that used to
  exist (`$rank[3]['rankRangeMin']` vs `$rank[2]` vs `$rank[4]['rankRangeMax']`
  — all different, all wrong in different ways).
- `Grading::absentGrade()` — the sentinel `'ABS'`. Never write the literal
  string `'ABS'`; call this instead, so if the sentinel ever changes there's
  one place to update.
- `Grading::description($grade, $locale)` — Swahili/English grade wording.
  Replaces `PrintController::getGradeDescription()` (deleted).
- `Grading::letters()`, `Grading::totalBands()`, `Grading::subjectBands()` —
  for A/B/C/D/E tally loops and legends. Never write a literal
  `['A','B','C','D','E']` array or a literal `41`/`241` boundary anywhere
  outside `config/grading.php`.
- `Grading::totalFromSubjectMean($meanSubjectMark, $classId)` — converts a
  school-wide mean subject mark (0-50) into the equivalent mean total
  (0-300). **Do not use this to derive an individual student's total from
  `marks.average`** — `average = total / (count of NON-NULL subjects)`, so
  for a student who missed a subject, `average × 6` overstates their real
  total. Always read `marks.total` directly for a single student. This
  conversion is only valid for aggregate/whole-school figures.

### `app/Facades/Grading.php` (new file)

A Laravel facade so `Grading::gradeTotal(...)` works bare in Blade views
(no `use` statement needed — same as `Session::`/`Auth::` elsewhere in this
codebase) and in Export classes (which are `new`-ed manually, so constructor
injection isn't available to them).

Registered as a singleton in `AppServiceProvider::register()` and aliased in
`config/app.php`'s `aliases` array. If you ever need the underlying class
directly (e.g. in a queued job or a test), use
`app(\App\Services\GradingService::class)` — it resolves to the same
singleton instance as the facade.

### `tests/Unit/GradingServiceTest.php` (new file)

48 assertions covering every band boundary (both scales), null/absence
handling, `0` is never absent, `passingTotal()` agrees with `totalPasses()`
at every boundary for every classId, the mis-scale guard (see below), and
wording. Loads the **real** `config/grading.php` via `require`, so it guards
the actual shipped bands, not a fixture copy. Run with:

```bash
php artisan test --filter=GradingServiceTest
```

It's a pure PHPUnit test (extends `PHPUnit\Framework\TestCase` directly, no
Laravel container) — same pattern as the pre-existing
`tests/Unit/ExampleTest.php`. It's safe to run without a database, which
matters here because `phpunit.xml` has the sqlite in-memory lines commented
out, so any container-booted DB test would hit the real configured DB.

## The mis-scale guard

`Grading::gradeSubject()` throws `InvalidArgumentException` (in
strict/debug mode) or logs a warning (in production) if handed a value above
50 — because a subject mark can never legitimately exceed 50 (validated at
input: `min:0|max:50`), so anything higher is almost certainly a 0-300 total
passed to the wrong method. There's no equivalent guard for
`gradeTotal()` receiving a 0-50 value by mistake — that's numerically
undetectable, but it fails *loudly and visibly* instead (every student grades
`E`), which nobody misses in practice. This asymmetry is intentional and
documented in the service's docblock.

## What changed, file by file (grouped by concern)

### Deleted (verified zero references before deletion)
- `app/Exports/OLD_MarksExport.php`, `OLDSubjectExport.php`,
  `OLDSchoolReportExpor.php`, `OLDStudentDataExport.php`,
  `OLDTeacherReportExport.php` — dead legacy export classes
- `resources/views/user/oldreports.blade.php`,
  `resources/views/admin/NewsubjectReport.blade.php`,
  `resources/views/admin/NEWstudentData.blade.php` — dead legacy views
  (verified via `route:list` that nothing renders them)
- `config/ranks.php` — the old band-table config, fully superseded by
  `config/grading.php`
- `App\Http\Controllers\admin\DashboardController::query()` and its route
  `GET /query` — this was an **unauthenticated** endpoint (no session guard,
  unlike every sibling method in that controller) that rewrote every row in
  `marks` using a hardcoded class-6 subject list (corrupting classes 1-5) and
  overwrote `NULL` averages with `'0.00'` (destroying the absence flag for
  every genuinely-absent student in the database). If you see code that
  wants this back, don't — it was a live data-corruption hazard, not a
  feature.

### Grade calculation — subject scale (behavior preserved)
Every `assignGrade($subjectMark)` call site was replaced with
`Grading::gradeSubject($subjectMark)`, and every local `function assignGrade`
definition was deleted. This touched: all remaining Export classes, all
report controllers, and ~14 Blade views. Behavior is byte-identical **except**
one intentional fix: `resources/views/user/subjectReport.blade.php` used to
use a strict `>` on the band minimum (so a mark of exactly 41/31/21/11 fell
through to grade E) — this is now correct.

### Grade calculation — total scale (the actual behavior change)
Every `assignGrade($average)` / `finalStatus($average, ...)` call site that
computed the *overall* Daraja was changed to feed `marks.total` (or a
properly-derived mean total for aggregates) into `Grading::gradeTotal()` /
`Grading::statusForTotal()`. This is the one visible behavior change users
will notice: **a student's Daraja is now based on their total, not their
average.**

One consequence worth remembering if a teacher asks "why did this student's
grade drop": a student who sat 3 of 6 subjects and scored 40 on each used to
average 40 → grade B. Their total is 120 → grade D. This is the intended
behavior per the approved design (grade the raw total, don't pro-rate for
partial attendance) — it is not a bug, but it will surprise people the first
time they see it. Flagged in the original plan's risk section as #6.

### SQL: the `averageMarks` alias was retired
The old code had a single alias name, `averageMarks`, that meant **three
different things** depending on which file you were in: a school's
`AVG(average)` (true mean, 0-50), a school's `SUM(total)` (a grand sum, not
a mean at all), or a bare per-student `average` column. This is now split
into unambiguous names:
- **`avgTotal`** — `AVG(CASE WHEN average IS NOT NULL THEN total END)`, a
  mean TOTAL (0-300) that excludes fully-absent students so they don't drag
  the school mean down. This is the number school-level Daraja is graded on.
- **`studentTotal`** — a bare per-student `total`, no aggregation.

If you find `$x['averageMarks']` anywhere, it's a bug this migration missed
— it will throw an undefined-key error rather than silently grading the
wrong scale (that fail-loud behavior is intentional; the rename was chosen
specifically so a missed site errors instead of misbehaving).

### The `/6` bug (fixed in 4 places)
`Exports/TeacherReportExport.php`, `Exports/SchoolReportExport.php`, and both
`detailedReport.blade.php` views used to compute a school mean total in PHP
(`SUM(total) / (headcount - absentees)`) and then divide by 6 again before
grading — a leftover from when grading was on the 0-50 average scale. This
division is now gone entirely; the mean total is computed once in SQL
(`avgTotal`) and graded directly.

### The "two Wastani boxes show different scales but one grade" bug (fixed in 5 places)
`user/reports.blade.php`, `admin/reports.blade.php`,
`admin/studentData.blade.php`, `pdf/report-all.blade.php`, and
`pdf/english/report-all.blade.php` each render two summary boxes — one
showing a mean *subject mark* (0-50) and one showing a mean *total* (0-300)
— but used to display the **same grade** next to both, computed from only
one of the two scales. Now both boxes show the grade derived from the mean
total, which is correct for both (the subject-mark box is informational
only, kept at 0-50 for backward-compat display, but the grade next to it now
matches the total-based box).

### Ranking/ordering switched to total
Every `orderBy('average', 'desc')` that feeds a ranked/positioned list was
changed to `orderByRaw('(average IS NULL), total DESC')`. Reasoning: ranking
by average and ranking by total only agree when every student sat the same
number of subjects — which stopped being guaranteed once subjects became
nullable (migration `2026_05_18_000000_make_mark_subjects_nullable.php`).
Since the displayed grade is now total-based, the rank order must also be
total-based, or you get tables where a lower-graded student sits above a
higher-graded one. The `(average IS NULL)` leading term keeps absentees
deterministically last (a true absentee and a genuine 0-scorer both have
`total = 0`, and without this they'd sort in arbitrary order).

### PDF paths stopped trusting client-supplied grades
Two security-relevant fixes in `app/Http/Controllers/user/PrintController.php`:
- `printReport()` / `printReportEnglish()` used to build the PDF entirely
  from client-supplied JSON (`selectedStudents` — a hidden form field the
  browser populates from on-screen data before POSTing). A user could edit
  that JSON before submitting and get a report showing any grade they want.
  Now, only the `markId` is trusted from the client; `PrintController`
  re-fetches the real `Marks` records and calls
  `rebuildStudentFromMark()` to recompute subjects, total, average, and
  grade server-side before rendering.
- Per-subject and overall positions are now computed server-side too
  (`calculateSubjectPositions()`), not trusted from the client.
- `resources/views/user/reports.blade.php`'s `$reportData` blob (posted to
  `/printAllReport` and `/report.school.english`) no longer includes the
  `ranks` key — the PDF views (`pdf/report-all.blade.php`,
  `pdf/english/report-all.blade.php`) now call `Grading::` directly instead
  of receiving a client-editable band table.

  **This is a partial fix, not a complete one.** `$reportData` as a whole
  (marks, counts, `schoolGrade`, etc.) is still built server-side then
  POSTed back by the browser essentially unvalidated (only `marks` presence
  is checked in `UserReportController::printAllReport`/`printAllReportEnglish`).
  A determined user could still edit other fields in that JSON before
  submitting. Fully closing this would mean rewriting those two controller
  methods to accept only filter criteria (classId/examId/dates) and
  recompute the entire summary server-side — that's a larger refactor that
  was explicitly deferred; the `ranks` removal was the highest-value,
  lowest-effort slice of it (a forged band table was the most severe
  exposure). If asked to harden this further, that's where to start.

### Write path / absence invariant
- `app/Imports/MarksImport.php` and `app/Http/Controllers/user/UploadController.php`
  (`saveUpload`, `updateUpload`): `number_format($total/$count, 2)` (which
  returns a **string**, not a number — the root cause of the `/6` string-cast
  bug elsewhere) replaced with `round(...)`.
- `app/Console/Commands/ProcessMarksGrades.php` was almost entirely
  commented out and would have written `average = 0` to every row if ever
  run (the accumulation loop was dead code). It's now a working backfill
  command (`php artisan app:process-marks-grades`) that recomputes
  `total`/`average` for every row in 5000-row chunks. **It has not been run
  in this session** (no live DB was available in the sandbox — see
  Verification below). If the database has rows written by the old
  `DashboardController::query()` method before it was deleted, those rows
  may have `average = '0.00'` where they should have `average = NULL`
  (absent). Running this command will repair that, but **snapshot the
  `marks` table first** — it's a bulk UPDATE across the whole table.

**The absence rule, stated once:** `average IS NULL` ⟺ the student sat zero
subjects. `total = 0` means "scored zero," not absent. Every grading site now
follows this — `Grading::gradeTotal()`/`gradeSubject()` return `'ABS'` only
for `null`/`''`/non-numeric input, never for `0`.

## Verification performed (and what wasn't possible)

**Done, and passing:**
- `php artisan test` — 50 tests, 107 assertions, all green.
- Every `.php` file under `app/` — `php -l` clean.
- All 45 Blade views under `resources/views/` — compiled via
  `Blade::compileString()` and syntax-checked with `php -l` on the compiled
  output. All clean.
- `php artisan route:list` — boots without error (confirms no controller
  references a deleted class/method, no fatal route registration errors).
- Manual grep sweeps confirming zero remaining live references to
  `assignGrade(`, `finalStatus(`, `Ranks::`, `config('ranks')`, or hardcoded
  grade-boundary literals (`41`, `31`, `21`, `11` as band boundaries) outside
  `config/grading.php` and `app/Services/GradingService.php`.

**NOT done — no live database was available in this sandbox**
(`SQLSTATE[HY000] [1049] Unknown database 'lms'`):
- No end-to-end HTTP request was made against a seeded database.
- The backfill command (`app:process-marks-grades`) has not been run.
- The manual verification table from the original plan (seed a class-4 and
  class-6 group with boundary students, absentees, ties, etc.; hit every
  report/export/PDF route; confirm numbers) has **not** been executed.

**Before this goes to production, someone with DB access should:**
1. Back up `marks`.
2. Run `php artisan migrate` if there are pending migrations (there
   shouldn't be any new ones from this work — no schema changes were made).
3. Optionally run `php artisan app:process-marks-grades` to repair any
   `average = '0.00'` rows left by the deleted `query()` method, watching
   that `SELECT COUNT(*) FROM marks WHERE average IS NULL` *increases*
   (never decreases) as a sanity check.
4. Run `php artisan view:clear` — several Blade views had their compiled
   cache holding old `@php function assignGrade() {...}` bodies; those must
   be invalidated on deploy.
5. Run `php artisan cache:clear` — a couple of controllers cache computed
   grade distributions (`admin\ReportController` under key prefix
   `grades_v2_...`, already bumped from `processeds_marks_...` specifically
   so old caches are never served — but `FilterReportJob`'s
   `filtered_report_...` key was **not** bumped, since that job's cached
   payload doesn't include a grade, only raw counts; double check this
   before relying on it if `FilterReportJob` output is ever graded
   downstream).
6. Manually spot-check a few real students against expected new grades
   (total ÷ nothing, no averaging) before telling end users the system has
   changed.

## If you're asked to change the bands again

Edit `config/grading.php` only. Change the `scales.total.bands` (or
`scales.subject.bands`) array, update `tests/Unit/GradingServiceTest.php`'s
data providers to match the new boundaries, run
`php artisan test --filter=GradingServiceTest`, done. Nothing else in the
codebase should need to change — that's the entire point of this migration.
If you find yourself needing to edit a second file to change a grade
boundary, something regressed back toward the old duplicated design; fix
that instead of adding the second edit.
