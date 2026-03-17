<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDiscountHistory extends Model
{
    use HasFactory;

    protected $table = 'customer_discount_history';

    protected $fillable = [
        'customer_discount_id',
        'previous_value',
        'new_value',
        'changed_by',
        'reason'
    ];

    public function discount()
    {
        return $this->belongsTo(Desconto::class, 'customer_discount_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
