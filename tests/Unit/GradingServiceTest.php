<?php

namespace Tests\Unit;

use App\Services\GradingService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit test — no Laravel container, no database.
 *
 * GradingService is constructed directly from the real config arrays, so this
 * suite guards the actual shipped band tables, not a fixture copy of them.
 */
class GradingServiceTest extends TestCase
{
    private GradingService $grading;

    protected function setUp(): void
    {
        parent::setUp();

        $config = require __DIR__.'/../../config/grading.php';
        $subjects = require __DIR__.'/../../config/subjects.php';

        // Pin strict mode so the test does not depend on APP_DEBUG / the
        // config() helper, which is unavailable without the container.
        $config['strict'] = true;

        $this->grading = new GradingService($config, $subjects);
    }

    // ---------------------------------------------------------------------
    // Config health
    // ---------------------------------------------------------------------

    public function test_config_is_internally_consistent(): void
    {
        $this->assertSame([], $this->grading->assertConsistent());
    }

    public function test_letters_come_from_config_in_best_first_order(): void
    {
        $this->assertSame(['A', 'B', 'C', 'D', 'E'], $this->grading->letters());
        $this->assertSame(0, $this->grading->letterIndex('A'));
        $this->assertSame(4, $this->grading->letterIndex('E'));
        $this->assertNull($this->grading->letterIndex('Z'));
    }

    // ---------------------------------------------------------------------
    // SUBJECT scale, 0..50 (unchanged legacy bands)
    // ---------------------------------------------------------------------

    /** @dataProvider subjectMarks */
    public function test_grades_a_subject_mark_on_the_0_50_scale($mark, string $expected): void
    {
        $this->assertSame($expected, $this->grading->gradeSubject($mark));
    }

    public static function subjectMarks(): array
    {
        return [
            'top of A' => [50, 'A'],
            'bottom of A' => [41, 'A'],
            'top of B' => [40, 'B'],
            'bottom of B' => [31, 'B'],
            'top of C' => [30, 'C'],
            'bottom of C' => [21, 'C'],
            'top of D' => [20, 'D'],
            'bottom of D' => [11, 'D'],
            'top of E' => [10, 'E'],
            'zero is E not ABS' => [0, 'E'],
            'fractional 20.5' => [20.5, 'D'],
            'fractional 10.9' => [10.9, 'E'],
            'numeric string' => ['35', 'B'],
        ];
    }

    // ---------------------------------------------------------------------
    // TOTAL scale, 0..300 (the new Daraja)
    // ---------------------------------------------------------------------

    /** @dataProvider totals */
    public function test_grades_a_total_on_the_0_300_scale($total, string $expected): void
    {
        $this->assertSame($expected, $this->grading->gradeTotal($total));
    }

    public static function totals(): array
    {
        return [
            'perfect 300' => [300, 'A'],
            'bottom of A' => [241, 'A'],
            'top of B' => [240, 'B'],
            'bottom of B' => [181, 'B'],
            'top of C' => [180, 'C'],
            'bottom of C' => [121, 'C'],
            'top of D' => [120, 'D'],
            'bottom of D' => [61, 'D'],
            'top of E' => [60, 'E'],
            'zero is E' => [0, 'E'],
            // Aggregate means are fractional and must not fall into a gap.
            'mean 180.6 is C' => [180.6, 'C'],
            'mean 241.01 is A' => [241.01, 'A'],
            'mean 60.5 is E' => [60.5, 'E'],
        ];
    }

    public function test_a_student_who_sat_fewer_subjects_is_graded_on_the_raw_total(): void
    {
        // Four subjects at 45 = 180. Not scaled up, not pro-rated: grade C.
        $this->assertSame('C', $this->grading->gradeTotal(180));
    }

    // ---------------------------------------------------------------------
    // Absence
    // ---------------------------------------------------------------------

    /** @dataProvider absentValues */
    public function test_only_a_missing_value_is_absent($value): void
    {
        $this->assertTrue($this->grading->isAbsent($value));
        $this->assertSame(GradingService::ABSENT, $this->grading->gradeTotal($value));
        $this->assertSame(GradingService::ABSENT, $this->grading->gradeSubject($value));
    }

