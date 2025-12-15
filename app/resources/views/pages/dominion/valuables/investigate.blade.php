@extends('layouts.master')

@section('page-header', 'Investigate Valuable')

@section('content')
    <div class="row">

        <div class="col-sm-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="ra ra-scout"></i> Start Investigation</h3>
                </div>
                <form action="{{ route('dominion.espionage.valuables.investigate', $valuable) }}" method="post" role="form">
                    @csrf
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Valuable</label>
                                    <p class="form-control-static">
                                        <strong>{{ $valuablesHelper->getDiscoveryDisplay($valuable) }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Target Dominion</label>
                                    <p class="form-control-static">
                                        <strong>{{ $valuable->targetDominion->name }}</strong> (#{{ $valuable->targetDominion->realm->number }})
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Select Investigation Duration</label>
                                    <table class="table table-condensed table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Duration</th>
                                                <th class="text-center">Spies Required</th>
                                                <th class="text-center">Completes At</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $hourOptions = [6, 12, 18, 24, 30, 36];
                                                $recommendedHours = 24;
                                            @endphp
                                            @foreach ($hourOptions as $hours)
                                                @php
                                                    $spiesNeeded = (int) ceil($requiredSpyHours / $hours);
                                                    $isWithinBounds = $spiesNeeded >= $minSpies && $spiesNeeded <= $maxSpies;
                                                    $hasEnoughSpies = $spiesNeeded <= $availableSpies;
                                                    $isValid = $isWithinBounds && $hasEnoughSpies;
                                                    $completesAt = now()->addHours($hours);
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
                                                        } else {
                                                            $reason = 'Too slow (max ' . number_format($maxSpies) . ' spies)';
                                                        }
                                                    }
                                                @endphp
                                                <tr class="{{ $rowClass }}">
                                                    <td class="text-center">
                                                        <strong>{{ $hours }} hours</strong>
                                                        @if ($hours === $recommendedHours && $isValid)
                                                            <span class="label label-success">Recommended</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        {{ number_format($spiesNeeded) }}
                                                        @if (!$isValid)
                                                            <br><small class="text-danger">{{ $reason }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span title="{{ $completesAt }}">{{ $completesAt->format('M j, g:ia') }}</span>
                                                        <br><small class="text-muted">{{ $completesAt->diffForHumans() }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($isValid)
                                                            <button type="submit"
                                                                    name="spies_assigned"
                                                                    value="{{ $spiesNeeded }}"
                                                                    class="btn btn-sm btn-primary"
                                                                    {{ $selectedDominion->isLocked() ? 'disabled' : '' }}>
                                                                <i class="ra ra-scout"></i> Start
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
                    <p><strong>Note:</strong> Once you start an investigation, you cannot change or cancel it.</p>
                    <p>This {{ $valuable->rarity }} valuable requires <strong>{{ number_format($requiredSpyHours) }}</strong> spy-hours to complete.</p>
                    <p>You have <strong>{{ number_format($availableSpies) }}</strong> {{ str_plural('spy', $availableSpies) }} available.</p>
                    <p><strong>After completion:</strong> You'll have 12 hours to sell the valuable before the opportunity expires.</p>
                </div>
            </div>
        </div>

    </div>
@endsection
