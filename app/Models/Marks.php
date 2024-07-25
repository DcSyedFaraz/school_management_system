<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marks extends Model
{
    use HasFactory;
    protected $table = "marks";
    protected $primaryKey = "markId";
    public $timestamps = false;

    public function school()
    {
        return $this->belongsTo(Schools::class, 'schoolId', 'schoolId');
    }
    public function class()
    {
        return $this->belongsTo(Grades::class, 'classId', 'gradeId');
    }
    public function exam()
    {
        return $this->belongsTo(Exams::class, 'examId', 'examId');
    }
}
