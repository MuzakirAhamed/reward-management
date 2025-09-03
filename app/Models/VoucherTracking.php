<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherTracking extends Model
{
    protected $fillable = [
        'user_id',
        'voucher_id',
        'status',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id', 'id')->where('status', 1)->select('id', 'name', 'description', 'code', 'currency', 'expiry_date');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->where('role', 'user')->select('id', 'name', 'email');
    }
}
