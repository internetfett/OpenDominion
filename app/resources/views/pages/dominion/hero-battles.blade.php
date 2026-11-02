@extends('layouts.master')

@section('page-header', 'Hero Battles')

@section('content')
    <div class="row">

        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <span class="card-title"><i class="ra ra-axe"></i> Active Battles</span>
                    @if ($activeBattles->where('finished', false)->count() == 0)
                        <div class="float-end">
                            <a class="btn btn-primary btn-sm" href="{{ route('dominion.heroes.battles.practice') }}">
                                Practice Battles
                            </a>
                            @if ($hero->isInQueue())
                                <a class="btn btn-danger btn-sm" href="{{ route('dominion.heroes.battles.dequeue') }}">
                                    Leave Queue
                                </a>
                            @else
                                <a class="btn btn-primary btn-sm" href="{{ route('dominion.heroes.battles.queue') }}">
                                    Queue for Battle
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @foreach ($activeBattles as $battle)
                        @include('partials.dominion.hero-battle', ['view' => $battlePresenter->present($battle, $selectedDominion->id), 'showOrders' => true])
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-9">
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

@push('inline-scripts')
    <script type="text/javascript">
        document.querySelectorAll('[data-battle-orders]').forEach(function (box) {
            var form = box.querySelector('form');
            var lock = box.querySelector('[data-orders-lock]');
            var count = box.querySelector('[data-orders-count]');
            var heroes = Array.from(box.querySelectorAll('[data-hero-orders][data-manual]'));

            function refresh() {
                var chosen = heroes.filter(function (hero) {
                    return hero.querySelector('[data-order-action]').value !== '';
                }).length;
                if (count) {
                    count.textContent = chosen + ' of ' + heroes.length + ' chosen';
                }
                lock.disabled = heroes.length === 0 || chosen < heroes.length;
            }

            heroes.forEach(function (hero) {
                var action = hero.querySelector('[data-order-action]');
                var target = hero.querySelector('[data-order-target]');
                var pick = hero.querySelector('[data-pick]');
                var buttons = hero.querySelectorAll('.battle-action');
                var pickers = hero.querySelectorAll('[data-target-pick]');

                function select(button) {
                    buttons.forEach(function (other) {
                        other.classList.toggle('selected', other === button);
                    });
                }

                function choose(button, targetId, label) {
                    action.value = button.dataset.action;
                    target.value = targetId || '';
                    pick.textContent = label;
                    pick.classList.add('chosen');
                    select(button);
                    pickers.forEach(function (picker) {
                        picker.hidden = true;
                    });
                    refresh();
                }

                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        button.dispatchEvent(new MouseEvent('mouseout', { bubbles: true, relatedTarget: document.body }));
                        if (!button.hasAttribute('data-picks')) {
                            choose(button, button.dataset.target, button.dataset.label);
                            return;
                        }
                        action.value = '';
                        target.value = '';
                        pick.textContent = 'Choose';
                        pick.classList.remove('chosen');
                        select(button);
                        pickers.forEach(function (picker) {
                            picker.hidden = picker.dataset.targetPick !== button.dataset.action;
                        });
                        refresh();
                    });
                });

                pickers.forEach(function (picker) {
                    var button = hero.querySelector('.battle-action[data-action="' + picker.dataset.targetPick + '"]');
                    picker.querySelectorAll('[data-target]').forEach(function (option) {
                        option.addEventListener('click', function () {
                            choose(button, option.dataset.target, option.dataset.label);
                        });
                    });
                    picker.querySelector('[data-target-cancel]').addEventListener('click', function () {
                        picker.hidden = true;
                        button.classList.remove('selected');
                    });
                });
            });

            form.addEventListener('submit', function () {
                lock.disabled = true;
            });

            refresh();
        });
    </script>
@endpush
