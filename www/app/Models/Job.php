<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'job';

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
