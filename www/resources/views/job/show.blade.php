@extends('layout')

@section('content')

    <div class="col-md-6 col-md-offset-3">
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

        <h2>Notes</h2>
        @if(empty($job->notes))
            <p>No notes</p>
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
        <h3>New Note</h3>
        <form method="POST" action="/job/{{ $job->id }}/note">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
                <textarea name="body" class="form-control">

                </textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
@stop