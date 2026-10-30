<div class="hero-class-row{{ $row['active'] ? ' active' : '' }}{{ $row['level'] > 0 ? '' : ' unplayed' }}">
    <i class="ra {{ $row['class']['icon'] }}"></i>
    <div>
        <div class="hero-class-name">{{ $row['class']['name'] }}</div>
        <div class="hero-class-bonus">
            <span class="{{ $row['level'] > 0 ? 'text-green' : '' }}">{{ $row['description'] }}</span>
            @if ($row['baseBonus'])
                <span class="hero-class-note" title="Before your hero bonus increase" data-bs-toggle="tooltip">({{ sprintf('%+.2f%%', $row['baseBonus']) }})</span>
            @endif
            @if (!$row['active'] && $row['level'] > 0)
                <span class="hero-class-note">(halved)</span>
            @endif
        </div>
    </div>
    <span class="hero-class-level">Lv {{ $row['level'] }}</span>
    @if ($row['active'])
        <span class="hero-class-active">Active</span>
    @else
        <a href="{{ route('dominion.heroes.change-class', $row['class']['key']) }}" class="btn btn-primary btn-sm{{ $heroCalculator->canChangeClass($hero) ? '' : ' disabled' }}">Switch</a>
    @endif
</div>
