@extends('layouts.main')

@section('page-title', 'Home')
@section('page-desc', "Welcome to your team's home page")

@section('content')
    <div class="container main-content">

    @if (count($scheduled))
        <div class="row">
            <div class="col-8">

                <div class="rounded rounded-3 bg-white p-4 mb-1">
                    <div class="mb-4">
                        <div class="position-relative d-inline-block me-3" style="width:3rem; height:3rem;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center w-100 h-100 bg-success text-white">
                                <i class="bi bi-info-square-fill"></i>
                            </div>
                        </div>
                        <h4 class="d-inline-block align-middle">Today</h4>
                    </div>
        @foreach ($scheduled as $sched)
                    <div class="rounded rounded-3 border p-5 mb-4">
                        <div class="row border-bottom pb-3 text-center">
                            <div class="col-2"></div>
                            <div class="col-3">
                                <img class="logo img-fluid" src="{{ asset($sched->homeTeam->club->logo) }}"/>
                                <div class="pt-2 pb-1">{{ $sched->homeTeam->name }}</div>
                                <span class="text-body-tertiary small">Home</span>
                            </div>
                            <div class="col-2">
                                <div class="pt-5">{{ $sched->date->inUserTimezone()->format('g:i a') }}</div>
                                <a href="{{ route('games.live', $sched->id) }}" class="btn btn-success btn-sm text-white">Start Game</a>
                            </div>
                            <div class="col-3">
                                <img class="logo img-fluid" src="{{ asset($sched->awayTeam->club->logo) }}"/>
                                <div class="pt-2 pb-1">{{ $sched->awayTeam->name }}</div>
                                <span class="text-body-tertiary small">Away</span>
                            </div>
                            <div class="col-2"></div>
                        </div>
                        <div class="d-flex justify-content-between text-secondary pt-2">
                            <div class="pe-5 text-end">
                                {{ $sched->competition->name }}
                                <i class="bi bi-tag"></i>
                            </div>
                            <div class="ps-5">
                                <i class="bi bi-geo-alt"></i>
                                {{ $sched->location->name }}
                            </div>
                        </div>
                    </div>
        @endforeach

                </div><!--/.rounded-->
            </div><!--/.col-8-->
            <div class="col-4">
                <div class="rounded rounded-3 bg-white p-4 mb-1">
                    weather?
                </div>
            </div>
        </div><!--/.row-->
    @endif

    </div><!--/container-->
@endsection
