<?php

namespace Modules\QuizManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quizzes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'quiz_question',
        'total_marks',
        'passing_marks',
        'per_qus_time',
        'total_time_duration',
        'color',
        'status',
        'image',
        'workspace',
        'created_by',
    ];

    public static function getMinutesRange()
    {
        return array_combine(range(0, 60, 5), range(0, 60, 5));
    }

    public function category()
    {
        return $this->belongsTo(QuizCategories::class, 'category_id'); // Ensure 'category_id' matches your database schema
    }

    public function questions()
    {
        return $this->belongsToMany(QuizQuestions::class, 'quizzes_questions', 'quiz_id', 'question_id');
    }
}
