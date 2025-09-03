<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'name',
        'description',
        'expiry_date',
        'currency',
        'code',
        'status',
    ];
}
