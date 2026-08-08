<?php

namespace Database\Seeders;

use App\Models\Exams;
use App\Models\Grades;
use App\Models\Marks;
use App\Models\Regions;
use App\Models\Districts;
use App\Models\Wards;
use App\Models\Schools;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds classes (grades 1..6, matching config/subjects.php), two exams, and
 * fake marks for every class at every school created by
 * LocationsAndUsersSeeder — enough data to exercise dashboards, reports,
 * exports and PDF report cards end-to-end.
 *
 * Deliberately generates a mix of: fully-scored students, partially-absent
 * students (some subjects null), and fully-absent students (average IS
 * NULL) — the three cases the grading system (see CLAUDE.md) must handle
 * correctly.
 *
 * None of the models here declare $fillable, so writes use the same
 * "new Model; $model['field'] = value; $model->save();" pattern as the rest
 * of the codebase (create()/firstOrCreate() would throw
 * MassAssignmentException — base Eloquent default is $guarded = ['*']).
 *
 * Safe to re-run: classes/exams are looked up by name first; marks are
 * cleared for the seeded schools+exams before regenerating, so re-running
 * does not keep piling up duplicate students.
 */
class MarksSeeder extends Seeder
{
    /** @var string[] classId (1..6) => gradeName */
    private array $classNames = [
        1 => 'Darasa la Kwanza',
        2 => 'Darasa la Pili',
        3 => 'Darasa la Tatu',
        4 => 'Darasa la Nne',
        5 => 'Darasa la Tano',
        6 => 'Darasa la Sita',
    ];

    private array $firstNames = [
        'Juma', 'Amina', 'Rashidi', 'Fatuma', 'Baraka', 'Halima', 'Emmanuel', 'Zainabu',
        'Hassan', 'Mwajuma', 'Daudi', 'Salma', 'Peter', 'Neema', 'Ibrahim', 'Rehema',
        'John', 'Sarah', 'Musa', 'Grace', 'Yusuph', 'Joyce', 'Ali', 'Mariam',
    ];

    private array $lastNames = [
        'Mrisho', 'Kileo', 'Mnyika', 'Chao', 'Mwakalinga', 'Kessy', 'Mushi', 'Lyimo',
        'Mtui', 'Kimaro', 'Massawe', 'Nyerere', 'Chuwa', 'Shayo', 'Malya', 'Kilewo',
    ];

    private int $studentsPerClass = 15;

    public function run(): void
    {
        $region = Regions::where('regionName', 'Dodoma')->first();
        $district = Districts::where('districtName', 'Dodoma Mjini')->first();
        $ward = Wards::where('wardName', 'Kati')->first();
        $schoolOne = Schools::where('schoolName', 'Shule ya Msingi Mfano A')->first();
        $schoolTwo = Schools::where('schoolName', 'Shule ya Msingi Mfano B')->first();
        $teacherOne = User::where('email', 'user@gmail.com')->first();
        $teacherTwo = User::where('email', 'user2@gmail.com')->first();

        if (!$region || !$district || !$ward || !$schoolOne || !$schoolTwo || !$teacherOne || !$teacherTwo) {
            $this->command?->error(
                'Locations/users not found — run LocationsAndUsersSeeder first '
                .'(php artisan db:seed --class=Database\\Seeders\\LocationsAndUsersSeeder).'
            );

            return;
        }

        $grades = $this->seedGrades();
        $exams = $this->seedExams();

        $schools = [
            ['school' => $schoolOne, 'teacher' => $teacherOne],
            ['school' => $schoolTwo, 'teacher' => $teacherTwo],
        ];

        // Clear previously-seeded marks for these schools+exams so re-running
        // this seeder regenerates fresh students instead of accumulating.
        Marks::whereIn('schoolId', [$schoolOne->schoolId, $schoolTwo->schoolId])
            ->whereIn('examId', array_map(fn ($e) => (string) $e->examId, $exams))
            ->delete();

        $today = date('Y-m-d');
        $examDates = [
            $exams[0]->examId => date('Y-m-d', strtotime('-30 days')),
            $exams[1]->examId => $today,
        ];

        $created = 0;

        foreach ($exams as $exam) {
            foreach ($schools as $entry) {
                $school = $entry['school'];
                $teacher = $entry['teacher'];

                foreach ($grades as $classId => $grade) {
                    $subjects = config('subjects.'.$classId, config('subjects.class_default'));

                    for ($i = 0; $i < $this->studentsPerClass; $i++) {
                        $this->createStudentMark(
                            $classId,
                            $subjects,
                            $exam,
                            $examDates[$exam->examId],
                            $region,
                            $district,
                            $ward,
                            $school,
                            $teacher,
                            $i
                        );
                        $created++;
                    }
                }
            }
        }

        $this->command?->info("MarksSeeder: created {$created} student mark rows "
            .'across '.count($grades).' classes, '.count($exams).' exams, 2 schools.');
    }

