<?php

namespace Modules\QuizManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizParticipants extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'mobile_no',
        'quiz_id',
        'start_time',
        'end_time',
        'status',
        'created_by',
        'workspace',
    ];

    public function quizzes()
    {
        return $this->belongsTo(Quizzes::class, 'quiz_id');
    }
}
