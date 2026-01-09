@extends('layouts.master')

@section('page-header', 'Investigate Valuable')

@section('content')
    <div class="row">

        <div class="col-sm-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="ra ra-spy"></i> Plan Valuable Theft</h3>
                </div>
                <form action="{{ route('dominion.espionage.valuables.investigate', $valuable) }}" method="post" role="form">
                    @csrf
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="icon fa fa-info-circle"></i>
                                    Your spies have discovered <strong>{{ $valuablesHelper->getDiscoveryDisplay($valuable) }}</strong> belonging to
                                    <strong>{{ $valuable->targetDominion->name }}</strong> (#{{ $valuable->targetDominion->realm->number }}).
                                    Assign spies to plan and execute the theft. They will investigate the target, determine the optimal time to strike, and steal the valuable.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fa fa-clock-o"></i> Heist Duration</label>
                                    <p class="text-muted small">Choose how long your spies will take to plan and execute the theft. Faster operations require more spies.</p>
                                    <table class="table table-condensed table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Duration</th>
                                                <th class="text-center">Spies Required</th>
                                                <th class="text-center">Total Spy Strength</th>
                                                <th class="text-center">Completes At</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $hourOptions = [6, 12, 18, 24, 30, 36];
                                            @endphp
                                            @foreach ($hourOptions as $hours)
                                                @php
                                                    $spiesNeeded = (int) ceil($requiredSpyHours / $hours);
                                                    $isWithinBounds = $spiesNeeded >= $minSpies && $spiesNeeded <= $maxSpies;
                                                    $hasEnoughSpies = $spiesNeeded <= $availableSpies;
                                                    $spyStrengthCost = $valuablesHelper->getInvestigationSpyStrengthCost($hours);

                                                    // Check if this investigation would make spy strength regen negative
                                                    // Each investigation costs 2% per hour
                                                    $wouldBeNegativeRegen = $currentSpyStrengthRegen < $valuablesHelper::SPY_STRENGTH_PER_INVESTIGATION;

                                                    $isValid = $isWithinBounds && $hasEnoughSpies && !$wouldBeNegativeRegen;
                                                    $completesAt = now()->startOfHour()->addHours($hours);
                                                    $rowClass = '';
                                                    $disabled = '';
                                                    $reason = '';

                                                    if (!$isValid) {
                                                        $rowClass = 'text-muted';
                                                        $disabled = 'disabled';
                                                        if (!$hasEnoughSpies) {
                                                            $reason = 'Not enough spies';
                                                        } elseif ($spiesNeeded < $minSpies) {
                                                            $reason = 'Too few spies (min ' . number_format($minSpies) . ')';
                                                        } elseif ($wouldBeNegativeRegen) {
                                                            $reason = 'Would cause negative spy strength recovery';
                                                        } else {
                                                            $reason = 'Too slow (max ' . number_format($maxSpies) . ' spies)';
                                                        }
                                                    }
                                                @endphp
                                                <tr class="{{ $rowClass }}">
                                                    <td class="text-center">
                                                        <strong>{{ $hours }} hours</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        {{ number_format($spiesNeeded) }}
                                                        @if (!$isValid)
                                                            <br><small class="text-danger">{{ $reason }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        {{ number_format($spyStrengthCost, 2) }}%
                                                    </td>
                                                    <td class="text-center">
                                                        <span title="{{ $completesAt }}">{{ $completesAt }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($isValid)
                                                            <button type="submit"
                                                                    name="spies_assigned"
                                                                    value="{{ $spiesNeeded }}"
                                                                    class="btn btn-sm btn-primary"
                                                                    {{ $selectedDominion->isLocked() ? 'disabled' : '' }}>
                                                                <i class="ra ra-scout"></i> Select
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-default" disabled>
                                                                Unavailable
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('dominion.espionage') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Information</h3>
                </div>
                <div class="box-body">
                    <p>Choose how quickly you want to complete the investigation. Faster investigations require more spies.</p>
                    <p>This {{ $valuable->rarity }} valuable requires <strong>{{ number_format($requiredSpyHours) }}</strong> spy-hours to complete.</p>
                    <p>You have <strong>{{ number_format($availableSpies) }}</strong> {{ str_plural('spy', $availableSpies) }} available.</p>
                    <p><strong>Spy Strength:</strong> Each investigation reduces spy strength recovery by a flat <strong>2% per hour</strong>. Your current regeneration is <strong>{{ number_format($currentSpyStrengthRegen, 2) }}%</strong> per hour.</p>
                    <p class="text-warning"><i class="fa fa-warning"></i> <strong>Warning:</strong> You can run up to {{ floor($currentSpyStrengthRegen / 2) }} investigation(s) simultaneously without going into negative spy strength recovery.</p>
                    <p><strong>After completion:</strong> You'll have 12 hours to sell the valuable before the opportunity expires.</p>
                </div>
            </div>
        </div>

    </div>
@endsection
