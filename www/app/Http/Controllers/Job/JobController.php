<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index() {
        //$jobs = ['firstjob', 'secondjob', 'thirdjob', 'etc.'];
        //$jobs = DB::table('job')->get();
        $jobs = Job::all()->where('is_published', 1);

        return view('job.index', compact('jobs'));
    }

    public function show(Job $job) {
        //$job = Job::find($id);
        return view('job.show', compact('job'));
    }
}
