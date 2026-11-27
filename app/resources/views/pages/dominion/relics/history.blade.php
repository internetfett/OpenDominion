@extends ('layouts.master')

@section('page-header', $relicHelper->getName(true) . ' History')

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-9">
            <div class="card card-primary">
                <div class="card-header">
                    <span class="card-title"><i class="ra ra-gem"></i> Round History</span>
                </div>
                <div class="card-body table-responsive">
                    @if ($history->isEmpty())
                        <p class="text-center text-muted my-3">You haven't sold or salvaged any {{ strtolower($relicHelper->getName(true)) }} this round.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Result</th>
                                    <th>Platinum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($history as $relic)
                                    <tr>
                                        <td>{{ $relic->sold_at }}</td>
                                        <td>
                                            <strong class="{{ $relicHelper->getRarityClass($relic->rarity) }}">{{ $relic->name }}</strong><br>
                                            <small class="text-muted">{{ $relicHelper->getRarityLabel($relic->rarity) }} &middot; {{ $relicHelper->getCategoryLabel($relic->category) }}</small>
                                        </td>
                                        <td>{{ $relic->wasSalvaged() ? 'Salvaged' : 'Sold' }}</td>
                                        <td>{{ number_format($relic->sold_price) }}p</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Round Summary</span>
                </div>
                <div class="card-body">
                    <p>Sold or salvaged: <strong>{{ number_format($history->count()) }}</strong></p>
                    <p>Platinum earned: <strong>{{ number_format($totalPlatinum) }}p</strong></p>
                    <p class="mb-0"><a href="{{ route('dominion.relics') }}">&laquo; Back to {{ $relicHelper->getName(true) }}</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection
