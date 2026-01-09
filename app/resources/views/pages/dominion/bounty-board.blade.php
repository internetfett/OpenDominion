@extends('layouts.master')

@section('page-header', 'Bounty Board')

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="ra ra-hanging-sign"></i> Bounty Board</h3>
                </div>
                <div class="box-body table-responsive">
                    @include('partials.dominion.bounty.info-table', [
                        'bounties' => $bountiesActive,
                        'emptyMessage' => 'No bounties available.'
                    ])
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="ra ra-hanging-sign"></i> Recently Bountied</h3>
                </div>
                <div class="box-body table-responsive">
                    @include('partials.dominion.bounty.info-table', [
                        'bounties' => $bountiesInactive,
                        'emptyMessage' => ''
                    ])
                </div>
            </div>

            {{-- NEW SECTION: Realm Valuables Available for Transfer --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="ra ra-gem"></i> Realm Valuables Available for Transfer
                    </h3>
                </div>
                <div class="box-body">
                    @if ($realmValuablesForTransfer->isEmpty())
                        <p class="text-center text-muted">No valuables currently available for transfer from your realm mates.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>Offered By</th>
                                        <th>Valuable</th>
                                        <th>Target</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($realmValuablesForTransfer as $valuable)
                                        <tr>
                                            <td>
                                                @if ($valuable->source_dominion_id === $selectedDominion->id)
                                                    <strong>You</strong>
                                                @else
                                                    {{ $valuable->sourceDominion->name }}
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $valuable->name }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ ucfirst($valuable->rarity) }} {{ ucfirst($valuable->type) }}
                                                </small>
                                            </td>
                                            <td>
                                                {{ $valuable->targetDominion->name }}
                                                <br>
                                                <small class="text-muted">(#{{ $valuable->targetDominion->realm->number }})</small>
                                            </td>
                                            <td>
                                                <strong>{{ number_format($valuablesHelper->getTransferPrice($valuable)) }}</strong> platinum
                                                <br>
                                                <small class="text-muted">
                                                    Spy-Hours: {{ number_format($valuable->spy_hours) }}
                                                </small>
                                            </td>
                                            <td>
                                                @if ($valuable->source_dominion_id === $selectedDominion->id)
                                                    {{-- Seller sees unlist button --}}
                                                    <form action="{{ route('dominion.espionage.valuables.unlist', $valuable) }}" method="post">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning" {{ $selectedDominion->isLocked() ? 'disabled' : '' }}>
                                                            Unlist
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Buyer sees purchase button --}}
                                                    <form action="{{ route('dominion.espionage.valuables.purchase', $valuable) }}" method="post">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm btn-success"
                                                                {{ ($selectedDominion->isLocked() || $selectedDominion->resource_platinum < $valuablesHelper->getTransferPrice($valuable)) ? 'disabled' : '' }}>
                                                            Purchase
                                                        </button>
                                                    </form>
                                                    @if ($selectedDominion->resource_platinum < $valuablesHelper->getTransferPrice($valuable))
                                                        <small class="text-danger">Insufficient platinum</small>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Information</h3>
                        </div>
                        <div class="box-body">
                            <p>Info ops that you have requested to be collected by your realmies appear here.</p>
                            <p>The first {{ $bountyService::DAILY_RP_LIMIT }} bounties per day will award {{ $bountyService::REWARD_AMOUNT }} research points.</p>
                            <p>The first {{ $bountyService::DAILY_XP_LIMIT }} bounties per day will award an additional {{ $bountyService::XP_AMOUNT }} XP.</p>
                            <p>Any info op on a dominion that has been marked for observation will count as a bounty. There are currently <b>{{ count($selectedDominion->realm->getSetting('observeDominionIds') ?? []) }}</b> dominions under observation.</p>
                            <p>Bounties collected from bots or ops that have already been taken for the current tick will earn no rewards. You cannot collect your own bounties.</p>
                            <p>You have {{ number_format($selectedDominion->resource_mana) }} mana, {{ sprintf("%.4g", $selectedDominion->wizard_strength) }}% wizard strength, and {{ sprintf("%.4g", $selectedDominion->spy_strength) }}% spy strength.</p>
                            <p>You have collected <b>{{ $bountiesCollected }}</b> bounties today.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
