@extends('layouts.default')

@section('content')
    <h1>Edit the Note</h1>

    <form method="POST" action="/note/{{ $note->id }}">

        {{ method_field('PATCH') }}

        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
            <textarea name="body" class="form-control">{{ $note->body }}</textarea>
        </div>
        <div class="checkbox">
            <label><input type="checkbox" name="is_visible_when_listing_jobs" value="1" @if($note->is_visible_when_listing_jobs) checked @endif />Show it when I list Opportunities</label>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
        <div class="form-group">
            <a href="/note/{{ $note->id }}/delete">Delete forever</a>
        </div>
    </form>

@stop