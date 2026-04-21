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
                        <span class="bi-geo-alt-fill"></span>
                    </div>
                </div>
                <div>
                    <div class="fs-5 mb-1">
                        {{ $selectedLocation->name }}
                    </div>
                @if($selectedLocation->address)
                    <div class="text-secondary small">
                        {{ $selectedLocation->address }}
                    </div>
                @endif
                </div>
            </div>
            <div class="pt-3">
                {{ count($results) }} {{ count($results) === 1 ? 'game' : 'games' }} played
            @if(count($results))
                @php
                    $dates = $results->pluck('date')->sort()->values();
                @endphp
                <span class="text-secondary fst-italic small ms-2">
                    {{ $dates->first()->format('F jS, Y') }}
                    &ndash;
                    {{ $dates->last()->format('F jS, Y') }}
                </span>
            @endif
            </div>
        </div>

        @include('stats.charts')

    </div><!--/container-->
@endsection
