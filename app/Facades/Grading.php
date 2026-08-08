<?php

namespace App\Facades;

use App\Services\GradingService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string gradeSubject($mark)
 * @method static string gradeTotal($total)
 * @method static bool isAbsent($value)
 * @method static bool gradePasses(?string $grade, $classId)
 * @method static bool totalPasses($total, $classId)
 * @method static string statusForTotal($total, $classId, ?string $locale = null)
 * @method static array failingGrades($classId)
 * @method static float passingTotal($classId)
 * @method static string description(?string $grade, ?string $locale = null)
 * @method static string descriptionForTotal($total, ?string $locale = null)
 * @method static string absentLabel(?string $locale = null)
 * @method static string absentGrade()
 * @method static array letters()
 * @method static int|null letterIndex(string $letter)
 * @method static array bands(string $scale)
 * @method static array subjectBands()
 * @method static array totalBands()
 * @method static array emptyTally(bool $includeAbsent = false)
 * @method static array tally(iterable $grades, bool $includeAbsent = false)
 * @method static int maxSubjectMark()
 * @method static int subjectCount($classId)
 * @method static array subjectsFor($classId)
 * @method static int maxTotal($classId)
 * @method static float totalFromSubjectMean(float $meanSubjectMark, $classId)
 * @method static array assertConsistent()
 *
 * @see GradingService
 */
class Grading extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'grading';
    }
}
