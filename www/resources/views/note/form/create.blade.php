<form method="POST" action="/job/{{ $job->id }}/note">
    {{ csrf_field() }}
    <div class="form-group">
        <textarea name="body" class="form-control">{{ old('body') }}</textarea>
    </div>
    <div class="checkbox">
        <label><input type="checkbox" name="is_visible_when_listing_jobs" value="1" @if(old('is_visible_when_listing_jobs')) checked @endif />Show it when I list Opportunities</label>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Add the Note</button>
    </div>
</form>