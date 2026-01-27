<?php

namespace Modules\QuizManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizzesQuestions extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_id',
        'created_at',
        'updated_at',
    ];
}
