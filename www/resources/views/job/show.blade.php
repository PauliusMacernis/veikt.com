@extends('layouts.default')

@section('content')

    <div class="col-md-6 col-md-offset-3">

        @if(count($errors))
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        @endif


        <h3>Personal Notes</h3>
        @if($isUserLoggedIn)
            @include('note.form.create')
        @else
            <p>Available for registered users only.</p>
        @endif

        <hr />

        @if($job->notes->isEmpty())
            <p>We collect opportunities from many sources, you - create your private notes on top. Tracking own career-related experience is much easier.</p>
        @else
            <ul class="list-group">
                @foreach($job->notes as $note)
                    <li id="note-{{ $note->id }}" class="list-group-item @if($note->is_visible_when_listing_jobs) alert alert-warning @endif">
                        <a class="pull-right" href="/note/{{ $note->id }}/edit">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </a>

                        @if($note->is_visible_when_listing_jobs)
                            <a class="glyphicon glyphicon-eye-open " title="Turn off when listing" href="/note/{{ $note->id }}/turnOffListing"></a>
                        @else
                            <a class="glyphicon glyphicon-eye-close" title="Turn on when listing" href="/note/{{ $note->id }}/turnOnListing"></a>
                        @endif
                        {{ $note->updated_at }}<br />
                        <!--by <a href="#">{{ $note->user->name }}</a><br /><br />-->
                        {{ $note->body }}
                    </li>
                @endforeach
            </ul>
        @endif
        <hr />

        <h1>Opportunity</h1>
        <ul class="list-group">
            <li class="list-group-item">
                {{ $job->file_project }}
            </li>
            <li class="list-group-item">
                <img src="https://maps.googleapis.com/maps/api/staticmap?center={!! $job->latitude !!},{!! $job->longitude !!}&zoom=12&size=500x300&maptype=roadmap&markers=color:red%7+label:C%7C{!! $job->latitude !!},{!! $job->longitude !!}&key={{ env('GOOGLE_MAPS_JAVASCRIPT_API_KEY', 'GOOGLE_MAPS_JAVASCRIPT_API_KEY_is_not_set') }}"
            </li>
            <li class="list-group-item">
                <a href="{{ $job->file_url }}" target="_blank">{{ $job->file_url }}</a>
            </li>
            <li class="list-group-item list-group-item-success">
                {!! nl2br( mb_substr($job->content_static_without_tags, 0, 1000)) !!}
                <br><a href="{{ $job->file_url }}" target="_blank">...</a>
            </li>
            <li class="list-group-item">
                {{ $job->content_dynamic_without_tags }}
            </li>
            <li class="list-group-item">
                {{ $job->created_at }} - first data import<br>
                {{ $job->file_datetime }} - last remote data check<br>
                {{ $job->datetime_imported }} - last data import<br>
                {{ $job->updated_at }} - last update<br>
            </li>
        </ul>

        @if(isset($user) && ($user->isAdministrator()))
            <a href="/job/{{ $job->id }}/edit"><span class="glyphicon glyphicon-edit"></span> Edit the Opportunity</a>
        @endif


    </div>
@stop