@extends('layouts.default')

@section('content')

    <div class="col-md-6 col-md-offset-3">

        <h1>Job</h1>

        @if(count($errors))
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        @endif

        <h3>Notes</h3>
        @if($job->notes->isEmpty())
            <p>No notes so far. You can add notes for any of posts. Many job posting websites we collect data from, but one place for you to save your career related experience. Convenient.</p>
        @else
            <ul class="list-group">
                @foreach($job->notes as $note)
                    <li class="list-group-item">
                        <a class="pull-right" href="/note/{{ $note->id }}/edit">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </a>

                        {{ $note->updated_at }}<br />
                        by <a href="#">{{ $note->user->username }}</a><br /><br />
                        {{ $note->body }}
                    </li>
                @endforeach
            </ul>
        @endif
        <hr />

        <h4 title="You can add notes for any of posts posted. Many job posting websites we collect data from, but one place for you to save your career related experience. Convenient.">New Note</h4>
        <form method="POST" action="/job/{{ $job->id }}/note">
            {{ csrf_field() }}
            <div class="form-group">
                <textarea name="body" class="form-control">{{ old('body') }}</textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>
        <hr />

        <h1>Job</h1>
        <ul class="list-group">
            <li class="list-group-item">
                {{ $job->file_project }}
            </li>
            <li class="list-group-item">
                <a href="{{ $job->file_url }}" target="_blank">{{ $job->file_url }}</a>
            </li>
            <li class="list-group-item-success">
                {!! nl2br($job->content_static_without_tags) !!}
            </li>
            <li class="list-group-item">
                {{ $job->content_dynamic_without_tags }}
            </li>
            <li class="list-group-item">
                Last system check: {{ $job->file_datetime }}
            </li>
        </ul>
        
    </div>
@stop