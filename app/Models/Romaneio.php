<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Romaneio extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'descricao',
        'motorista',
        'veiculo',
        'data_entrega',
        'status',
        'observacoes',
        'user_id',
    ];

    protected $casts = [
        'data_entrega' => 'date',
    ];

    /**
     * Lotes de separação vinculados a este romaneio.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(PickingBatch::class);
    }

    /**
     * Usuário que criou o romaneio.
     */
    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
