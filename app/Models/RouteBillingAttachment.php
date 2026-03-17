<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class RouteBillingAttachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'orcamento_id',
        'user_id',
        'file_path',
        'file_type',
        'notes',
        'is_valid',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getStatusLabelAttribute(): string
    {
        if (is_null($this->is_valid)) {
            return 'Pendente';
        }
        return $this->is_valid ? 'Válido' : 'Inválido';
    }
}
