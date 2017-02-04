<form method="GET" action="/job/search">
    <div class="row">
        <div class="col-lg-6">
            <div class="input-group">
                <input type="word" name="searchInput" class="form-control" placeholder="Search for..." value="@if(isset($searchInput)){{ $searchInput }}@endif">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">Go!</button>
                </span>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->
</form>
@if(isset($searchInput) && !empty($searchInput))
    <span>Interested in: "<em>{{ $searchInput }}</em>"</span>
@endif