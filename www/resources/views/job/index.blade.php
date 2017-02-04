@extends('layouts.default')

@section('content')

    <h1>Opportunities available <span class="label label-default">{{ $jobsInTotal }}</span></h1>

    @include('job.search.form.main')

    <!--
    @if(!$jobs->isEmpty())
        Available: {{ $jobsInTotal }}
    @else
        There are no jobs. ;(
    @endif
    -->

    <hr>

    <?php
        $counter = $counterInitValue;
    ?>

    @if(!$jobs->isEmpty())
    <ul class="list-group">
    @endif
    @foreach($jobs as $job)

        <li class="list-group-item" title="Last system check: {{ $job->file_datetime }}">

            @if($notes[$job->id] > 0)
                <span class="badge" title="Notes"><a class="badge" href="/job/{{ $job->id }}">{{ $notes[$job->id] }}</a></span>
            @endif

            <a href="/job/{{ $job->id }}"><span class="glyphicon glyphicon-globe"></span> {{ ++$counter }}. ...</a>{!! $transformedJobInfo[$job->id] !!}<a href="/job/{{ $job->id }}">...</a><br>
            <a href="{{ $job->file_url }}" target="_blank"><span class="glyphicon glyphicon-link"></span></a> <small>{{ $job->file_url }}</small>
        </li>
    @endforeach
    @if(!$jobs->isEmpty())
    </ul>
    @endif

    {!!
    $jobs->appends(
        [
            'searchInput' => $searchInput,
        ]
    )->links()
    !!}

@stop