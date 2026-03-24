<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubStock extends Model
{
    /** @use HasFactory<\Database\Factories\HubStockFactory> */
    use HasFactory;

    protected $table = 'hub_stocks';

    protected $fillable = [
        'produto_id',
        'quantidade',
        'quantidade_reservada',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'quantidade_reservada' => 'integer',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
