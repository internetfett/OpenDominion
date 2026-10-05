@extends('layouts.master')

@section('page-header', 'Hero Battles')

@section('content')
    <div class="row">

        <div class="col-sm-12 col-md-9">
            <div class="card card-primary">
                <div class="card-header">
                    <span class="card-title"><i class="ra ra-axe"></i> Active Battles</span>
                </div>
                <div class="card-body">
                    @foreach ($activeBattles as $battle)
                        @php $view = $battlePresenter->present($battle, $selectedDominion->id); @endphp
                        <div class="row">
                            <div class="col-md-6">
                                @foreach ($view->sides() as $side)
                                    <h5 class="mb-2">{{ $side['label'] }}</h5>
                                    <div class="row">
                                        @foreach ($side['combatants'] as $combatant)
                                            @include('partials.dominion.hero-combatant', ['view' => $view, 'combatant' => $combatant])
                                        @endforeach
                                    </div>
                                @endforeach
                                @if ($battle->finished)
                                    <div class="text-center">
                                        @if ($battle->isDraw())
                                            <h4>Draw!</h4>
                                        @else
                                            <h4>{{ $battle->winnerLabel() }} {{ $battle->winningCombatants()->whereNotNull('hero_id')->count() > 1 ? 'win' : 'wins' }}!</h4>
                                        @endif
                                    </div>
                                @endif
                                @foreach ($view->commandable() as $combatant)
                                    @include('partials.dominion.hero-combat-controls', ['view' => $view, 'combatant' => $combatant])
                                @endforeach
                            </div>
                            <div class="col-md-6" style="max-height: {{ count($view->battle->combatants()) > 2 ? '645px' : '272px' }}; overflow-y: scroll;">
                                @include('partials.dominion.hero-combat-log', ['view' => $view, 'battle' => $battle])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <span class="card-title"><i class="ra ra-axe"></i> Previous Battles</span>
                    <div class="float-end">
                        <a href="{{ route('dominion.heroes.battles.leaderboard') }}">View Leaderboard</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Result</th>
                                        <th>Combatants</th>
                                        <th>Winner</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                @foreach ($inactiveBattles as $battle)
                                    <tr>
                                        <td>
                                            @if ($battle->isDraw())
                                                Draw
                                            @elseif ($battle->hasWinningController($selectedDominion->id))
                                                Win
                                            @else
                                                Loss
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('dominion.heroes.battles.report', ['battle'=>$battle->id]) }}">
                                                {{ $battle->matchupLabel() }}
                                            </a>
                                        </td>
                                        <td>{{ $battle->winnerLabel() ?? '--' }}</td>
                                        <td>{{ $battle->created_at }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Information</span>
                </div>
                <div class="card-body">
                    @include('partials.dominion.hero-combat')
                    @if ($activeBattles->where('finished', false)->count() == 0)
                        <a class="btn btn-primary btn-block mb-2" href="{{ route('dominion.heroes.battles.practice') }}">
                            Practice Battles
                        </a>
                        @if ($hero->isInQueue())
                            <a class="btn btn-danger btn-block" href="{{ route('dominion.heroes.battles.dequeue') }}">
                                Leave Queue
                            </a>
                        @else
                            <a class="btn btn-primary btn-block" href="{{ route('dominion.heroes.battles.queue') }}">
                                Queue for Battle
                            </a>
                        @endif
                    @endif
                </div>
            </div>

            <!--
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Class-Based Abilities</span>
                </div>
                <div class="card-body">
                    Each hero gains one ability based on their currently active class:
                    <ul>
                        <li>Alchemist - Volatile Mixture: Attack for 150% damage, but 20% chance to hit yourself.</li>
                        <li>Architect - Fortify: Prevent the next 20 non-counter damage dealt.</li>
                        <li>Blacksmith - Forge: Increases attack value by 1 for the remainder of the battle.</li>
                        <li>Engineer - Tactical Awareness: Reduces target's counter value by 2 for the remainder of the battle.</li>
                        <li>Farmer - Hardiness: Remain on 1 health the first time your health would be reduced below 1.</li>
                        <li>Healer - Mending: Focus enhances your Recover ability, increasing healing.</li>
                        <li>Infiltrator - Shadow Strike: Attack that cannot be evaded and deals +2 damage if the target is defending.</li>
                        <li>Sorcerer - Channeling: Focus can be used while already active, stacking bonus damage.</li>
                        <li>Scholar - Combat Analysis: Decreases target's defense value by 1 for the remainder of the battle.</li>
                        <li>Scion - Last Stand: When at 40 health or less, all combat stats are increased by 10%.</li>
                    </ul>
                </div>
            </div>
            -->
        </div>

    </div>
@endsection
