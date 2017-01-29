@extends('layouts.default')

@section('content')

    <h1>All jobs</h1>

    @if(!$jobs->isEmpty())
        There are jobs :)
    @else
        There are no jobs. ;(
    @endif

    <?php
        $counter = 0;
    ?>

    @if(!$jobs->isEmpty())
    <ul class="list-group">
    @endif
    @foreach($jobs as $job)
        <li>
            <ul class="list-group">
                <li class="list-group-item-success">
                    <a href="/job/{{ $job->id }}">{{ ++$counter }}</a>.
                </li>
                <li class="list-group-item" title="Last system check: {{ $job->file_datetime }}">
                    <a href="{{ $job->file_url }}" target="_blank">{{ $job->file_url }}</a>
                </li>
            </ul>
        </li>
    @endforeach
    @if(!$jobs->isEmpty())
    </ul>
    @endif
@stop