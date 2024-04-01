@extends('layouts.main')

@section('icon', 'bi-house-fill')
@section('page-title', 'Home')
@section('page-desc', "Welcome to your team's home page")

@section('content')
    <div class="container" style="margin-top: -130px">

    @if (count($scheduled))
        @foreach ($scheduled as $sched)
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

                    <div class="row border-bottom pb-2 text-center">
                        <div class="col-4">
                            <h3>{{ $sched->homeTeam->name }}</h3>
                            <span class="small">Home</span>
                        </div>
                        <div class="col-4">
                            <div class="">{{ $sched->date->inUserTimezone()->format('g:i a') }}</div>
                        </div>
                        <div class="col-4">
                            <h3>{{ $sched->awayTeam->name }}</h3>
                            <span class="small">Away</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-2">
                        <div class="">{{ $sched->competition->name }}</div>
                        <div class="">{{ $sched->location->name }}</div>
                    </div>

                </div>

            </div><!--/.col-8-->
            <div class="col-4">
                <div class="rounded rounded-3 bg-white p-4 mb-1">
                    weather?
                </div>
            </div>
        </div><!--/.row-->
        @endforeach
    @endif

    </div><!--/container-->
@endsection
