@extends('layouts.master')

@php
    $target = $selectedDominion;
    $pageHeader = 'Castle Advisor';
    if($targetDominion != null) {
        $target = $targetDominion;
        $pageHeader .= ' for '.$target->name;
    }
@endphp

@section('page-header', $pageHeader)

@section('content')
    @include('partials.dominion.advisor-selector')
    <div class="row">

        <div class="col-md-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-arrow-up"></i> Improvements</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <colgroup>
                            <col width="150">
                            <col>
                            <col width="100">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Part</th>
                                <th>Rating</th>
                                <th class="text-center">Invested</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($improvementHelper->getImprovementTypes() as $improvementType)
                                <tr>
                                    <td>
                                        {{ ucfirst($improvementType) }}
                                        <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ $improvementHelper->getImprovementHelpString($improvementType) }}"></i>
                                    </td>
                                    <td>
                                        {{ sprintf(
                                            $improvementHelper->getImprovementRatingString($improvementType),
                                            number_format($improvementCalculator->getImprovementMultiplierBonus($target, $improvementType) * 100, 2),
                                            number_format($improvementCalculator->getImprovementMultiplierBonus($target, $improvementType) * 100 * 2, 2)
                                        ) }}
                                    </td>
                                    <td class="text-center">{{ number_format($target->{'improvement_' . $improvementType}) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-md-3">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Information</h3>
                </div>
                <div class="box-body">
                    <p>The castle advisor tells you the amount of resources invested and the bonus amount. It also displays unlocked techs.</p>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-flask"></i> Technological Advances</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <colgroup>
                            <col width="200">
                            <col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($unlockedTechs = $target->techs->pluck('key')->all())
                            @foreach ($techHelper->getTechs() as $tech)
                                @if(in_array($tech->key, $unlockedTechs))
                                    <tr>
                                        <td>{{ $tech['name'] }}</td>
                                        <td>{{ $techHelper->getTechDescription($tech) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection