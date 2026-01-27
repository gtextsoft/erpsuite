<?php

namespace Modules\QuizManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizResults extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'quiz_id',
        'score',
        'start_time',
        'end_time',
        'status',
        'total_time_teken',
        'attempt_date',
        'created_by',
        'workspace',
    ];

    public function quizzes()
    {
        return $this->belongsTo(Quizzes::class, 'quiz_id');
    }

    public function participants()
    {
        return $this->belongsTo(QuizParticipants::class, 'participant_id');
    }
}
