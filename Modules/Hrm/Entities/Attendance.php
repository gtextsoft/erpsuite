<?php

namespace Modules\Hrm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'late',
        'early_leaving',
        'overtime',
        'total_rest',
        'workspace',
        'created_by',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_in_address',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Hrm\Database\factories\AttendanceFactory::new();
    }

    public function employees()
    {
        return $this->hasOne('App\Models\User', 'id', 'employee_id');
    }
}
