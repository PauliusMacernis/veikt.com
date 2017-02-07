<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index(Request $request, $page=1, $perPage=100) {
        //$jobs = ['firstjob', 'secondjob', 'thirdjob', 'etc.'];

        $user = $request->user();

        $jobs = DB::table('job')
            ->where('is_published', 1)
            ->orderBy('updated_at', 'desc')
            ->orderBy('datetime_imported', 'desc')
            ->orderBy('file_datetime', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        if(!empty($user) && isset($user->id)) {
            $notes = $this->getPrivateNoteInfo($jobs, $user->id);
        } else {
            $notes = null;
        }
        $jobsInTotal = $jobs->total();
        $counterInitValue = (($jobs->currentPage() - 1) * $jobs->perPage());
        //$jobs = Job::all()->where('is_published', 1)->forPage($page, $perPage);

        $searchInput = null;
        $transformedJobInfo = $this->transformJobsInfo($jobs, $searchInput);

        return view('job.index', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'searchInput', 'transformedJobInfo', 'user'));
    }

    public function map(Request $request, $page=1, $perPage=100) {
        //$jobs = ['firstjob', 'secondjob', 'thirdjob', 'etc.'];

        $user = $request->user();

        $jobs = DB::table('job')
            ->where('is_published', 1)
            //->orderBy('updated_at', 'desc')
            //->orderBy('datetime_imported', 'desc')
            //->orderBy('file_datetime', 'desc')
            //->orderBy('created_at', 'desc')
            //->paginate($perPage)
            ->get()
        ;

        if(!empty($user) && isset($user->id)) {
            $notes = $this->getPrivateNoteInfo($jobs, $user->id);
        } else {
            $notes = null;
        }
        $jobsInTotal = count($jobs);
        //$counterInitValue = (($jobs->currentPage() - 1) * $jobs->perPage());
        $counterInitValue = null;
        //$jobs = Job::all()->where('is_published', 1)->forPage($page, $perPage);

        $searchInput = null;
        $transformedJobInfo = $this->transformJobsInfo($jobs, $searchInput);

        return view('job.map', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'searchInput', 'transformedJobInfo'));
    }

    protected function getPrivateNoteInfo($jobs, $userId) {

        $notesResult = array();
        foreach($jobs as $job) {
            $notesResult[$job->id] = array('privateAllCount' => 0, 'privateListableData' => array());
        }

        // Count
        $privateAllCount = DB::table('notes')
            ->select('job_id', DB::raw('count(*) as total'))
            ->whereIn('job_id', array_keys($notesResult))
            ->whereIn('user_id', [$userId])
            ->groupBy('job_id')
            ->get();

        foreach($privateAllCount as $note) {
            $notesResult[$note->job_id]['privateAllCount'] = $note->total;
        }

        // Data
        // @todo: Merge Data with Count; and finally (maybe) merge to jobs as well..
        $privateNotesListable = DB::table('notes')
            ->select('job_id', 'id', 'body', 'created_at', 'updated_at')
            ->where('is_visible_when_listing_jobs', 1)
            ->whereIn('job_id', array_keys($notesResult))
            ->whereIn('user_id', [$userId])
            ->orderBy('created_at', 'DESC')
            ->get();

        foreach($privateNotesListable as $note) {
            $notesResult[$note->job_id]['privateListableData'][$note->id] = $note;
        }

        return $notesResult;

    }

    public function show(Request $request, $job) {

        $user = $request->user();
        $isUserLoggedIn = $user && $user->exists();

        if($isUserLoggedIn) {
            $job = Job::with(['notes' => function($query) use ($user) {
                $query->where('user_id', '=', $user->id);
            }])->find($job);
        } else {
            $job = Job::with(['notes' => function($query) use ($user) {
                $query->where('user_id', '=', NULL);
            }])->find($job);
        }

        // @todo: Admin:
        // $job->load('notes.user');
        // ?

        return view('job.show', compact('job', 'isUserLoggedIn', 'user'));
    }

    public function find(Request $request, $page=1, $perPage=100) {
        //$jobs = ['firstjob', 'secondjob', 'thirdjob', 'etc.'];

        $user = $request->user();

        $searchInput = $request->input('searchInput', '');
        $jobs = DB::table('job')
            ->where('is_published', 1)
            ->where('content_static_without_tags', 'like', '%' . $searchInput . '%')
            ->orderBy('updated_at', 'desc')
            ->orderBy('datetime_imported', 'desc')
            ->orderBy('file_datetime', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        if(!empty($user) && isset($user->id)) {
            $notes = $this->getPrivateNoteInfo($jobs, $user->id);
        } else {
            $notes = null;
        }
        $jobsInTotal = $jobs->total();
        $counterInitValue = (($jobs->currentPage() - 1) * $jobs->perPage());
        //$jobs = Job::all()->where('is_published', 1)->forPage($page, $perPage);

        //dd($jobs);
        $transformedJobInfo = $this->transformJobsInfo($jobs, $searchInput);

        return view('job.index', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'searchInput', 'transformedJobInfo'));
    }

    public function findOnMap(Request $request, $page=1, $perPage=100) {
        //$jobs = ['firstjob', 'secondjob', 'thirdjob', 'etc.'];

        $user = $request->user();

        $searchInput = $request->input('searchInput', '');
        $jobs = DB::table('job')
            ->where('is_published', 1)
            ->where('content_static_without_tags', 'like', '%' . $searchInput . '%')
            //->orderBy('updated_at', 'desc')
            //->orderBy('datetime_imported', 'desc')
            //->orderBy('file_datetime', 'desc')
            //->orderBy('created_at', 'desc')
            //->paginate($perPage)
            ->get()
        ;
        if(!empty($user) && isset($user->id)) {
            $notes = $this->getPrivateNoteInfo($jobs, $user->id);
        } else {
            $notes = null;
        }
        $jobsInTotal = count($jobs);
        $counterInitValue = null;
        //$jobs = Job::all()->where('is_published', 1)->forPage($page, $perPage);
        
        $transformedJobInfo = $this->transformJobsInfo($jobs, $searchInput);

        return view('job.map', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'searchInput', 'transformedJobInfo'));
    }

    /**
     * @param LengthAwarePaginator $jobs
     * @param $searchInput
     */
    protected function transformJobsInfo($jobs, $searchInput = null) {

        $extraInfoOnJobs = array();

        $markBegin  = '<mark>';
        $markEnd    = '</mark>';
        $stringLengthAllowed = 250;
        $rollbackLength = 20;

        foreach ($jobs as $job) {

            if(!isset($searchInput)) {
                $extraInfoOnJobs[$job->id] = mb_substr($job->content_static_without_tags, 0, $stringLengthAllowed);
                continue;
            }

            $contentStaticWithoutTagsModified = '';
            $contentStaticWithoutTags = preg_replace(
                "/(\t|\n|\v|\f|\r| |\xC2\x85|\xc2\xa0|\xe1\xa0\x8e|\xe2\x80[\x80-\x8D]|\xe2\x80\xa8|\xe2\x80\xa9|\xe2\x80\xaF|\xe2\x81\x9f|\xe2\x81\xa0|\xe3\x80\x80|\xef\xbb\xbf)+/",
                " ",
                ($job->content_static_without_tags)
            );

            // Transform
            $contentStaticWithoutTags_Marked = preg_replace("/\p{L}*?".preg_quote($searchInput)."\p{L}*/ui", $markBegin . "$0" . $markEnd, $contentStaticWithoutTags);

            // Find first mark position
            $firstMarkPositionFound = mb_stripos($contentStaticWithoutTags_Marked, $markBegin);

            $cutFrom = $firstMarkPositionFound - $rollbackLength;
            $maxLengthAvailable = mb_strlen($contentStaticWithoutTags_Marked);
            if($cutFrom < 0) {
                $cutFrom = 0;
            } elseif($firstMarkPositionFound + $stringLengthAllowed > $maxLengthAvailable) {
                $cutFrom = $maxLengthAvailable - $stringLengthAllowed;
            }
            $extraInfoOnJobs[$job->id] = mb_substr($contentStaticWithoutTags_Marked, $cutFrom, $stringLengthAllowed);
        }

        return $extraInfoOnJobs;

    }


    public function edit(Request $request, Job $job)
    {
        /**
         * @var User
         */
        $user = $request->user();
        //$job->

        if(!$user || !$user->isAdministrator()) {
            abort(404);
        }

        return view('job.edit', compact('job'));

    }

    public function update(Request $request, Job $job, User $user)
    {
        $data = $request->all();

        // Checkbox unchecked
        //if(!isset($data['is_visible_when_listing_jobs'])) {
        //    $data['is_visible_when_listing_jobs'] = 0;
        //}

        $job->update($data);

        return redirect('/job/' . $job->id);

    }

}
