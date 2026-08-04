{{--
    Shared season filter dropdown. Every season-filtered page uses this.

    Required: $seasons        newest-first, keyed by id
              $selectedSeason the Season model, or null for all seasons
              $filterRoute    route name to link back to
    Optional: $filterParams   extra route params to preserve
              $allowAll       offer "All Seasons" (default true) — pages that
                              can only show one season at a time pass false
--}}
@php
    $filterParams = $filterParams ?? [];
    $allowAll     = $allowAll ?? true;
@endphp
<div class="dropdown">
    <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
        {{ $selectedSeason?->season_year ?? 'All Seasons' }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
    @if ($allowAll)
        <li>
            <a @class(['dropdown-item', 'active' => !$selectedSeason])
                href="{{ route($filterRoute, $filterParams + ['filter-seasons' => '']) }}">All Seasons</a>
        </li>
    @endif
    @forelse ($seasons as $season)
        <li>
            <a @class(['dropdown-item', 'active' => $selectedSeason && $selectedSeason->id == $season->id])
                href="{{ route($filterRoute, $filterParams + ['filter-seasons' => $season->id]) }}">{{ $season->season_year }}</a>
        </li>
    @empty
        <li><span class="dropdown-item-text text-muted">No seasons</span></li>
    @endforelse
    </ul>
</div>
