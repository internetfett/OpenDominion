@php $borrowed = $view->isBorrowed($combatant); @endphp
<form action="{{ route('dominion.heroes.battles') }}" method="post" role="form" class="mb-3">
    @csrf
    <input type="hidden" name="combatant" value="{{ $combatant->id }}">
    @if (count($view->commandable()) > 1)
        <h6 class="fw-bold">{{ $combatant->name }} @if ($borrowed)<small class="text-muted">(borrowed)</small>@endif</h6>
    @endif
    <div class="row mb-3">
        <div class="col-sm-6">
            <label class="form-label">
                Actions in queue
            </label>
            <table class="table-sm">
                @foreach ($view->queue($combatant) as $idx => $queued)
                    <tr>
                        <td>{{ $queued['turn'] }}</td>
                        <td>
                            {{ $queued['ability'] }}
                            @if ($queued['target'])
                                <small class="text-muted">&rarr; {{ $queued['target'] }}</small>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('dominion.heroes.battles.action.delete', ['combatant'=>$combatant->id, 'action'=>$idx]) }}">
                                <i class="fa fa-trash text-danger"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div class="col-sm-6">
            <label class="form-label">
                Perform/Queue an action
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
    </div>
    <div class="row mb-3">
        <div class="col-sm-12">
            <label class="form-label">
                Strategy <small>(for turns taken while offline)</small>
            </label>
            <select name="strategy" class="form-select">
                @foreach ($view->strategies() as $key => $strategyName)
                    <option value="{{ $key }}" {{ $combatant->ai == $key ? 'selected' : null }}>{{ $strategyName }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-9">
            <div class="form-check">
                <input type="checkbox" id="automated-{{ $combatant->id }}" name="automated" class="form-check-input" {{ $combatant->automated ? 'checked' : null }}>
                <label for="automated-{{ $combatant->id }}" class="form-check-label">
                    Automate all of {{ $borrowed ? $combatant->name . "'s" : 'my' }} turns
                </label>
            </div>
        </div>
        <div class="col-sm-3">
            <button class="btn btn-primary">Update</button>
        </div>
    </div>
</form>
