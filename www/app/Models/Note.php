<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['body'];

    public function job() {
        $this->belongsTo(Job::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
