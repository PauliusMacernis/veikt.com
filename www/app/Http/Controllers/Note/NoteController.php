<?php

namespace App\Http\Controllers\Note;

use App\Models\Job;
use App\Models\Note;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NoteController extends Controller
{
    public function store(Request $request, Job $job)
    {
        $note = new Note;
        $note->body = $request->body;

        $job->notes()->save($note);

        return back();

    }
}
