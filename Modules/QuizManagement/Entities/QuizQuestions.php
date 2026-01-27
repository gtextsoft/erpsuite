<?php

namespace Modules\QuizManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizQuestions extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'type',
        'mcq_values',
        'other_qus_values',
        'correct_answer',
        'marks',
        'workspace',
        'created_by',
    ];
    
    
    public function category()
{
    return $this->belongsTo(\Modules\QuizManagement\Entities\QuestionCategory::class, 'category_id');
}

}
