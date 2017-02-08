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

            var map = new google.maps.Map(document.getElementById('googleMap'), {
                zoom: 2,
                center: {lat: 27, lng: 0}
            });
            
            // Create an array of alphabetical characters used to label the markers.
            var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            // Add some markers to the map.
            // Note: The code uses the JavaScript Array.prototype.map() method to
            // create an array of markers based on a given "locations" array.
            // The map() method here has nothing to do with the Google Maps API.
            var markers = locations.map(function(jobInfo, i) {
                var marker = new google.maps.Marker({
                    position: {lat: jobInfo.lat, lng: jobInfo.lng},
                    label: labels[i % labels.length],
                    title: jobInfo.mt
                });
                marker.addListener('click', function() {

                    var infowindow = new google.maps.InfoWindow({
                        content: jobInfo.iwc
                    });

                    infowindow.open(map, marker);
                });

                return marker;

            });

            // Add a marker clusterer to manage the markers.
            var markerCluster = new MarkerClusterer(map, markers,
                {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});
        }
        var locations = {!! $mapInfo !!};

    </script>
    <script
            src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_JAVASCRIPT_API_KEY', 'GOOGLE_MAPS_JAVASCRIPT_API_KEY_is_not_set') }}&callback=initMap">
    </script>

@stop