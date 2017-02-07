@extends('layouts.default')

@section('content')
    <h1>Opportunity update<br><a href="{{ $job->file_url }}" target="_blank"><span class="glyphicon glyphicon-link"></span></a><small>{{ $job->file_url }}</small></h1>


    <form method="POST" action="/job/{{ $job->id }}">

        {{ method_field('PATCH') }}

        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>

    </form>

@stop