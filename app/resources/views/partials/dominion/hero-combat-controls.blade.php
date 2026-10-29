@php $borrowed = $view->isBorrowed($combatant); @endphp
<div class="mb-3">
    @if (count($view->commandable()) > 1)
        <h6 class="fw-bold">{{ $combatant->name }} @if ($borrowed)<small class="text-muted">(borrowed)</small>@endif</h6>
    @endif
    <label class="form-label">
        {{ $showQueue ? 'Perform/Queue an action' : 'Perform an action' }}
    </label>
    <div>
        @foreach ($view->abilities($combatant) as $ability)
            @php
                $label = $ability['name'];
                if ($ability['cooldown'] > 0) {
                    $label .= " ({$ability['cooldown']})";
                }
                if ($ability['charges'] !== null) {
                    $label .= " [{$ability['charges']}]";
                }
                $targets = $ability['needsTarget'] ? $view->targetsFor($combatant, $ability['key']) : [];
            @endphp
            @if (!$ability['usable'] || ($ability['needsTarget'] && count($targets) == 0))
                <a class="btn btn-block btn-secondary disabled mb-1" aria-disabled="true" tabindex="-1" title="{{ $ability['description'] }}">
                    {{ $label }}
                </a>
            @elseif ($ability['needsTarget'] && count($targets) > 1)
                <div class="dropdown mb-1">
                    <button class="btn btn-block btn-primary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ $ability['description'] }}">
                        {{ $label }}
                    </button>
                    <ul class="dropdown-menu w-100">
                        @foreach ($targets as $target)
                            <li>
                                <a class="dropdown-item" href="{{ route('dominion.heroes.battles.action', ['combatant'=>$combatant->id, 'target'=>$target->id, 'action'=>$ability['key']]) }}">
                                    {{ $target->id === $combatant->id ? 'Yourself' : $target->name }} <small class="text-muted">({{ $target->currentHealth }} hp)</small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <a class="btn btn-block btn-primary mb-1" title="{{ $ability['description'] }}"
                    href="{{ route('dominion.heroes.battles.action', ['combatant'=>$combatant->id, 'target'=>$targets[0]->id ?? null, 'action'=>$ability['key']]) }}">
                    {{ $label }}
                </a>
            @endif
        @endforeach
    </div>
</div>
@if ($showQueue && $queueInline)
    @include('partials.dominion.hero-battle-queue', ['view' => $view, 'combatant' => $combatant])
@endif
