<?php

namespace Modules\Hrm\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'employee_id',
        'title',
        'description',
        'first_approver_id',
        'first_approver_status',
        'first_approver_remark',
        'gcoo_id',
        'gcoo_status',
        'gcoo_remark',
        'accountant_id',
        'accountant_status',
        'accountant_remark',
        'status',
        'rejection_reason', 
    ];

    /**
     * Get the employee who submitted the requisition.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the first approver for the requisition.
     */
    public function firstApprover()
    {
        return $this->belongsTo(User::class, 'first_approver_id');
    }

    /**
     * Get the GCOO approver for the requisition.
     */
    public function gcoo()
    {
        return $this->belongsTo(User::class, 'gcoo_id');
    }

    /**
     * Get the accountant approver for the requisition.
     */
    public function accountant()
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }
}