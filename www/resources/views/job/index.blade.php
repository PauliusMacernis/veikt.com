@extends('layout')

@section('content')

    <h1>All jobs</h1>

    @if(!empty($jobs))
        There are jobs :)
    @else
        There are no jobs. ;(
    @endif

    <?php
        $counter = 0;
    ?>

    @if(!empty($jobs))
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
    @if(!empty($jobs))
    </ul>
    @endif
@stop