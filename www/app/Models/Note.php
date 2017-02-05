<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['body', 'is_visible_when_listing_jobs'];

    public function job() {
        $this->belongsTo(Job::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
