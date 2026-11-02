<div class="turn-phases">
    @foreach ($summary['phases'] as $phase)
        <div class="turn-phase">
            <div class="turn-phase-name">{{ $phase['label'] }}</div>
            <ul>
                @foreach ($phase['lines'] as $line)
                    <li @class(['quiet' => $line['quiet']])>{!! \OpenDominion\HeroCombat\Presentation\TurnSummary::html($line['segments']) !!}</li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
