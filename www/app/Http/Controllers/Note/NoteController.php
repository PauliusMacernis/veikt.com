<?php

namespace App\Http\Controllers\Note;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $request, Job $job)
    {
        $job->addNote(
            new Note($request->all())
        );

        return back();

    }
}
