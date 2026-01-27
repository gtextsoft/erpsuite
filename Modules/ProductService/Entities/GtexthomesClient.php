<?php

namespace Modules\ProductService\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GtexthomesClient extends Model
{
    use HasFactory;

    protected $table = 'gtexthomes_clients';

    protected $fillable = [
        'serial_number',
        'name',
        'house',
        'amount_agreed',
        'amount_paid',
        'land_rec',
        'email',
        'phone_number',
        'workspace',
        'currency',
    ];

    protected static function newFactory()
    {
        return \Modules\ProductService\Database\Factories\GtexthomesClientFactory::new();
    }
}
