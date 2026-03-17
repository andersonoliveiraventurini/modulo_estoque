<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteBillingApproval extends Model
{
    protected $fillable = [
        'orcamento_id',
        'user_id',
        'status',
        'comments',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }}
