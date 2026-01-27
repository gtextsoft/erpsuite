<?php

namespace Modules\QuizManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    protected $fillable = ['name'];

    public function questions()
    {
        return $this->hasMany(QuizQuestions::class, 'category_id');
    }
}
