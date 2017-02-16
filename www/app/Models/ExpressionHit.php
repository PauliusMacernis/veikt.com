<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpressionHit extends Model
{
    protected $fillable = ['expression', 'hits', 'last_hit'];
}
