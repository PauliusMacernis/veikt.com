@extends('layout')

@section('content')

    <h1>All jobs</h1>

    @if(empty($jobs))
        There are no jobs. ;(
    @else
        There are jobs :)
    @endif

    @if(!isset($counter))
        {{ $counter = 0 }}
    @endif
    @foreach($jobs as $job)
        <li>
            <ul>
                <li>
                    {{ ++$counter }}.
                    <a href="{{ $job->file_url }}" target="_blank">{{ $job->file_url }}</a>
                </li>
                <li>{{ $job->file_project }} | {{ $job->file_datetime }}</li>
                <li>{{ $job->content_static_without_tags }}</li>
            </ul>
        </li>
    @endforeach

@stop