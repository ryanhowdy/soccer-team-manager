@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Team Statistics')
@section('page-desc', "Learn all about a team")

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white position-relative p-4 mb-3">
            <div class="d-flex pb-3 border-bottom">
                <div>
                    <div class="circle-icon rounded-circle text-bg-primary text-center me-2">
                        <span class="bi-trophy-fill"></span>
                    </div>
                </div>
                <div>
                    <div class="fs-5 mb-1">
                        {{ $selectedCompetition->name }}
                    @if($selectedCompetition->place)
                        <span @class([
                            'position-absolute top-0 end-0 fs-3 m-3 badge',
                            'text-bg-success text-white' => ($selectedCompetition->place == 1),
                            'text-bg-info' => ($selectedCompetition->place == 2),
                            'text-bg-dark' => ($selectedCompetition->place >= 3),
                            ])>{{ $selectedCompetition->place_ordinal }}</span>
                    @endif
                    </div>
                @if($selectedCompetition->total_levels)
                    <div class="progress bg-light mb-1" style="max-width: 200px;" title="{{ $selectedCompetition->level }} out of {{ $selectedCompetition->total_levels }}">
                        <div @class([
                            'progress-bar progress-bar-striped',
                            'text-white bg-success' => $selectedCompetition->level_percentage >= 99,
                            'text-bg-info' => ($selectedCompetition->level_percentage >= 80 && $selectedCompetition->level_percentage < 99),
                            'text-bg-dark' => ($selectedCompetition->level_percentage >= 60 && $selectedCompetition->level_percentage < 80),
                            'text-bg-warning' => ($selectedCompetition->level_percentage >= 40 && $selectedCompetition->level_percentage < 60),
                            'bg-danger' => $selectedCompetition->level_percentage < 40,
                            ]) role="progressbar" style="width:{{ $selectedCompetition->level_percentage }}%">
                            {{ $selectedCompetition->level }}
                        </div>
                    </div>
                @endif
                </div>
            </div>
            <div class="pt-3">
                {{ $selectedCompetition->division }} - 
                <a class="link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                    href="{{ $selectedCompetition->website }}">Website</a>
            </div>
            <div class="text-secondary fst-italic small">
                {{ $selectedCompetition->started_at->format('F, jS') }} - {{ $selectedCompetition->ended_at->format('F, jS Y') }}
            </div>
        </div>

        @include('stats.charts')

    </div><!--/container-->
@endsection
