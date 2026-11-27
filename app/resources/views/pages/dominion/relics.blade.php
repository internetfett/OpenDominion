@extends ('layouts.master')

@section('page-header', $relicHelper->getName(true))

@section('content')
    @php
        $singular = $relicHelper->getName();
        $plural = $relicHelper->getName(true);
        $inventoryCap = config('relics.inventory_cap');
        $roundLocked = $relicCalculator->isRoundLocked($selectedDominion->round);
        $searchIntensity = $relicCalculator->getSearchIntensity($selectedDominion);
        $hoursUntilFind = $relicCalculator->getHoursUntilFind($selectedDominion);
        $meterMax = config('relics.search.meter_max');
        $meterPercent = min(100, $selectedDominion->relic_search_meter / $meterMax * 100);
    @endphp
    <div class="row">
        <div class="col-sm-12 col-md-9">

            {{-- Inventory --}}
            <div class="card card-primary">
                <div class="card-header">
                    <span class="card-title"><i class="ra ra-open-chest"></i> Your {{ $plural }}</span>
                    <span class="float-end">{{ $inventory->count() }} / {{ $inventoryCap }}</span>
                </div>
                <div class="card-body table-responsive">
                    @if ($inventory->isEmpty())
                        <p class="text-center text-muted my-3">You aren't holding any {{ strtolower($plural) }}.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>State</th>
                                    <th>Value</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventory as $relic)
                                    @php
                                        $isEquipped = in_array($relic->loadoutKey(), $equippedKeys, true);
                                        $stats = $relicHelper->describeStats($relic);
                                        $hoursToMax = $relicCalculator->getHoursUntilMaxValue($relic);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong class="{{ $relicHelper->getRarityClass($relic->rarity) }}">{{ $relic->name }}</strong><br>
                                            <small class="text-muted">
                                                {{ $relicHelper->getRarityLabel($relic->rarity) }} &middot; {{ $relicHelper->getCategoryLabel($relic->category) }}
                                                @if ($stats)
                                                    &middot; {{ $stats }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            @if ($isEquipped)
                                                <span class="badge text-bg-success">Equipped</span>
                                            @endif
                                            @if ($relic->is_bound)
                                                <span class="badge text-bg-secondary" data-bs-toggle="tooltip" title="Bound to your hero: it can't be sold, listed or stolen.">Bound</span>
                                            @endif
                                            @if ($relic->is_listed)
                                                <span class="badge text-bg-info">Listed</span>
                                            @endif
                                            @if (!$relic->is_bound)
                                                <span class="badge text-bg-warning" data-bs-toggle="tooltip" title="Unbound {{ strtolower($plural) }} can be stolen.">Stealable</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($relic->is_bound)
                                                {{ number_format($relicCalculator->getSalvageValue($relic->rarity)) }}p
                                                <small class="text-muted">salvage</small>
                                            @else
                                                {{ number_format($relicCalculator->getMarketPrice($relic)) }}p
                                                @if ($hoursToMax > 0)
                                                    <br><small class="text-muted">max {{ number_format($relicHelper->getRarity($relic->rarity)['max']) }}p in {{ $hoursToMax }}h</small>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($isEquipped)
                                                <a href="{{ route('dominion.heroes') }}" class="btn btn-sm btn-secondary">Hero</a>
                                            @elseif ($relic->is_bound)
                                                <form action="{{ route('dominion.relics.salvage', $relic->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning">Salvage</button>
                                                </form>
                                            @else
                                                <form action="{{ route('dominion.relics.sell', $relic->id) }}" method="post" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary">Sell</button>
                                                </form>
                                                @if ($relic->is_listed)
                                                    <form action="{{ route('dominion.relics.unlist', $relic->id) }}" method="post" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-secondary">Unlist</button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('dominion.relics.list', $relic->id) }}" method="post" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Offer to realmmates for {{ number_format($relicCalculator->getRealmPrice($relic->rarity)) }}p">List</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- Realm listings --}}
            <div class="card card-primary mt-3">
                <div class="card-header">
                    <span class="card-title"><i class="ra ra-scroll-unfurled"></i> For Sale in Your Realm</span>
                </div>
                <div class="card-body table-responsive">
                    @if ($listings->isEmpty())
                        <p class="text-center text-muted my-3">Your realmmates have nothing listed.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Seller</th>
                                    <th>Price</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listings as $relic)
                                    @php
                                        $price = $relicCalculator->getRealmPrice($relic->rarity);
                                        $stats = $relicHelper->describeStats($relic);
                                        $cannotBuy = $selectedDominion->resource_platinum < $price || $inventory->count() >= $inventoryCap;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong class="{{ $relicHelper->getRarityClass($relic->rarity) }}">{{ $relic->name }}</strong><br>
                                            <small class="text-muted">
                                                {{ $relicHelper->getRarityLabel($relic->rarity) }} &middot; {{ $relicHelper->getCategoryLabel($relic->category) }}
                                                @if ($stats)
                                                    &middot; {{ $stats }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>{{ $relic->owner->name }}</td>
                                        <td>{{ number_format($price) }}p</td>
                                        <td class="text-end">
                                            <form action="{{ route('dominion.relics.purchase', $relic->id) }}" method="post" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" {{ $cannotBuy ? 'disabled' : '' }}>Buy</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            @if ($relicHelper->isSourceEnabled('search'))
                <div class="card">
                    <div class="card-header">
                        <span class="card-title"><i class="fa fa-search"></i> Search</span>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-2" role="progressbar" aria-valuenow="{{ $meterPercent }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: {{ $meterPercent }}%">{{ number_format($selectedDominion->relic_search_meter, 1) }}</div>
                        </div>
                        <p class="mb-2">
                            @if ($searchIntensity === 0)
                                Search is off.
                            @elseif ($hoursUntilFind === null)
                                Your spy ratio is too low for Search to make progress.
                            @else
                                Next find in about <strong>{{ $hoursUntilFind }}</strong> {{ Str::plural('hour', $hoursUntilFind) }}.
                            @endif
                        </p>
                        <form action="{{ route('dominion.relics.search') }}" method="post">
                            @csrf
                            <div class="mb-2">
                                <select name="intensity" class="form-select form-select-sm" {{ $selectedDominion->isLocked() ? 'disabled' : '' }}>
                                    <option value="0" {{ $searchIntensity === 0 ? 'selected' : '' }}>Off</option>
                                    @foreach (config('relics.search.intensities') as $intensity)
                                        <option value="{{ $intensity }}" {{ $searchIntensity === $intensity ? 'selected' : '' }}>{{ $intensity }}% spy strength per hour</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-check mb-2">
                                <input type="hidden" name="stop_on_find" value="0">
                                <input class="form-check-input" type="checkbox" name="stop_on_find" value="1" id="stop_on_find" {{ $relicCalculator->isSearchStopOnFind($selectedDominion) ? 'checked' : '' }}>
                                <label class="form-check-label" for="stop_on_find">Stop when I find something</label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100" {{ $selectedDominion->isLocked() ? 'disabled' : '' }}>Update Search</button>
                        </form>
                        @if ($roundLocked)
                            <p class="form-text text-warning mb-0 mt-2">Nothing can be found until the fourth day of the round.</p>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card mt-3">
                <div class="card-header">
                    <span class="card-title">How {{ $plural }} Work</span>
                </div>
                <div class="card-body">
                    <p>You own a {{ strtolower($singular) }} the moment you find it. Its market value climbs from its rarity's minimum to its maximum over {{ config('relics.appreciation_hours') }} hours.</p>
                    <p><strong>Sell</strong> for its current value, or <strong>List</strong> it for realmmates at its rarity's minimum.</p>
                    <p>Equipment can be equipped on your hero, which <strong>binds</strong> it: bound {{ strtolower($plural) }} can't be sold, listed or stolen, only salvaged for {{ config('relics.salvage_ratio') * 100 }}% of their minimum.</p>
                    <p class="text-warning">Unbound {{ strtolower($plural) }} can be stolen by rival spies, even while listed.</p>
                    <p class="mb-0"><a href="{{ route('dominion.relics.history') }}">View round history &raquo;</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection
