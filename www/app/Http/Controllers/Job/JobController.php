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
            $notes = $this->getPrivateNoteCounts($jobs, $user->id);
        } else {
            $notes = null;
        }
        $jobsInTotal = $jobs->total();
        $counterInitValue = (($jobs->currentPage() - 1) * $jobs->perPage());
        //$jobs = Job::all()->where('is_published', 1)->forPage($page, $perPage);

        $searchInput = null;
        $transformedJobInfo = $this->transformJobsInfo($jobs, $searchInput);

        return view('job.index', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'searchInput', 'transformedJobInfo'));
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

    public function show(Request $request, $job) {

        $user = $request->user();

        if($user && $user->exists()) {
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

        return view('job.show', compact('job'));
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
            $notes = $this->getPrivateNoteCounts($jobs, $user->id);
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

}
