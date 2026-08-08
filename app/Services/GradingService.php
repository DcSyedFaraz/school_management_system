<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Centralised grading. The ONLY place in the application that turns a
 * number into a grade letter, a pass/fail verdict, or grade wording.
 *
 * TWO SCALES — READ THIS BEFORE CALLING ANYTHING:
 *
 *   SUBJECT scale (0..50)  — one subject mark. Use gradeSubject().
 *   TOTAL   scale (0..300) — the SUM of a student's subject marks.
 *                            This is the overall "Daraja". Use gradeTotal().
 *
 * There is deliberately NO generic grade($value, $scale) method. Picking the
 * scale is a decision the caller must make at the call site, in the method
 * name, where a reviewer can see it.
 *
 * The TOTAL scale is fed from marks.total (the DB column), NOT from
 * marks.average * subjectCount — those differ whenever a subject is null,
 * because average is total/(count of NON-NULL subjects).
 *
 * Absence: a value of null (or '') means the student sat nothing and yields
 * self::ABSENT. A student who sat 4 of 6 subjects is NOT absent; their raw
 * total is graded as-is and they simply score lower.
 *
 * This class performs NO database queries and has NO Eloquent dependency.
 * It is constructed from plain arrays so it can be unit-tested with no
 * container and no DB (see tests/Unit/GradingServiceTest.php).
 */
class GradingService
{
    /** Returned instead of a letter when the student has no marks at all. */
    public const ABSENT = 'ABS';

    public const SCALE_SUBJECT = 'subject';
    public const SCALE_TOTAL = 'total';

    private array $config;

    /** @var array<string,string[]> class key => subject column names */
    private array $subjectsByClass;

    /** @var array<string,array<string,array{min:int,max:int}>> memoised, sorted desc by min */
    private array $sortedBands = [];

    /**
     * @param  array  $config          Contents of config/grading.php
     * @param  array  $subjectsByClass Contents of config/subjects.php
     */
    public function __construct(array $config, array $subjectsByClass = [])
    {
        $this->config = $config;
        $this->subjectsByClass = $subjectsByClass;
    }

    // =========================================================================
    // Grading a value
    // =========================================================================

    /**
     * Grade ONE SUBJECT mark on the 0..50 scale.
     *
     * @param  int|float|string|null  $mark  A single subject mark. null/'' => ABSENT.
     * @return string A grade letter, or self::ABSENT.
     *
     * @throws InvalidArgumentException When strict mode is on and $mark exceeds
     *                                  the subject maximum (50) — that almost always means a 0..300
     *                                  TOTAL was passed to the wrong method.
     */
    public function gradeSubject($mark): string
    {
        if ($this->isAbsent($mark)) {
            return self::ABSENT;
        }

        $this->guardSubjectRange((float) $mark);

        return $this->gradeOnScale(self::SCALE_SUBJECT, (float) $mark);
    }

    /**
     * Grade a STUDENT TOTAL on the 0..300 scale. This is the overall "Daraja".
     *
     * Accepts fractional values so it can also grade an AGGREGATE mean total
     * (e.g. a school's mean total across students) — see totalFromSubjectMean().
     *
     * @param  int|float|string|null  $total  Sum of the student's subject marks.
     *                                        null/'' => ABSENT.
     * @return string A grade letter, or self::ABSENT.
     */
    public function gradeTotal($total): string
    {
        if ($this->isAbsent($total)) {
            return self::ABSENT;
        }

        return $this->gradeOnScale(self::SCALE_TOTAL, (float) $total);
    }

    /**
     * True when the value represents "no marks at all".
     *
     * @param  mixed  $value
     */
    public function isAbsent($value): bool
    {
        return $value === null || $value === '' || ! is_numeric($value);
    }

    // =========================================================================
    // Pass / fail
    // =========================================================================

