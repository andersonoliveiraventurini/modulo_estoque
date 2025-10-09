<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrcamentoTransporte extends Model
{
    /** @use HasFactory<\Database\Factories\OrcamentoTransporteFactory> */
    use HasFactory, softDeletes;
}
