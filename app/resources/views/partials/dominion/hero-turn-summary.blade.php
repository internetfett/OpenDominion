@php
    $turns ??= $view->turns();
    $latest = array_shift($turns);
@endphp
<div class="turn-summary">
    @if ($latest === null)
        <p class="text-muted mb-0">No turns have been resolved yet.</p>
    @else
        <div class="turn-summary-head">Turn {{ $latest['turn'] }} results</div>
        @include('partials.dominion.hero-turn-phases', ['summary' => $latest])

        @if ($turns)
            <div class="turn-summary-earlier">
                <div class="turn-summary-label">Earlier turns</div>
                @foreach ($turns as $summary)
                    <details>
                        <summary>Turn {{ $summary['turn'] }} <span class="turn-gist">{!! \OpenDominion\HeroCombat\Presentation\TurnSummary::html($summary['gist']) !!}</span></summary>
                        @include('partials.dominion.hero-turn-phases', ['summary' => $summary])
                    </details>
                @endforeach
            </div>
        @endif
    @endif
</div>
