@extends('layouts.master')

@section('page-header', 'Hero Battles')

@section('content')
    <div class="row">

        <div class="col-sm-12 col-md-9">
            <form action="{{ route('dominion.heroes.battles.practice') }}" method="post" role="form">
                @csrf
                <div class="card card-primary">
                    <div class="card-header">
                        <span class="card-title"><i class="ra ra-axe"></i> Practice Battles</span>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table">
                            <colgroup>
                                <col width="5%">
                                <col width="35%">
                                <col width="60%">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Encounter</th>
                                    <th>Source</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="radio" id="encounter-default" name="encounter" value="default" checked /></td>
                                    <td><label for="encounter-default" style="font-weight: normal;">Evil Twin</label></td>
                                    <td>Clone with your current stats</td>
                                </tr>
                                @foreach ($encounters as $key => $encounter)
                                    <tr>
                                        <td><input type="radio" id="encounter-{{ $key }}" name="encounter" value="{{ $key }}" /></td>
                                        <td>
                                            <label for="encounter-{{ $key }}" style="font-weight: normal;">{{ $encounter->name() }}</label>
                                            @if ($encounter->maxPartySize() > 1)
                                                <span class="badge text-bg-info">Party of {{ $encounter->maxPartySize() }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $encounter->source() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($companions->isNotEmpty())
                            <label for="companion_hero_id" class="form-label">Bring a realmmate's hero <small class="text-muted">(party encounters only)</small></label>
                            <select name="companion_hero_id" id="companion_hero_id" class="form-select">
                                <option value="">Go alone</option>
                                @foreach ($companions as $companion)
                                    <option value="{{ $companion->id }}">{{ $companion->name }} ({{ $heroHelper->getClassDisplayName($companion->class) }}, {{ $companion->dominion->name }})</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" type="submit">Start Practice Battle</button>
                    </div>
                </div>
            </form>

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
        </div>

    </div>
@endsection
