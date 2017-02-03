@extends('layouts.default')

@section('content')

    <h1>All jobs</h1>

    @include('job.search.form.main')

    @if(!$jobs->isEmpty())
        Jobs available: {{ $jobsInTotal }}
    @else
        There are no jobs. ;(
    @endif

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

            <a href="/job/{{ $job->id }}">{{ ++$counter }}. </a>{!! mb_substr($job->content_static_without_tags, 0, 150) !!}<a href="/job/{{ $job->id }}">... <span class="glyphicon glyphicon-menu-right"></span></a><br>
            <a href="{{ $job->file_url }}" target="_blank">{{ $job->file_url }}</a>
        </li>
    @endforeach
    @if(!$jobs->isEmpty())
    </ul>
    @endif

    {!! $jobs->links() !!}

@stop