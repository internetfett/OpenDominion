@extends ('layouts.master')

@section('page-header', 'Espionage')

@section('content')
    <div class="row">

        <div class="col-sm-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-user-secret"></i> Offensive Operations</h3>
                </div>

                @if ($protectionService->isUnderProtection($selectedDominion))
                    <div class="box-body">
                        You are currently under protection for
                        @if ($protectionService->getUnderProtectionHoursLeft($selectedDominion))
                            <b>{{ number_format($protectionService->getUnderProtectionHoursLeft($selectedDominion), 2) }}</b> more hours
                        @else
                            <b>{{ $selectedDominion->protection_ticks_remaining }}</b> ticks
                        @endif
                        and may not perform any espionage operations during that time.
                    </div>
                @else
                    <form action="{{ route('dominion.espionage') }}" method="post" role="form">
                        @csrf

                        @php
                            $recentlyInvadedByDominionIds = $militaryCalculator->getRecentlyInvadedBy($selectedDominion, 12);
                        @endphp

                        <div class="box-body">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="target_dominion">Select a target</label>
                                        <select name="target_dominion" id="target_dominion" class="form-control select2" required style="width: 100%" data-placeholder="Select a target dominion" {{ $selectedDominion->isLocked() ? 'disabled' : null }}>
                                            <option></option>
                                            @foreach ($rangeCalculator->getDominionsInRange($selectedDominion, true) as $dominion)
                                                <option value="{{ $dominion->id }}"
                                                        data-race="{{ $dominion->race->name }}"
                                                        data-land="{{ number_format($landCalculator->getTotalLand($dominion)) }}"
                                                        data-percentage="{{ number_format($rangeCalculator->getDominionRange($selectedDominion, $dominion), 2) }}"
                                                        data-war="{{ $governmentService->isAtWar($selectedDominion->realm, $dominion->realm) ? 1 : 0 }}"
                                                        data-revenge="{{ in_array($dominion->id, $recentlyInvadedByDominionIds) ? 1 : 0 }}"
                                                        data-guard="{{ $guardMembershipService->isBlackGuardMember($dominion) && $guardMembershipService->isBlackGuardMember($selectedDominion) ? 1 : 0 }}"
                                                    >
                                                    {{ $dominion->name }} (#{{ $dominion->realm->number }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label>Information Gathering Operations</label>
                                </div>
                            </div>

                            @foreach ($espionageHelper->getInfoGatheringOperations()->chunk(4) as $operations)
                                <div class="row">
                                    @foreach ($operations as $operation)
                                        <div class="col-xs-6 col-sm-3 col-md-6 col-lg-3 text-center">
                                            <div class="form-group">
                                                <button type="submit" name="operation" value="{{ $operation['key'] }}" class="btn btn-primary btn-block" {{ $selectedDominion->isLocked() || !$espionageCalculator->canPerform($selectedDominion, $operation['key']) ? 'disabled' : null }}>
                                                    {{ $operation['name'] }}
                                                </button>
                                                <p>{{ $operation['description'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            <div class="row">
                                <div class="col-md-12">
                                    <label>Resource Theft Operations</label>
                                </div>
                            </div>

                            @foreach ($espionageHelper->getResourceTheftOperations()->chunk(4) as $operations)
                                <div class="row">
                                    @foreach ($operations as $operation)
                                        <div class="col-xs-6 col-sm-3 col-md-6 col-lg-3 text-center">
                                            <div class="form-group">
                                                <button type="submit"
                                                        name="operation"
                                                        value="{{ $operation['key'] }}"
                                                        class="btn btn-primary btn-block"
                                                        {{ $selectedDominion->isLocked() || $selectedDominion->round->hasOffensiveActionsDisabled() || !$espionageCalculator->canPerform($selectedDominion, $operation['key']) || (now()->diffInDays($selectedDominion->round->start_date) < 3) ? 'disabled' : null }}>
                                                    {{ $operation['name'] }}
                                                </button>
                                                <p>{{ $operation['description'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            <div class="row">
                                <div class="col-md-12">
                                    <label>Black Operations</label>
                                </div>
                            </div>

                            @foreach ($espionageHelper->getBlackOperations()->chunk(4) as $operations)
                                <div class="row">
                                    @foreach ($operations as $operation)
                                        <div class="col-xs-6 col-sm-3 col-md-6 col-lg-3 text-center">
                                            <div class="form-group">
                                                <button type="submit"
                                                        name="operation"
                                                        value="{{ $operation['key'] }}"
                                                        class="btn btn-primary btn-block"
                                                        {{ $selectedDominion->isLocked() || $selectedDominion->round->hasOffensiveActionsDisabled() || !$espionageCalculator->canPerform($selectedDominion, $operation['key']) || (now()->diffInDays($selectedDominion->round->start_date) < 3) ? 'disabled' : null }}>
                                                    {{ $operation['name'] }}
                                                </button>
                                                <p>{{ $operation['description'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            <div class="row">
                                <div class="col-md-12">
                                    <label>War Operations</label>
                                </div>
                            </div>

                            @foreach ($espionageHelper->getWarOperations()->chunk(4) as $operations)
                                <div class="row">
                                    @foreach ($operations as $operation)
                                        <div class="col-xs-6 col-sm-3 col-md-6 col-lg-3 text-center">
                                            <div class="form-group">
                                                <button type="submit"
                                                        name="operation"
                                                        value="{{ $operation['key'] }}"
                                                        class="btn btn-primary btn-block war-op disabled"
                                                        {{ $selectedDominion->isLocked() || $selectedDominion->round->hasOffensiveActionsDisabled() || !$espionageCalculator->canPerform($selectedDominion, $operation['key']) || (now()->diffInDays($selectedDominion->round->start_date) < 3) ? 'disabled' : null }}>
                                                    {{ $operation['name'] }}
                                                </button>
                                                <p>{{ $operation['description'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </form>
                @endif

            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Information</h3>
                </div>
                <div class="box-body">
                    <p>Here you can perform espionage operations on hostile dominions to gain important information for you and your realmies.</p>
                    <p>Any obtained data after successfully performing an information gathering operation gets posted to the <a href="{{ route('dominion.op-center') }}">Op Center</a> for your realmies.</p>
                    <p>Theft can only be performed on dominions greater than your size. Theft and black ops cannot be performed until the 4th day of the round.</p>
                    <p>Performing espionage operations spends some spy strength (2% for info, otherwise 5%), but it regenerates 4% every hour. You may only perform espionage operations at or above 30% strength.</p>
                    <p>You have {{ sprintf("%.4g", $selectedDominion->spy_strength) }}% spy strength.</p>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="ra ra-open-chest"></i> Valuables Discovered</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-condensed">
                        <colgroup>
                            <col width="100">
                            <col>
                            <col width="200">
                            <col width="100">
                            <col width="150">
                            <col width="100">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Discovered</th>
                                <th>Name</th>
                                <th>Target</th>
                                <th>Spies</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($selectedDominion->valuables()->active()->orderByDesc('created_at')->get() as $valuable)
                                <tr>
                                    <td>
                                        <span title="{{ $valuable->created_at }}">{{ $valuable->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $valuable->name }}</strong>
                                        <br><small class="text-muted">{{ ucfirst($valuable->rarity) }} {{ ucfirst($valuable->type) }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('dominion.op-center.show', $valuable->targetDominion->id) }}">{{ $valuable->targetDominion->name }}</a> (#{{ $valuable->targetDominion->realm->number }})
                                    </td>
                                    <td>
                                        {{ number_format($valuable->spies_assigned) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($valuable->investigation_started_at)
                                            @if ($valuable->investigation_completes_at && $valuable->investigation_completes_at > now())
                                                <span class="{{ $valuable->getProgressColorClass() }}">
                                                    {{ $valuable->getTicksRemaining() }} {{ str_plural('tick', $valuable->getTicksRemaining()) }} remaining
                                                </span>
                                                <br><small class="text-muted">({{ number_format($valuable->getInvestigationProgress(), 1) }}% complete)</small>
                                            @elseif ($valuable->investigation_completes_at)
                                                <span class="text-success">
                                                    <i class="fa fa-check"></i> Ready to steal
                                                </span>
                                                <br><small class="text-muted">Expires in {{ $valuable->getTicksUntilExpiration() }} {{ str_plural('tick', $valuable->getTicksUntilExpiration()) }}</small>
                                            @else
                                                <span class="text-muted">Calculating...</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($valuable->investigation_started_at)
                                            <form action="{{ route('dominion.espionage.valuables.cancel', $valuable) }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-block btn-danger" {{ $selectedDominion->isLocked() ? 'disabled' : '' }}>
                                                    Cancel
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('dominion.espionage.valuables.investigate', $valuable) }}" class="btn btn-sm btn-block btn-primary">
                                                <i class="ra ra-scout"></i> Investigate
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No valuables discovered</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="ra ra-open-chest"></i> Valuables Stolen</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-condensed">
                        <colgroup>
                            <col width="100">
                            <col>
                            <col width="200">
                            <col width="250">
                            <col width="100">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Stolen</th>
                                <th>Name</th>
                                <th>Target</th>
                                <th>Price History</th>
                                <th>Current Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($selectedDominion->valuables()->stolen()->orderByDesc('completed_at')->get() as $valuable)
                                <tr>
                                    <td>
                                        <span title="{{ $valuable->completed_at }}">{{ $valuable->completed_at->diffForHumans() }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $valuable->name }}</strong>
                                        <br><small class="text-muted">{{ ucfirst($valuable->rarity) }} {{ ucfirst($valuable->type) }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('dominion.op-center.show', $valuable->targetDominion->id) }}">{{ $valuable->targetDominion->name }}</a> (#{{ $valuable->targetDominion->realm->number }})
                                    </td>
                                    <td>
                                        @php
                                            $priceHistory = $espionageCalculator->getValuableSellPrice($valuable, 24);
                                        @endphp
                                        <span class="sparkline-price" data-sparkline="{{ implode(',', $priceHistory) }}"></span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($priceHistory[0]) }} platinum</strong>
                                    </td>
                                    <td>
                                        @if (!$valuable->isSold())
                                            <form action="{{ route('dominion.espionage.valuables.sell', $valuable) }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-block btn-success">
                                                    Sell
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">Sold</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No valuables stolen</td>
                                </tr>
                            @endforelse
                        </tbody>
                        {{--
                        <thead>
                            <tr class="active">
                                <th>History</th>
                                <th>Name</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th class="text-center">Sale Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($selectedDominion->valuables()->completed()->orderByDesc('updated_at')->get() as $valuable)
                                <tr>
                                    <td>
                                        @if ($valuable->sold_at)
                                            <span title="{{ $valuable->sold_at }}">{{ $valuable->sold_at->diffForHumans() }}</span>
                                        @elseif ($valuable->completed_at)
                                            <span title="{{ $valuable->completed_at }}">{{ $valuable->completed_at->diffForHumans() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($valuable->investigation_started_at)
                                            {{ $valuable->name }}
                                        @else
                                            <span class="text-muted">???</span>
                                        @endif
                                        <br><small class="text-muted">{{ ucfirst($valuable->rarity) }} {{ ucfirst($valuable->type) }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('dominion.op-center.show', $valuable->targetDominion->id) }}">{{ $valuable->targetDominion->name }}</a> (#{{ $valuable->targetDominion->realm->number }})
                                    </td>
                                    <td>
                                        @if ($valuable->sold_at)
                                            <span class="label label-success">Sold</span>
                                        @else
                                            <span class="label label-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($valuable->platinum_received)
                                            {{ number_format($valuable->platinum_received) }} platinum
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">-</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No history</td>
                                </tr>
                            @endforelse
                        </tbody>
                        --}}
                    </table>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Information</h3>
                </div>
                <div class="box-body">
                    <p>You can investigate things. Each investigation uses up 1% spy strength per hour.</p>
                    <p>Once the thing is stolen you can sell it for current market price.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.min.css') }}">
@endpush

@push('page-scripts')
    <script type="text/javascript" src="{{ asset('assets/vendor/select2/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/vendor/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
@endpush

@push('inline-scripts')
    <script type="text/javascript">
        (function ($) {
            $('#target_dominion').select2({
                templateResult: select2Template,
                templateSelection: select2Template,
            });
            $('#target_dominion').change(function(e) {
                var warStatus = $(this).find(":selected").data('war');
                var revengeStatus = $(this).find(":selected").data('revenge');
                var guardStatus = $(this).find(":selected").data('guard');
                if (warStatus == 1 || revengeStatus == 1 || guardStatus == 1) {
                    $('.war-op').removeClass('disabled');
                } else {
                    $('.war-op').addClass('disabled');
                }
            });
            @if ($targetDominion)
                $('#target_dominion').val('{{ $targetDominion }}').trigger('change.select2').trigger('change');
            @endif
            @if (session('target_dominion'))
                $('#target_dominion').val('{{ session('target_dominion') }}').trigger('change.select2').trigger('change');
            @endif
        })(jQuery);

        function select2Template(state) {
            if (!state.id) {
                return state.text;
            }

            const race = state.element.dataset.race;
            const land = state.element.dataset.land;
            const percentage = state.element.dataset.percentage;
            const war = state.element.dataset.war;
            const revenge = state.element.dataset.revenge;
            const guard = state.element.dataset.guard;
            let difficultyClass;

            if (percentage >= 133) {
                difficultyClass = 'text-red';
            } else if (percentage >= 75) {
                difficultyClass = 'text-green';
            } else if (percentage >= 60) {
                difficultyClass = 'text-muted';
            } else {
                difficultyClass = 'text-gray';
            }

            warStatus = '';
            if (war == 1) {
                warStatus = '<div class="pull-left">&nbsp;|&nbsp;<span class="text-red">WAR</span></div>';
            } else if (guard == 1) {
                warStatus = '<div class="pull-left">&nbsp;|&nbsp;<span class="text-red">SHADOW LEAGUE</span></div>';
            } else if (revenge == 1) {
                warStatus = '<div class="pull-left">&nbsp;|&nbsp;<span class="text-red">REVENGE</span></div>';
            }

            return $(`
                <div class="pull-left">${state.text.replace(/\</g,"&lt;")} - ${race}</div>
                ${warStatus}
                <div class="pull-right">${land} land <span class="${difficultyClass}">(${percentage}%)</span></div>
                <div style="clear: both;"></div>
            `);
        }

        // Initialize sparklines for price history
        $('.sparkline-price').each(function() {
            var $this = $(this);
            var sparklineData = $this.data('sparkline').toString().split(',');

            $this.sparkline(sparklineData, {
                type: 'line',
                lineColor: '#3c8dbc',
                fillColor: false,
                lineWidth: 2,
                height: '30',
                width: '150',
                spotColor: '#00a65a',
                minSpotColor: '#f39c12',
                maxSpotColor: '#00a65a',
                highlightSpotColor: '#dd4b39',
                highlightLineColor: '#222',
                tooltipSuffix: ' platinum'
            });
        });
    </script>
@endpush