    /**
     * Does this grade letter count as a pass for this class?
     *
     * classId >= config('grading.pass.upper_class_from') fails on D and E;
     * lower classes fail only on E. Driven entirely by letters, so there is
     * no numeric boundary and therefore no < vs <= ambiguity.
     *
     * @param  string|null  $grade  Letter, or self::ABSENT.
     * @param  int|string|null  $classId
     */
    public function gradePasses(?string $grade, $classId): bool
    {
        if ($grade === null || $grade === self::ABSENT) {
            return false;
        }

        return ! in_array($grade, $this->failingGrades($classId), true);
    }

    /**
     * Does this 0..300 TOTAL pass for this class?
     *
     * @param  int|float|string|null  $total
     * @param  int|string|null  $classId
     */
    public function totalPasses($total, $classId): bool
    {
        return $this->gradePasses($this->gradeTotal($total), $classId);
    }

    /**
     * Human pass/fail label for a 0..300 TOTAL.
     * Replaces every finalStatus() in the codebase.
     *
     * @param  int|float|string|null  $total
     * @param  int|string|null  $classId
     * @param  string|null  $locale  'sw' (FAULU/FELI) or 'en' (PASS/FAIL).
     * @return string Pass label, fail label, or the absent status ('-').
     */
    public function statusForTotal($total, $classId, ?string $locale = null): string
    {
        $locale = $this->locale($locale);

        if ($this->isAbsent($total)) {
            return $this->config['absent']['status'][$locale];
        }

        $key = $this->totalPasses($total, $classId) ? 'pass' : 'fail';

        return $this->config['labels'][$locale][$key];
    }

    /**
     * Letters that FAIL for this class.
     *
     * @param  int|string|null  $classId
     * @return string[]
     */
    public function failingGrades($classId): array
    {
        $band = ((int) $classId) >= (int) $this->config['pass']['upper_class_from']
            ? 'upper'
            : 'lower';

        return $this->config['pass']['failing_grades'][$band];
    }

    /**
     * The LOWEST 0..300 total that still passes for this class.
     *
     * This is the single replacement for the four inconsistent $borderLine
     * definitions. It is derived from the failing-letter list, so it moves
     * automatically if the bands or the pass rule change.
     *
     * Compare with >= . Values are inclusive: total >= passingTotal() passes.
     *
     * @param  int|string|null  $classId
     */
    public function passingTotal($classId): float
    {
        $failing = $this->failingGrades($classId);

        // Bands are best-first; the last non-failing letter is the lowest pass.
        $lowestPassing = null;
        foreach ($this->bands(self::SCALE_TOTAL) as $letter => $band) {
            if (! in_array($letter, $failing, true)) {
                $lowestPassing = $band;
            }
        }

        if ($lowestPassing === null) {
            // Every letter fails — nothing can pass.
            return INF;
        }

        return (float) $lowestPassing['min'];
    }

    // =========================================================================
    // Wording
    // =========================================================================

    /**
     * Swahili/English description for a grade letter.
     * Replaces PrintController::getGradeDescription().
     *
     * @param  string|null  $grade  Letter, self::ABSENT, or null.
     * @param  string|null  $locale
     */
    public function description(?string $grade, ?string $locale = null): string
    {
        $locale = $this->locale($locale);

        if ($grade === null || ! isset($this->config['descriptions'][$grade])) {
            return $this->config['absent']['description'][$locale];
        }

        return $this->config['descriptions'][$grade][$locale];
    }

    /** Description for a 0..300 total, in one call. */
    public function descriptionForTotal($total, ?string $locale = null): string
    {
        return $this->description($this->gradeTotal($total), $locale);
    }

    /** Display label for an absent student ("Hayupo" / "Absent"). */
    public function absentLabel(?string $locale = null): string
    {
        return $this->config['absent']['label'][$this->locale($locale)];
    }

    /** The sentinel string returned for absent students. */
    public function absentGrade(): string
    {
        return self::ABSENT;
    }

