<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_name',
        'amount',
        'user_type',
        'type',
        'description',
        'category',
        'date',
        'payment_id',
        'workspace',
        'created_by',
        'PropertyName',
        'PropertyId',
        'TransactionId',
        'EstateName',
        'EstateId',
        'AmountPaid',
        'PaymentCurrency',
        'PropertyPrice',
        'AccountStatus',
        'AmountPending',
        'NumberOfProperty',
        'UserId',
        'EstateUserId',
        'PaymentMode',
        'PaymentType'
    ];

   
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'TransactionId', 'invoice_id');
    }
}

