@extends('layouts.master')

@section('page-header', 'Explore')

@section('content')


@if ((bool)$selectedDominion->race->getPerkValue('cannot_explore'))
    <div class="row">
        <div class="col-sm-12 col-md-9">
          @if ($protectionService->isUnderProtection($selectedDominion))
              <div class="box box-primary">
                  <div class="box-header with-border">
                      <h3 class="box-title"><i class="ra ra-crossed-swords"></i> Explore</h3>
                  </div>
                  <div class="box-body">
                      You are currently under protection for <b>{{ number_format($protectionService->getUnderProtectionHoursLeft($selectedDominion), 2) }}</b> more hours and may not explore during that time.
                  </div>
              </div>
          @elseif ($selectedDominion->morale < 20)
              <div class="box box-primary">
                  <div class="box-header with-border">
                      <h3 class="box-title"><i class="ra ra-crossed-swords"></i> Explore</h3>
                  </div>
                  <div class="box-body">
                      Your military needs at least 20% morale to explore. Your military currently has {{ $selectedDominion->morale }}% morale.
                  </div>
              </div>
          @else
            <div class="box box-primary">
                <p>Your race is not able to obtain land by exploring.</p>
                <p>Grow your <a href="{{ route('dominion.military') }}">military power</a> and <a href="{{ route('dominion.invade') }}">invade other dominions</a>.</p>
            </div>
        </div>
    </div>
@elseif ($selectedDominion->resource_food > 0 or $selectedDominion->race->getPerkMultiplier('food_consumption') == -1)

    <div class="row">

        <div class="col-sm-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="ra ra-telescope"></i> Explore Land</h3>
                </div>
                <form action="{{ route('dominion.explore') }}" method="post" role="form">
                    @csrf
                    <div class="box-body table-responsive no-padding">
                        <table class="table">
                            <colgroup>
                                <col>
                                <col width="100">
                                <col width="100">
                                <col width="100">
                                <col width="100">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Terrain</th>
                                    <th class="text-center">Owned</th>
                                    <th class="text-center">Barren</th>
                                    <th class="text-center">Exploring</th>
                                    <th class="text-center">Explore For</th>
                                    @if ($selectedDominion->race->name == 'Beastfolk')
                                    <th class="text-center">Bonus</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($landHelper->getLandTypes() as $landType)
                                    <tr>
                                        <td>
                                            {{ ucfirst($landType) }}
                                            @if ($landType === $selectedDominion->race->home_land_type)
                                                <br>
                                                <small class="text-muted"><i><span title="This is the land type where your {{ strtolower($selectedDominion->race->name) }} race constructs home buildings on">Home land type</span></i></small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ number_format($selectedDominion->{'land_' . $landType}) }}
                                            <small>
                                                ({{ number_format((($selectedDominion->{'land_' . $landType} / $landCalculator->getTotalLand($selectedDominion)) * 100), 1) }}%)
                                            </small>
                                        </td>
                                        <td class="text-center">{{ number_format($landCalculator->getTotalBarrenLandByLandType($selectedDominion, $landType)) }}</td>
                                        <td class="text-center">{{ number_format($queueService->getExplorationQueueTotalByResource($selectedDominion, "land_{$landType}")) }}</td>
                                        <td class="text-center">
                                            <input type="number" name="explore[land_{{ $landType }}]" class="form-control text-center" placeholder="0" min="0" max="{{ $explorationCalculator->getMaxAfford($selectedDominion) }}" value="{{ old('explore.' . $landType) }}" {{ $selectedDominion->isLocked() ? 'disabled' : null }}>
                                        </td>
                                        @if ($selectedDominion->race->name == 'Beastfolk')
                                        <td class="text-center">
                                          @if($landType == 'plain')
                                              +{{ round(100 * 0.2 * ($selectedDominion->{'land_' . $landType} / $landCalculator->getTotalLand($selectedDominion)), 3) }}% Offensive Power
                                          @elseif($landType == 'mountain')
                                              +{{ round(100 * ($selectedDominion->{'land_' . $landType} / $landCalculator->getTotalLand($selectedDominion)), 3) }}% Platinum Production
                                          @elseif($landType == 'swamp')
                                              +{{ round(100 * 2 * ($selectedDominion->{'land_' . $landType} / $landCalculator->getTotalLand($selectedDominion)), 3) }}% Wizard Strength
                                          @elseif($landType == 'cavern')
                                              +{{ round(100 * ($selectedDominion->{'land_' . $landType} / $landCalculator->getTotalLand($selectedDominion)), 3) }}% Spy Strength
                                          @elseif($landType == 'forest')
                                              +{{ round(100 * ($selectedDominion->{'land_' . $landType} / $landCalculator->getTotalLand($selectedDominion)), 3) }}% Max Population
                                          @elseif($landType == 'hill')
                                              +{{ round(100 * ($selectedDominion->{'land_' . $landType} / $landCalculator->getTotalLand($selectedDominion)), 3) }}% Defensive Power
                                          @elseif($landType == 'water')
                                              +{{ round(100 * 5 * ($selectedDominion->{'land_' . $landType} / $landCalculator->getTotalLand($selectedDominion)), 3) }}% Food and Boat Production
                                          @endif
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary" {{ $selectedDominion->isLocked() ? 'disabled' : null }}>Explore</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Information</h3>
                    <a href="{{ route('dominion.advisors.land') }}" class="pull-right">Land Advisor</a>
                </div>
                <div class="box-body">
                    <p>Exploration will net you additional acres of barren land to construct buildings upon and will take <b>12 hours</b> to process.</p>
                    <p>Exploration per acre of barren land will come at a cost of {{ number_format($explorationCalculator->getPlatinumCost($selectedDominion)) }} platinum and {{ number_format($explorationCalculator->getDrafteeCost($selectedDominion)) }} {{ str_plural('draftee', $explorationCalculator->getDrafteeCost($selectedDominion)) }}.</p>
                    <p>You have {{ number_format($selectedDominion->resource_platinum) }} platinum and {{ number_format($selectedDominion->military_draftees) }} {{ str_plural('draftee', $selectedDominion->military_draftees) }}.</p>
                    <p>You can afford to explore for <b>{{ number_format($explorationCalculator->getMaxAfford($selectedDominion)) }} {{ str_plural('acre', $explorationCalculator->getMaxAfford($selectedDominion)) }} of barren land</b> at that rate.</p>
                </div>
            </div>
        </div>

    </div>
    @else
        <div class="row">
            <div class="col-sm-12 col-md-9">
                <div class="box box-primary">
                    <p>Due to starvation, you cannot explore until you have more food.</p>
                    <p>Go to the <a href="{{ route('dominion.bank') }}">National Bank</a> to convert other resources to food or <a href="{{ route('dominion.construct') }}">build more farms</a>.</p>
                </div>
            </div>
        </div>
    @endif
@endsection
