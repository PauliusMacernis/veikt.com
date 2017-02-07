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
        $user = $request->user();
        if($user && $user->exists()) {
            $userId = $user->id;
        } else {
            $userId = null;
        }

        $this->validate($request, [
            'body' => 'required'
        ]);

        $note = new Note($request->all());

        $job->addNote($note, $userId);

        return back();

    }

    public function edit(Request $request, Note $note)
    {
        $user = $request->user();
        $noteUser = $note->user()->first();

        if(!$user || !$user->exists() || $user->id != $noteUser->id) {
            abort(404);
        }

        return view('note.edit', compact('note'));

    }

    public function turnOffListing(Request $request, Note $note)
    {
        $user = $request->user();
        $noteUser = $note->user()->first();

        if(!$user || !$user->exists() || $user->id != $noteUser->id) {
            abort(404);
        } else {
            $note->update(['is_visible_when_listing_jobs' => 0]);
        }

        return back();

    }

    public function turnOnListing(Request $request, Note $note)
    {
        $user = $request->user();
        $noteUser = $note->user()->first();

        if(!$user || !$user->exists() || $user->id != $noteUser->id) {
            abort(404);
        } else {
            $note->update(['is_visible_when_listing_jobs' => 1]);
        }

        return back();

    }

    public function update(Request $request, Note $note, User $user)
    {
        $data = $request->all();

        // Checkbox unchecked
        if(!isset($data['is_visible_when_listing_jobs'])) {
            $data['is_visible_when_listing_jobs'] = 0;
        }

        $note->update($data);

        return redirect('/job/' . $note->job_id . '#note-' . $note->id);

    }

    public function delete(Request $request, Note $note, User $user)
    {
        $jobId = $note->job_id;

        $user = $request->user();
        $noteUser = $note->user()->first();

        if(!$user || !$user->exists() || $user->id != $noteUser->id) {
            abort(404);
        } else {
            $note->delete();
        }

        return redirect('/job/' . $jobId);
        //return view('job.edit', compact('note'));

    }
}