    // =========================================================================
    // Letters, bands, tallies — for views
    // =========================================================================

    /**
     * All grade letters, best first: ['A','B','C','D','E'].
     * Every A/B/C/D/E tally loop and legend in the views must iterate THIS,
     * never a literal array.
     *
     * @return string[]
     */
    public function letters(): array
    {
        return array_keys($this->config['scales'][self::SCALE_SUBJECT]['bands']);
    }

    /**
     * Zero-based position of a letter in letters(), or null.
     * Replaces the hard-coded $rank[3] / $maleRanks[2] indexing.
     */
    public function letterIndex(string $letter): ?int
    {
        $i = array_search($letter, $this->letters(), true);

        return $i === false ? null : (int) $i;
    }

    /**
     * Band table for a scale, best-first, for rendering legends.
     *
     * @param  string  $scale self::SCALE_SUBJECT or self::SCALE_TOTAL
     * @return array<string,array{min:int,max:int}>
     */
    public function bands(string $scale): array
    {
        if (isset($this->sortedBands[$scale])) {
            return $this->sortedBands[$scale];
        }

        if (! isset($this->config['scales'][$scale])) {
            throw new InvalidArgumentException("Unknown grading scale [{$scale}].");
        }

        $bands = $this->config['scales'][$scale]['bands'];
        uasort($bands, fn ($a, $b) => $b['min'] <=> $a['min']);

        return $this->sortedBands[$scale] = $bands;
    }

    /** @return array<string,array{min:int,max:int}> 0..50 bands, for subject legends. */
    public function subjectBands(): array
    {
        return $this->bands(self::SCALE_SUBJECT);
    }

    /** @return array<string,array{min:int,max:int}> 0..300 bands, for Daraja legends. */
    public function totalBands(): array
    {
        return $this->bands(self::SCALE_TOTAL);
    }

    /**
     * A counter array pre-seeded with every letter => 0.
     *
     * @param  bool  $includeAbsent Add an ABS bucket.
     * @return array<string,int>
     */
    public function emptyTally(bool $includeAbsent = false): array
    {
        $tally = array_fill_keys($this->letters(), 0);

        if ($includeAbsent) {
            $tally[self::ABSENT] = 0;
        }

        return $tally;
    }

    /**
     * Count a list of already-computed grade letters.
     *
     * @param  iterable<string|null>  $grades
     * @return array<string,int>
     */
    public function tally(iterable $grades, bool $includeAbsent = false): array
    {
        $tally = $this->emptyTally($includeAbsent);

        foreach ($grades as $grade) {
            if ($grade !== null && array_key_exists($grade, $tally)) {
                $tally[$grade]++;
            }
        }

        return $tally;
    }

    // =========================================================================
    // Scale metadata
    // =========================================================================

    /** Highest possible single subject mark (50). */
    public function maxSubjectMark(): int
    {
        return (int) $this->config['scales'][self::SCALE_SUBJECT]['max'];
    }

    /**
     * Number of subjects for a class, from config/subjects.php.
     *
     * @param  int|string|null  $classId
     */
    public function subjectCount($classId): int
    {
        return count($this->subjectsFor($classId));
    }

    /**
     * Subject column names for a class, from config/subjects.php.
     *
     * @param  int|string|null  $classId
     * @return string[]
     */
    public function subjectsFor($classId): array
    {
        $key = (string) $classId;

        return $this->subjectsByClass[$key]
            ?? $this->subjectsByClass['class_default']
            ?? [];
    }

    /**
     * Highest achievable TOTAL for a class (subjects x 50 = 300).
     * Use for "x / 300" report headings and progress bars.
     *
     * @param  int|string|null  $classId
     */
    public function maxTotal($classId): int
    {
        return $this->subjectCount($classId) * $this->maxSubjectMark();
    }

