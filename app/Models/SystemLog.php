<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'message',
        'context',
        'extra',
        'remote_addr',
        'user_agent',
        'user_id',
        'url',
        'method',
        'exception_class',
        'file',
        'line',
        'stack_trace',
    ];

    protected $casts = [
        'context' => 'array',
        'extra' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
