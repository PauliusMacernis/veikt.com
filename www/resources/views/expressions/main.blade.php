@if(!$expressions)
    -
@else
    <hr>
    <h2>Most popular <span class="label label-warning">expressions @if(!empty($searchInput)) for {{ $searchInput }} @endif</span></h2>
    @foreach( $expressions as $expression )

        <a href="/job/search?searchInput=%22{{ $expression->expression }}%22">{{ $expression->expression }}</a> <span class="label label-warning">{{ $expression->total }}</span>

    @endforeach
@endif