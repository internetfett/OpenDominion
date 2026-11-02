@if ($option !== null)
    @php($description = $heroHelper->getLoadoutOptionDescription($option))
    <div class="hero-tile" title="{{ $description }}" data-bs-toggle="tooltip" tabindex="0">
        <i class="ra {{ $option->icon }}"></i>
        <div class="hero-tile-label">{{ $slot->label() }}</div>
        <div class="hero-tile-name">{{ $heroHelper->getLoadoutOptionName($option) }}</div>
        <div class="hero-tile-description">{{ $description }}</div>
    </div>
@elseif ($available)
    <a href="#hero-loadout" class="hero-tile hero-tile-open" data-loadout-slot="{{ $slot->value }}">
        <i class="fa-regular fa-circle-question"></i>
        <div class="hero-tile-label">{{ $slot->label() }}</div>
        <div class="hero-tile-name">Empty slot</div>
        <div class="hero-tile-description">View {{ strtolower($slot->label()) }} options</div>
    </a>
@else
    <div class="hero-tile hero-tile-locked">
        <i class="ra ra-padlock"></i>
        <div class="hero-tile-label">{{ $slot->label() }}</div>
        <div class="hero-tile-name">Locked</div>
        <div class="hero-tile-description">{{ $heroHelper->getLoadoutSlotRequirement($slot) }}</div>
    </div>
@endif
