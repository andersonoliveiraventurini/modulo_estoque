<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'order_returns';

    protected $fillable = [
        'order_id',
        'customer_id',
        'status',
        'sales_supervisor_id',
        'sales_approved_at',
        'stock_supervisor_id',
        'stock_approved_at',
        'refusal_reason'
    ];

    protected $casts = [
        'sales_approved_at' => 'datetime',
        'stock_approved_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Pedido::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Cliente::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderReturnItem::class, 'order_return_id');
    }

    public function salesSupervisor()
    {
        return $this->belongsTo(User::class, 'sales_supervisor_id');
    }

    public function stockSupervisor()
    {
        return $this->belongsTo(User::class, 'stock_supervisor_id');
    }
}
