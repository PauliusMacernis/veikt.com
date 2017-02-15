@extends('layouts.default')

@section('content')

    <h1 class="medium">Opportunities available <span class="label label-default">{{ $jobsInTotal }}</span></h1>

    @include('job.search.form.main', ['formAction' => '/job/search'])

    <!--
    @if(!$jobs->isEmpty())
        Available: {{ $jobsInTotal }}
    @else
        There are no jobs. ;(
    @endif
    -->

    <hr>

    <?php
        $counter = $counterInitValue;
    ?>

    @if(!$jobs->isEmpty())
    <ul class="list-group">
    @endif
    @foreach($jobs as $job)

        <li class="list-group-item" title="Last data import: {{ $job->file_datetime }}">

        @if(isset($user) && ($user->isAdministrator()))
            <a href="/job/{{ $job->id }}/edit"><span class="glyphicon glyphicon-edit"></span></a>
        @endif


        @if($notes[$job->id]['privateAllCount'] > 0)
                <span class="badge" title="Notes"><a class="badge" href="/job/{{ $job->id }}">{{ $notes[$job->id]['privateAllCount'] }}</a></span>
            @endif

            <a href="/job/{{ $job->id }}" class="doNotUnderline">{{ ++$counter }}. ...{!! $markedJobInfo[$job->id] !!}...</a><br>
            <a href="{{ $job->file_url }}" target="_blank"><span class="glyphicon glyphicon-link"></span></a> <small>{{ $job->file_url }}</small>

            @if(!empty($notes[$job->id]['privateListableData']))
                <br><br>
                @foreach($notes[$job->id]['privateListableData'] as $noteInfo)
                    <div class="alert alert-warning" role="alert"><a class="glyphicon glyphicon-eye-open" title="Turn off when listing" href="/note/{{ $noteInfo->id }}/turnOffListing"></a> {{ $noteInfo->created_at }}<br>{{ $noteInfo->body }}</div>
                @endforeach
            @endif


        </li>

    @endforeach
    @if(!$jobs->isEmpty())
    </ul>
    @endif

    {!!
    $jobs->appends(
        [
            'searchInput' => $searchInput,
        ]
    )->links()
    !!}

@stop