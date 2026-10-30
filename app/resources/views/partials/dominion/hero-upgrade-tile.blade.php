@php
    $typeLabel = $upgrade !== null
        ? ucwords($upgrade->type)
        : implode(' or ', array_map('ucwords', $types));
@endphp
@if ($upgrade !== null)
    <div class="hero-tile">
        <i class="ra {{ $upgrade->icon }}"></i>
        <div class="hero-tile-label">{{ $upgrade->type === 'directive' ? 'Directive' : "Lv {$level} · {$typeLabel}" }}</div>
        <div class="hero-tile-name">{{ $upgrade->name }}</div>
        <div class="hero-tile-description">{!! $heroHelper->getCombatUpgradeDescription($upgrade) ?: $heroHelper->getUpgradeDescription($upgrade) !!}</div>
    </div>
@elseif ($heroLevel >= $level)
    <a href="#hero-upgrades" class="hero-tile hero-tile-open" data-upgrade-level="{{ $level }}">
        <i class="fa-regular fa-circle-question"></i>
        <div class="hero-tile-label">Lv {{ $level }} · {{ $typeLabel }}</div>
        <div class="hero-tile-name">Choose upgrade</div>
        <div class="hero-tile-description">View level {{ $level }} options</div>
    </a>
@else
    <div class="hero-tile hero-tile-locked">
        <i class="ra ra-padlock"></i>
        <div class="hero-tile-label">Lv {{ $level }} · {{ $typeLabel }}</div>
        <div class="hero-tile-name">Locked</div>
        <div class="hero-tile-description">Reach level {{ $level }}</div>
    </div>
@endif