    /**
     * Convert a MEAN SUBJECT MARK (0..50) into the equivalent MEAN TOTAL
     * (0..300) so it can be fed to gradeTotal().
     *
     * Use ONLY for aggregate/whole-school figures that are genuinely a mean
     * subject mark, e.g. resources/views/user/reports.blade.php ($gATotal /
     * (subjects * students)).
     *
     * DO NOT use this to derive an individual student's total from
     * marks.average — average is total/(NON-NULL subject count), so for a
     * student who missed a subject this OVERSTATES the total. Read
     * marks.total instead.
     *
     * @param  int|string|null  $classId
     */
    public function totalFromSubjectMean(float $meanSubjectMark, $classId): float
    {
        return $meanSubjectMark * $this->subjectCount($classId);
    }

    /**
     * Validate the config: both scales declare the same letters in the same
     * order, every letter has a description, failing letters exist, and each
     * scale's bands tile [min..max] with no gaps or overlaps.
     *
     * Called by the unit test; safe to call from a health check.
     *
     * @return string[] Human-readable problems. Empty array == healthy.
     */
    public function assertConsistent(): array
    {
        $problems = [];
        $letters = $this->letters();

        foreach (array_keys($this->config['scales']) as $scale) {
            $scaleLetters = array_keys($this->bands($scale));

            if ($scaleLetters !== $letters) {
                $problems[] = "Scale [{$scale}] letters ".implode(',', $scaleLetters)
                    .' do not match '.implode(',', $letters);
            }

            $expectedTop = (int) $this->config['scales'][$scale]['max'];
            foreach ($this->bands($scale) as $letter => $band) {
                if ($band['max'] !== $expectedTop) {
                    $problems[] = "Scale [{$scale}] band [{$letter}] max {$band['max']}"
                        ." should be {$expectedTop} (gap/overlap in the band table).";
                }
                $expectedTop = $band['min'] - 1;
            }

            if ($expectedTop !== (int) $this->config['scales'][$scale]['min'] - 1) {
                $problems[] = "Scale [{$scale}] lowest band does not reach the scale minimum.";
            }
        }

        foreach ($letters as $letter) {
            if (! isset($this->config['descriptions'][$letter])) {
                $problems[] = "No description configured for grade [{$letter}].";
            }
        }

        foreach ($this->config['pass']['failing_grades'] as $band => $failing) {
            foreach ($failing as $letter) {
                if (! in_array($letter, $letters, true)) {
                    $problems[] = "pass.failing_grades.{$band} references unknown grade [{$letter}].";
                }
            }
        }

        return $problems;
    }

    // =========================================================================
    // Internals
    // =========================================================================

    /**
     * Descending-min scan. Never uses 'max', so fractional aggregate values
     * (180.6) cannot fall between two bands, and there is no < vs <=
     * boundary to get wrong.
     */
    private function gradeOnScale(string $scale, float $value): string
    {
        $bands = $this->bands($scale);

        foreach ($bands as $letter => $band) {
            if ($value >= $band['min']) {
                return $letter;
            }
        }

        // Below the lowest band minimum (only reachable with negative input).
        return (string) array_key_last($bands);
    }

    private function guardSubjectRange(float $mark): void
    {
        $max = $this->maxSubjectMark();

        if ($mark <= $max) {
            return;
        }

        $message = "GradingService::gradeSubject() received {$mark}, above the "
            ."subject maximum of {$max}. A 0..300 TOTAL was probably passed to "
            .'the 0..50 method — use gradeTotal() instead.';

        if ($this->strict()) {
            throw new InvalidArgumentException($message);
        }

        Log::warning('[GRADING] '.$message);
    }

    private function strict(): bool
    {
        $strict = $this->config['strict'] ?? null;

        return $strict === null
            ? (bool) config('app.debug', false)
            : (bool) $strict;
    }

    private function locale(?string $locale): string
    {
        $locale = $locale ?: $this->config['default_locale'];

        return isset($this->config['labels'][$locale]) ? $locale : 'sw';
    }
}