    public static function absentValues(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'non numeric' => ['n/a'],
        ];
    }

    public function test_zero_is_a_real_mark_and_never_absent(): void
    {
        $this->assertFalse($this->grading->isAbsent(0));
        $this->assertFalse($this->grading->isAbsent('0'));
        $this->assertSame('E', $this->grading->gradeTotal(0));
    }

    public function test_absent_never_passes(): void
    {
        $this->assertFalse($this->grading->totalPasses(null, 5));
        $this->assertFalse($this->grading->totalPasses(null, 1));
        $this->assertFalse($this->grading->gradePasses(GradingService::ABSENT, 1));
    }

    // ---------------------------------------------------------------------
    // Pass / fail
    // ---------------------------------------------------------------------

    public function test_upper_classes_fail_on_d_and_e(): void
    {
        $classId = 5;

        $this->assertSame(['D', 'E'], $this->grading->failingGrades($classId));
        $this->assertTrue($this->grading->totalPasses(121, $classId));   // exactly C
        $this->assertFalse($this->grading->totalPasses(120, $classId));  // exactly D
        $this->assertTrue($this->grading->totalPasses(300, $classId));
        $this->assertFalse($this->grading->totalPasses(0, $classId));
    }

    public function test_lower_classes_fail_only_on_e(): void
    {
        $classId = 4;

        $this->assertSame(['E'], $this->grading->failingGrades($classId));
        $this->assertTrue($this->grading->totalPasses(61, $classId));    // exactly D
        $this->assertFalse($this->grading->totalPasses(60, $classId));   // exactly E
        $this->assertTrue($this->grading->totalPasses(120, $classId));
    }

    public function test_passing_total_replaces_the_four_borderline_definitions(): void
    {
        $this->assertSame(121.0, $this->grading->passingTotal(5));
        $this->assertSame(121.0, $this->grading->passingTotal(6));
        $this->assertSame(61.0, $this->grading->passingTotal(4));
        $this->assertSame(61.0, $this->grading->passingTotal(1));
    }

    public function test_passing_total_agrees_with_total_passes_at_every_boundary(): void
    {
        foreach ([1, 2, 3, 4, 5, 6] as $classId) {
            $border = $this->grading->passingTotal($classId);

            $this->assertTrue(
                $this->grading->totalPasses($border, $classId),
                "Exactly on the border must PASS for class {$classId}."
            );
            $this->assertFalse(
                $this->grading->totalPasses($border - 0.01, $classId),
                "Just under the border must FAIL for class {$classId}."
            );
        }
    }

    public function test_status_labels(): void
    {
        $this->assertSame('FAULU', $this->grading->statusForTotal(250, 5));
        $this->assertSame('FELI', $this->grading->statusForTotal(100, 5));
        $this->assertSame('PASS', $this->grading->statusForTotal(250, 5, 'en'));
        $this->assertSame('FAIL', $this->grading->statusForTotal(100, 5, 'en'));
        $this->assertSame('-', $this->grading->statusForTotal(null, 5));
        $this->assertSame('-', $this->grading->statusForTotal(null, 5, 'en'));
    }

    // ---------------------------------------------------------------------
    // Wording
    // ---------------------------------------------------------------------

    public function test_descriptions_match_the_legacy_print_controller_wording(): void
    {
        $this->assertSame('Bora', $this->grading->description('A'));
        $this->assertSame('Nzuri sana', $this->grading->description('B'));
        $this->assertSame('Nzuri', $this->grading->description('C'));
        $this->assertSame('Inaridhisha', $this->grading->description('D'));
        $this->assertSame('Dhaifu', $this->grading->description('E'));
        $this->assertSame('Hajafanya', $this->grading->description(null));
        $this->assertSame('Hajafanya', $this->grading->description(GradingService::ABSENT));
        $this->assertSame('Hajafanya', $this->grading->description('Z'));
    }

    public function test_description_for_total_uses_the_300_scale(): void
    {
        $this->assertSame('Bora', $this->grading->descriptionForTotal(280));
        $this->assertSame('Hajafanya', $this->grading->descriptionForTotal(null));
    }

    // ---------------------------------------------------------------------
    // Scale metadata
    // ---------------------------------------------------------------------

    public function test_max_total_is_derived_from_the_subjects_config(): void
    {
        $this->assertSame(6, $this->grading->subjectCount(3));
        $this->assertSame(50, $this->grading->maxSubjectMark());
        $this->assertSame(300, $this->grading->maxTotal(3));
        $this->assertSame(300, $this->grading->maxTotal(null)); // class_default
        $this->assertSame(300, $this->grading->maxTotal(99));   // unknown -> default
    }

    public function test_total_from_subject_mean_converts_a_school_average(): void
    {
        // A school mean subject mark of 30.0 == a mean total of 180 == grade C.
        $meanTotal = $this->grading->totalFromSubjectMean(30.0, 5);

        $this->assertSame(180.0, $meanTotal);
        $this->assertSame('C', $this->grading->gradeTotal($meanTotal));
    }

    public function test_band_tables_are_exposed_best_first_for_legends(): void
    {
        $this->assertSame(['A', 'B', 'C', 'D', 'E'], array_keys($this->grading->totalBands()));
        $this->assertSame(['min' => 241, 'max' => 300], $this->grading->totalBands()['A']);
        $this->assertSame(['min' => 41, 'max' => 50], $this->grading->subjectBands()['A']);
    }

    // ---------------------------------------------------------------------
    // Tally helpers (replaces the A/B/C/D/E counter arrays in ~15 views)
    // ---------------------------------------------------------------------

    public function test_empty_tally_seeds_every_letter(): void
    {
        $this->assertSame(
            ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0],
            $this->grading->emptyTally()
        );

        $this->assertArrayHasKey(
            GradingService::ABSENT,
            $this->grading->emptyTally(true)
        );
    }

    public function test_tally_counts_letters_and_ignores_absent_by_default(): void
    {
        $grades = ['A', 'A', 'C', GradingService::ABSENT, 'E', null];

        $this->assertSame(
            ['A' => 2, 'B' => 0, 'C' => 1, 'D' => 0, 'E' => 1],
            $this->grading->tally($grades)
        );

        $this->assertSame(
            1,
            $this->grading->tally($grades, true)[GradingService::ABSENT]
        );
    }

    // ---------------------------------------------------------------------
    // The mis-scale guard
    // ---------------------------------------------------------------------

    public function test_passing_a_total_to_grade_subject_throws_in_strict_mode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/gradeTotal/');

        $this->grading->gradeSubject(250); // a 0..300 total, wrong method
    }

    public function test_the_subject_maximum_itself_does_not_trip_the_guard(): void
    {
        $this->assertSame('A', $this->grading->gradeSubject(50));
    }
}
