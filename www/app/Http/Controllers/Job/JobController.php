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
        $markedJobInfo = $this->transformJobsInfo($jobs, $searchInput);

        return view('job.index', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'searchInput', 'markedJobInfo', 'user'));
    }

    public function map(Request $request, $page=1, $perPage=100) {
        //$jobs = ['firstjob', 'secondjob', 'thirdjob', 'etc.'];

        $user = $request->user();

        /**
         * @var \Illuminate\Support\Collection $jobs
         */
        $jobs = DB::table('job')
            ->where('is_published', 1)
            //->orderBy('updated_at', 'desc')
            //->orderBy('datetime_imported', 'desc')
            //->orderBy('file_datetime', 'desc')
            //->orderBy('created_at', 'desc')
            //->paginate($perPage)
            ->get()
            ->keyBy('id')
        ;


        if(!empty($user) && isset($user->id)) {
            $notes = $this->getPrivateNoteInfo($jobs, $user->id);
        } else {
            $notes = null;
        }
        $jobsInTotal = $jobs->count();

        $markedJobInfo = $this->transformJobsInfo($jobs, ($searchInput = null));

        $jobs = $jobs->map(function($job) use ($notes, $markedJobInfo, $searchInput, $user) {
            $job->notes                 = $notes[$job->id];
            $job->mapInfoWindow         = $this->formatMapInfoWindow($user, $job, $notes, $markedJobInfo, '');
            $job->markedJobInfo    = $this->markJobInfo($searchInput, $job);
            return $job;
        });

        $counterInitValue = null;

        $mapInfo = json_encode(array_map(function($job) {
            return [
                'lat' => rand(-85, +85),        // Latitude
                'lng' => rand(-180, 180),       // Longitude
                'iwc' => $job->mapInfoWindow,   // HTML for marker's info window on Google Maps
                'mt'  => md5(uniqid())          // Marker title (text)
            ];
        }, $jobs->toArray(), []));

        return view('job.map', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'searchInput', 'markedJobInfo', 'mapInfo'));
    }

    protected function formatMapInfoWindow($user, $job, $notes, $markedJobInfo, $counter) {

        $infoWindow = '';

        if(isset($user) && ($user->isAdministrator())) {
            $infoWindow .= '<a href="/job/' . $job->id . '/edit"><span class="glyphicon glyphicon-edit"></span></a>';
        }


        if($notes[$job->id]['privateAllCount'] > 0) {
            $infoWindow .= '<span class="badge" title="Notes"><a class="badge" href="/job/' . $job->id . '">' . $notes[$job->id]['privateAllCount'] . '</a></span>';
        }

        $infoWindow .= '
            <a href="/job/' . $job->id . '" class="doNotUnderline">' . ++$counter . '...' . $markedJobInfo[$job->id] . '...</a><br>
            <a href="' . $job->file_url . '" target="_blank"><span class="glyphicon glyphicon-link"></span></a> <small>' . $job->file_url . '</small>
        ';

        if(!empty($notes[$job->id]['privateListableData'])) {
            $infoWindow .= '<br><br>';
            foreach($notes[$job->id]['privateListableData'] as $noteInfo) {
                $infoWindow .= '<div class="alert alert-warning" role="alert"><a class="glyphicon glyphicon-eye-open" title="Turn off when listing" href="/note/' . $noteInfo->id . '/turnOffListing"></a> ' . $noteInfo->created_at . '<br>' . $noteInfo->body . '</div>';
            }
        }

        return $infoWindow;

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

        $separator = ' ';
        $searchInput = $request->input('searchInput', '');
        $searchInputAsArray = explode($separator, $searchInput);

        $user = $request->user();

        $jobs = DB::table('job')
            ->where('is_published', 1)
            ->where(function($query) use($searchInputAsArray) {
                if(!$searchInputAsArray) {
                    return;
                }
                foreach($searchInputAsArray as $searchInputWord) {
                    $query->where('content_static_without_tags', 'like', '%' . $searchInputWord . '%');
                }
            })
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
        $markedJobInfo = $this->transformJobsInfo($jobs, $searchInput);

        return view('job.index', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'searchInput', 'markedJobInfo'));
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
        
        $markedJobInfo = $this->transformJobsInfo($jobs, $searchInput);

        return view('job.map', compact('jobs', 'jobsInTotal', 'counterInitValue', 'notes', 'searchInput', 'markedJobInfo'));
    }

    /**
     * @param LengthAwarePaginator $jobs
     * @param $searchInput
     */
    protected function transformJobsInfo($jobs, $searchInput = null) {

        $extraInfoOnJobs = array();

        foreach ($jobs as $job) {
            $extraInfoOnJobs[$job->id] = $this->markJobInfo($searchInput, $job);
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

    /**
     * @param $searchInput
     * @param $job
     * @param $markBegin
     * @param $markEnd
     * @param $rollbackLength
     * @param $stringLengthAllowed
     * @param $extraInfoOnJobs
     * @return mixed
     */
    protected function markJobInfo($searchInput, $job)
    {

        $markBegin  = '<mark>';
        $markEnd    = '</mark>';
        $stringLengthAllowed = 250;
        $rollbackLength = 20;


        if(!isset($searchInput)) {
            return mb_substr($job->content_static_without_tags, 0, $stringLengthAllowed);
        }

        $contentStaticWithoutTagsModified = '';
        $contentStaticWithoutTags = preg_replace(
            "/(\t|\n|\v|\f|\r| |\xC2\x85|\xc2\xa0|\xe1\xa0\x8e|\xe2\x80[\x80-\x8D]|\xe2\x80\xa8|\xe2\x80\xa9|\xe2\x80\xaF|\xe2\x81\x9f|\xe2\x81\xa0|\xe3\x80\x80|\xef\xbb\xbf)+/",
            " ",
            ($job->content_static_without_tags)
        );

        // Transform
        $contentStaticWithoutTags_Marked = preg_replace("/\p{L}*?" . preg_quote($searchInput) . "\p{L}*/ui", $markBegin . "$0" . $markEnd, $contentStaticWithoutTags);

        // Find first mark position
        $firstMarkPositionFound = mb_stripos($contentStaticWithoutTags_Marked, $markBegin);

        $cutFrom = $firstMarkPositionFound - $rollbackLength;
        $maxLengthAvailable = mb_strlen($contentStaticWithoutTags_Marked);
        if ($cutFrom < 0) {
            $cutFrom = 0;
        } elseif ($firstMarkPositionFound + $stringLengthAllowed > $maxLengthAvailable) {
            $cutFrom = $maxLengthAvailable - $stringLengthAllowed;
        }

        return mb_substr($contentStaticWithoutTags_Marked, $cutFrom, $stringLengthAllowed);

    }

}
