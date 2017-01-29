<?php

namespace App\Http\Controllers\Note;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Note;
use App\User;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $request, Job $job)
    {

        $this->validate($request, [
            'body' => 'required'
        ]);

        $note = new Note($request->all());

        $job->addNote($note, 1);

        return back();

    }

    public function edit(Request $request, Note $note)
    {

        return view('note.edit', compact('note'));

    }

    public function update(Request $request, Note $note, User $user)
    {
        $note->update($request->all());

        return back();

    }
}
