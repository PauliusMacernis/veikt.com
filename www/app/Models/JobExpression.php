<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobExpression extends Model
{
    protected $fillable = ['job_id', 'expression_id', 'expressions_found'];

    public function job() {
        $this->belongsTo(Job::class);
    }

    public function expression() {
        return $this->belongsTo(ExpressionHit::class);
    }
}
