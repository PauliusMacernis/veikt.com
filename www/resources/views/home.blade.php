@extends('layouts.default')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    Successfully logged in!

                    <h2>Search history</h2>
                    <p>To be done. Search history, actions.</p>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Term</th>
                            <th>Search again</th>
                            <th>Remove</th>
                            <th>Study</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th scope="row">1</th>
                            <td>PHP Viln</td>
                            <td><a href="#" onclick="alert('To be done.. Will search again - redirect to decent search, results page.'); return false;">Search again</a></td>
                            <td><a href="#" onclick="alert('To be done.. Go to Expression school'); return false;">Study</a></td>
                            <td><a href="#" onclick="alert('To be done.. Will remove term from this list (from expression_hits and expression_hits_history tables)'); return false;">Remove</a></td>
                        </tr>
                        <tr>
                            <th scope="row">1</th>
                            <td>PHP Kaun</td>
                            <td><a href="#" onclick="alert('To be done.. Will search again - redirect to decent search, results page.'); return false;">Search again</a></td>
                            <td><a href="#" onclick="alert('To be done.. Go to Expression school'); return false;">Study</a></td>
                            <td><a href="#" onclick="alert('To be done.. Will remove term from this list (from expression_hits and expression_hits_history tables)'); return false;">Remove</a></td>
                        </tr>
                        <tr>
                            <th scope="row">1</th>
                            <td>PHP Toronto</td>
                            <td><a href="#" onclick="alert('To be done.. Will search again - redirect to decent search, results page.'); return false;">Search again</a></td>
                            <td><a href="#" onclick="alert('To be done.. Go to Expression school'); return false;">Study</a></td>
                            <td><a href="#" onclick="alert('To be done.. Will remove term from this list (from expression_hits and expression_hits_history tables)'); return false;">Remove</a></td>
                        </tr>
                        </tbody>
                    </table>

                    <h2>Notes on Opportunities</h2>
                    <p>To be done. The same list as in opportunities index page, search.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
