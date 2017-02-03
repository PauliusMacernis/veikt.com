<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index(Request $request, $page=1, $perPage=100) {
        //$jobs = ['firstjob', 'secondjob', 'thirdjob', 'etc.'];

        $user = $request->user();

        $jobs = DB::table('job')->where('is_published', 1)->paginate($perPage);
        if(!empty($user) && isset($user->id)) {
            $notes = $this->getPrivateNoteCounts($jobs, $user->id);
        } else {
            $notes = null;
        }
        $jobsInTotal = $jobs->total();
        $counterInitValue = (($jobs->currentPage() - 1) * $jobs->perPage());
        //$jobs = Job::all()->where('is_published', 1)->forPage($page, $perPage);

        return view('job.index', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes'));
    }

    protected function getPrivateNoteCounts($jobs, $userId) {

        $notesResult = array();
        foreach($jobs as $job) {
            $notesResult[$job->id] = 0;
        }
        $notes = DB::table('notes')
            ->select('job_id', DB::raw('count(*) as total'))
            ->whereIn('job_id', array_keys($notesResult))
            ->whereIn('user_id', [$userId])
            ->groupBy('job_id')
            ->get();

        foreach($notes as $note) {
            $notesResult[$note->job_id] = $note->total;
        }

        return $notesResult;

    }

    public function show(Job $job) {

        $job->load('notes.user');

        return view('job.show', compact('job'));
    }

    public function find(Request $request, $page=1, $perPage=100) {
        //$jobs = ['firstjob', 'secondjob', 'thirdjob', 'etc.'];

        $user = $request->user();

        $content_static_without_tags = $request->input('content_static_without_tags', '');
        $jobs = DB::table('job')->where('is_published', 1)->where('content_static_without_tags', 'like', '%' . $content_static_without_tags . '%')->paginate($perPage);
        if(!empty($user) && isset($user->id)) {
            $notes = $this->getPrivateNoteCounts($jobs, $user->id);
        } else {
            $notes = null;
        }
        $jobsInTotal = $jobs->total();
        $counterInitValue = (($jobs->currentPage() - 1) * $jobs->perPage());
        //$jobs = Job::all()->where('is_published', 1)->forPage($page, $perPage);

        return view('job.index', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'content_static_without_tags'));
    }

}
