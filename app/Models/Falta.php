<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Falta extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_falta', 'user_id', 'vendedor_id', 'nome_cliente',
        'cliente_id', 'valor_total', 'observacao',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($falta) {
            $ultimo = static::withTrashed()->max('id') ?? 0;
            $falta->numero_falta = 'FAL-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
        });
    }

    public function itens() { return $this->hasMany(FaltaItem::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function vendedor() { return $this->belongsTo(Vendedor::class); }
    public function cliente() { return $this->belongsTo(Cliente::class); }
}
