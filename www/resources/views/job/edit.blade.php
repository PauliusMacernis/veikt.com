@extends('layouts.default')

@section('content')
    <h1>Opportunity update<br><a href="{{ $job->file_url }}" target="_blank"><span class="glyphicon glyphicon-link"></span></a><small>{{ $job->file_url }}</small></h1>


    <form method="POST" action="/job/{{ $job->id }}">

        {{ method_field('PATCH') }}

        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="container">

            <div class="row">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>

            <div class="row">
                <div class="col-6 col-md-6">
                    <div id="googleMap" style="height: 300px"></div>
                </div>
                <div class="col-6 col-md-6">
                    <div class="form-group">
                        <label for="latitude">Latitude:</label>
                        <input type="text" class="form-control" name="latitude" value="{{ $job->latitude }}" id="latitude">
                    </div>

                    <div class="form-group">
                        <label for="longitude">Longitude:</label>
                        <input type="text" class="form-control" name="longitude" value="{{ $job->longitude }}" id="longitude">
                    </div>
                </div>
            </div>



        </div>







    </form>

@stop

@section('javascript')

    <script>
        function initMap() {

            var myLatlng = new google.maps.LatLng({!! (float)$job->latitude !!}, {!! (float)$job->longitude !!});
            var mapOptions = {
                zoom: 4,
                center: myLatlng
            }
            var map = new google.maps.Map(document.getElementById("googleMap"), mapOptions);

            // Place a draggable marker on the map
            var marker = new google.maps.Marker({
                position: myLatlng,
                map: map,
                draggable:true,
                title:"Drag me!"
            });

            google.maps.event.addListener(marker, 'drag', function(event){
                document.getElementById("latitude").value = event.latLng.lat();
                document.getElementById("longitude").value = event.latLng.lng();
            });

            /*

            var uluru = {lat: {{ $job->latitude }}, lng: {{ $job->longitude }}};
            var map = new google.maps.Map(document.getElementById('googleMap'), {
                zoom: 4,
                center: uluru
            });
            var marker = new google.maps.Marker({
                position: uluru,
                map: map
            });

            google.maps.event.addListener(marker, 'drag', function(event){
                //document.getElementById("latitude").value = event.latLng.lat();
                //document.getElementById("longitude").value = event.latLng.lng();
            });
            */
//            google.maps.event.addListener(marker, 'click', function (event) {
//                document.getElementById("latitude").value = event.latLng.lat();
//                document.getElementById("longitude").value = event.latLng.lng();
//            });

        }



        function initMap2() {

            var map = new google.maps.Map(document.getElementById('googleMap'), {
                zoom: 2,
                center: {lat: {{ $job->latitude }}, lng: {{ $job->longitude }}}
            });

            // Create an array of alphabetical characters used to label the markers.
            //var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            // Add some markers to the map.
            // Note: The code uses the JavaScript Array.prototype.map() method to
            // create an array of markers based on a given "locations" array.
            // The map() method here has nothing to do with the Google Maps API.
            var marker = new google.maps.Marker({
                position: {lat: jobInfo.lat, lng: jobInfo.lng},
                //label: labels[i % labels.length],
                //title: jobInfo.mt
            });


            //google.maps.event.addListener(marker, 'click', function (event) {
            //    document.getElementById("latitude").value = this.getPosition().lat();
            //    document.getElementById("longitude").value = this.getPosition().lng();
            //});

        }
        var jobInfo = [{lat: {{ $job->latitude }}, lng: {{ $job->longitude }}, iwc: 'fff', mt: 'cdcdc'}];



    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_JAVASCRIPT_API_KEY', 'GOOGLE_MAPS_JAVASCRIPT_API_KEY_is_not_set') }}&callback=initMap">
    </script>

@stop