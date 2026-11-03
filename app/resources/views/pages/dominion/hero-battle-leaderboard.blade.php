@extends('layouts.master')

@section('page-header', 'Hero Battles')

@section('content')
    <div class="row">

        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <span class="card-title"><i class="ra ra-trophy"></i> Leaderboard</span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Rating</th>
                                <th>Record (W-L-D)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($heroes as $hero)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $hero->name }}</td>
                                    <td>{{ $hero->combat_rating }}</td>
                                    <td>{{ $hero->stat_combat_wins }} - {{ $hero->stat_combat_losses }} - {{ $hero->stat_combat_draws }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
