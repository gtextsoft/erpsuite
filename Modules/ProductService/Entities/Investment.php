<?php

namespace Modules\ProductService\Entities;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'amount',
        'currency',
        'workspace',
        'duration_years',
        'interest_rate',
        'investment_date',
        'due_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'investment_date' => 'date',
        'due_date' => 'date',
    ];
}