    /**
     * @return array<int,Grades> classId => Grades model
     */
    private function seedGrades(): array
    {
        $grades = [];

        foreach ($this->classNames as $classId => $name) {
            $grade = Grades::where('gradeName', $name)->first();
            if (!$grade) {
                $grade = new Grades;
                $grade['gradeName'] = $name;
                $grade['isActive'] = '1';
                $grade['isDeleted'] = '0';
                $grade->save();
            }
            $grades[$classId] = $grade;
        }

        return $grades;
    }

    /**
     * @return Exams[] exactly two exams
     */
    private function seedExams(): array
    {
        $names = [
            ['name' => 'Mtihani wa Robo Muhula', 'type' => 'Weekly'],
            ['name' => 'Mtihani wa Mwisho wa Muhula', 'type' => 'Terminal'],
        ];

        $exams = [];

        foreach ($names as $def) {
            $exam = Exams::where('examName', $def['name'])->first();
            if (!$exam) {
                $exam = new Exams;
                $exam['examName'] = $def['name'];
                $exam['examType'] = $def['type'];
                $exam['isActive'] = '1';
                $exam['isDeleted'] = '0';
                $exam->save();
            }
            $exams[] = $exam;
        }

        return $exams;
    }

    private function createStudentMark(
        int $classId,
        array $subjects,
        Exams $exam,
        string $examDate,
        Regions $region,
        Districts $district,
        Wards $ward,
        Schools $school,
        User $teacher,
        int $index
    ): void {
        $mark = new Marks;
        $mark['examDate'] = $examDate;
        $mark['classId'] = $classId;
        $mark['studentName'] = $this->firstNames[array_rand($this->firstNames)]
            .' '.$this->lastNames[array_rand($this->lastNames)];
        $mark['gender'] = $index % 2 === 0 ? 'M' : 'F';

        // Roughly 1 in 15 students is fully absent (no subjects sat at all).
        $fullyAbsent = random_int(1, 15) === 1;

        $total = 0;
        $subjectCount = 0;

        foreach ($subjects as $subject) {
            if ($fullyAbsent) {
                $value = null;
            } else {
                // ~10% chance any single subject is unmarked (partial absence).
                $value = random_int(1, 10) === 1 ? null : random_int(0, 50);
            }

            $mark[$subject] = $value;

            if ($value !== null) {
                $total += $value;
                $subjectCount++;
            }
        }

        $mark['total'] = $total;
        $mark['average'] = $subjectCount > 0 ? round($total / $subjectCount, 2) : null;
        $mark['examId'] = (string) $exam->examId;
        $mark['userId'] = $teacher->userId;
        $mark['regionId'] = $region->regionId;
        $mark['districtId'] = $district->districtId;
        $mark['wardId'] = $ward->wardId;
        $mark['schoolId'] = $school->schoolId;
        $mark['isActive'] = '1';
        $mark['isDeleted'] = '0';
        $mark->save();
    }
}
