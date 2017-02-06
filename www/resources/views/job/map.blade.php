@extends('layouts.default')

@section('content')

    @include('job.search.form.main', ['formAction' => '/job/search/map'])

    <hr>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12" id="googleMap" style="height: 500px"></div>
        </div>
    </div>

    <hr>

@stop

@section('javascript')

    <script>
        function initMap() {
            var startingPoint = {lat: 43.328674, lng: -79.817734};
            var map = new google.maps.Map(document.getElementById('googleMap'), {
                zoom: 4,
                center: startingPoint
            });
            var marker = new google.maps.Marker({
                position: startingPoint,
                map: map
            });
        }
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_JAVASCRIPT_API_KEY', 'GOOGLE_MAPS_JAVASCRIPT_API_KEY_is_not_set') }}&callback=initMap">
    </script>

@stop