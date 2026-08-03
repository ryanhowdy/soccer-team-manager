{{--
    Shared season filter dropdown.

    Required: $seasons (keyed by id), $selectedSeason (season id or null), $filterRoute (route name)
    Optional: $filterParams (extra route params to preserve)
--}}
@php
    $filterParams  = $filterParams ?? [];
    $currentSeason = $selectedSeason ? ($seasons[$selectedSeason] ?? null) : null;
    $prevYear      = null;
@endphp
<div class="dropdown">
    <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
        <span class="d-none d-lg-inline-block">
            {{ $currentSeason ? $currentSeason->season_year : 'All Seasons' }}
        </span><span class="bi-filter ps-1"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item @if(!$currentSeason) active @endif"
                href="{{ route($filterRoute, $filterParams + ['filter-seasons' => '']) }}">All Seasons</a>
        </li>
    @foreach ($seasons as $season)
        @if ($prevYear !== $season->year)
        <li><h6 class="dropdown-header">{{ $season->year }}</h6></li>
        @endif
        <li>
            <a class="dropdown-item ps-4 @if($currentSeason && $currentSeason->id == $season->id) active @endif"
                href="{{ route($filterRoute, $filterParams + ['filter-seasons' => $season->id]) }}">{{ $season->season_year }}</a>
        </li>
        @php $prevYear = $season->year; @endphp
    @endforeach
    </ul>
</div>
