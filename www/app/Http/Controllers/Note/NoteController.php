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

    public function edit(Request $request, Note $note)
    {

        return view('note.edit', compact('note'));

    }

    public function update(Request $request, Note $note)
    {

        $note->update($request->all());

        return back();

    }
}
