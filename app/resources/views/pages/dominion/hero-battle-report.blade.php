@extends('layouts.master')

@section('page-header', 'Hero Battles')

@section('content')
    <div class="row">

        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <span class="card-title"><i class="ra ra-axe"></i> Battle Report</span>
                </div>
                <div class="card-body">
                    @include('partials.dominion.hero-battle', ['view' => $view, 'showOrders' => false])
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">How Hero Battles Work</span>
                </div>
                <div class="card-body">
                    @include('partials.dominion.hero-combat')
                </div>
            </div>
        </div>

    </div>
@endsection
