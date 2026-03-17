<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturnItem extends Model
{
    use HasFactory;

    protected $table = 'order_return_items';

    protected $fillable = [
        'order_return_id',
        'product_id',
        'quantity_requested',
        'quantity_approved',
        'unit_price'
    ];

    public function return()
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id');
    }

    public function product()
    {
        return $this->belongsTo(Produto::class, 'product_id');
    }
}
