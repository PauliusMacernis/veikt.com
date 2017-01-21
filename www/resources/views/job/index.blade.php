@extends('layout')

@section('content')

    <h1>All jobs</h1>

    @if(empty($jobs))
        There are no jobs. ;(
    @else
        There are jobs :)
    @endif


    @foreach($jobs as $job)
        <li>{{ $job->file_url }}</li>
    @endforeach

@stop