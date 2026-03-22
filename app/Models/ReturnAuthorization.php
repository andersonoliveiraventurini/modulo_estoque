<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnAuthorization extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'user_id',
        'role',
        'status',
        'observacoes',
    ];

    public function return()
    {
        return $this->belongsTo(ProductReturn::class, 'return_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
