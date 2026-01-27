<?php

namespace Modules\QuizManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizCategories extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'workspace',
        'created_by',
    ];
}
