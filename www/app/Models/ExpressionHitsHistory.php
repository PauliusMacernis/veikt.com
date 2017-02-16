<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpressionHitsHistory extends Model
{
    protected $table = 'expression_hits_history';

    protected $fillable = ['user_id', 'expression'];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
