<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlocokItem extends Model
{
    /** @use HasFactory<\Database\Factories\BlocokItemFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'bloco_k_items';
}
